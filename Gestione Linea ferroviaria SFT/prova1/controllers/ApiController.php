<?php
class ApiController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Gestisce l'invio della richiesta di pagamento a PaySteam
     */
    public function send_payment_request() {
        try {
            error_log("send_payment_request chiamato");

            // Recupera e valida il booking_id
            $booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
            if (!$booking_id) {
                throw new Exception("ID prenotazione non valido");
            }

            // Recupera dettagli della prenotazione
            $stmt = $this->db->prepare("
                SELECT p.*, t.nome as nome_treno, t.tipo,
                       s1.nome as stazione_partenza,
                       s2.nome as stazione_arrivo,
                       u.email as user_email
                FROM sft_prenotazione p
                JOIN sft_treno t ON p.treno_id = t.id
                JOIN sft_stazione s1 ON p.stazione_partenza_id = s1.id
                JOIN sft_stazione s2 ON p.stazione_arrivo_id = s2.id
                JOIN sys_utente u ON p.utente_id = u.id
                WHERE p.id = ?");
            $stmt->execute([$booking_id]);
            $prenotazione = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prenotazione) {
                throw new Exception("Prenotazione non trovata");
            }

            // Calcola il prezzo (10€ per posto)
            $prezzo = $prenotazione['numero_posti'] * 10;

            // Costruisci l'ID univoco della transazione
            $transaction_id = 'SFT_' . date('Ymd') . '_' . $booking_id;

            // Prepara i dati per PaySteam secondo le specifiche
            $paysteam_data = [
                'url_inviante' => 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?page=api&action=payment_callback',
                'url_risposta' => 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?page=api&action=payment_response',
                'id_esercente' => 'SFT_RAILWAY',
                'id_transazione' => $transaction_id,
                'descrizione' => sprintf(
                    "Biglietto treno %s da %s a %s - %s - %d posti",
                    $prenotazione['nome_treno'],
                    $prenotazione['stazione_partenza'],
                    $prenotazione['stazione_arrivo'],
                    $prenotazione['data_viaggio'],
                    $prenotazione['numero_posti']
                ),
                'prezzo' => $prezzo
            ];

            // Log dei dati della richiesta
            error_log("Dati PaySteam: " . print_r($paysteam_data, true));

            // Registra la transazione nel database
            $stmt = $this->db->prepare("
                INSERT INTO sft_transazioni (
                    prenotazione_id,
                    id_transazione_paysteam,
                    importo,
                    stato,
                    dati_richiesta,
                    data_creazione
                ) VALUES (?, ?, ?, 'PENDING', ?, NOW())");
            
            $stmt->execute([
                $booking_id,
                $transaction_id,
                $prezzo,
                json_encode($paysteam_data)
            ]);

            $transaction_db_id = $this->db->lastInsertId();
            error_log("Transazione registrata con ID: " . $transaction_db_id);

            
            include 'views/payment/process.php';

        } catch (Exception $e) {
            error_log("Errore in send_payment_request: " . $e->getMessage());
            $_SESSION['error_message'] = "Errore durante l'elaborazione del pagamento: " . $e->getMessage();
            header('Location: index.php?page=bookings');
            exit;
        }
    }

    /**
     * Gestisce la risposta dal sistema PaySteam
     */
    public function payment_response() {
        try {
            error_log("payment_response chiamato");

            // Recupera parametri dalla risposta
            $transaction_id = filter_input(INPUT_GET, 'id_transazione', FILTER_SANITIZE_STRING);
            $esito = filter_input(INPUT_GET, 'esito', FILTER_SANITIZE_STRING);

            if (!$transaction_id || !$esito) {
                throw new Exception("Parametri di risposta non validi");
            }

            error_log("Risposta ricevuta - ID: $transaction_id, Esito: $esito");

            // Aggiorna lo stato della transazione
            $stmt = $this->db->prepare("
                UPDATE sft_transazioni 
                SET stato = ?, 
                    data_risposta = NOW(),
                    dati_risposta = ?
                WHERE id_transazione_paysteam = ?");
            
            $stmt->execute([
                $esito === 'OK' ? 'COMPLETED' : 'FAILED',
                json_encode($_GET),
                $transaction_id
            ]);

            // Se il pagamento è andato a buon fine, aggiorna la prenotazione
            if ($esito === 'OK') {
                $stmt = $this->db->prepare("
                    UPDATE sft_prenotazione p
                    JOIN sft_transazioni t ON p.id = t.prenotazione_id
                    SET p.stato_pagamento = 'PAGATO'
                    WHERE t.id_transazione_paysteam = ?");
                
                $stmt->execute([$transaction_id]);

                $_SESSION['success_message'] = "Pagamento completato con successo!";
            } else {
                // Aggiorna lo stato della prenotazione come pagamento fallito
                $stmt = $this->db->prepare("
                    UPDATE sft_prenotazione p
                    JOIN sft_transazioni t ON p.id = t.prenotazione_id
                    SET p.stato_pagamento = 'FALLITO'
                    WHERE t.id_transazione_paysteam = ?");
                
                $stmt->execute([$transaction_id]);

                $_SESSION['error_message'] = "Il pagamento non è andato a buon fine";
            }

            // Invia la risposta all'applicazione chiamante
            $response_data = [
                'url_inviante' => $_GET['url_inviante'] ?? '',
                'id_transazione' => $transaction_id,
                'esito' => $esito
            ];

            error_log("Invio risposta: " . print_r($response_data, true));

            // Redirect alla lista delle prenotazioni
            header('Location: index.php?page=bookings');
            exit;

        } catch (Exception $e) {
            error_log("Errore in payment_response: " . $e->getMessage());
            $_SESSION['error_message'] = "Errore durante l'elaborazione della risposta di pagamento";
            header('Location: index.php?page=bookings');
            exit;
        }
    }

    /**
     * Gestisce il callback da PaySteam
     */
    public function payment_callback() {
        try {
            error_log("payment_callback chiamato");
            $input = file_get_contents('php://input');
            error_log("Dati callback ricevuti: " . $input);

            // Elabora i dati del callback
            $data = json_decode($input, true);
            if (!$data) {
                throw new Exception("Dati callback non validi");
            }

            // Aggiorna lo stato della transazione
            $stmt = $this->db->prepare("
                UPDATE sft_transazioni 
                SET stato = ?, 
                    data_callback = NOW(),
                    dati_callback = ?
                WHERE id_transazione_paysteam = ?");
            
            $stmt->execute([
                $data['esito'] === 'OK' ? 'CONFIRMED' : 'FAILED',
                $input,
                $data['id_transazione']
            ]);

            // Invia risposta al callback
            http_response_code(200);
            echo json_encode(['status' => 'OK']);

        } catch (Exception $e) {
            error_log("Errore in payment_callback: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
        }
    }
}