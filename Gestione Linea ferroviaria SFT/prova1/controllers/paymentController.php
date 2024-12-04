<?php
class PaymentController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function ensurePaySteamAccount() {
        try {
            // Recupera l'email dell'utente
            $stmt = $this->db->prepare("SELECT email FROM sys_utente WHERE id = ?");
            $stmt->execute([Session::getUserId()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("Utente non trovato");
            }

            // Verifica se esiste l'account PaySteam
            $stmt = $this->db->prepare("SELECT id FROM pay_utenti WHERE email = ?");
            $stmt->execute([$user['email']]);
            
            if (!$stmt->fetch()) {
                // Crea l'account PaySteam
                $stmt = $this->db->prepare("INSERT INTO pay_utenti (email, saldo) VALUES (?, 0.00)");
                $stmt->execute([$user['email']]);
                
                error_log("Account PaySteam creato per: " . $user['email']);
            }

            return true;
        } catch (Exception $e) {
            error_log("Errore in ensurePaySteamAccount: " . $e->getMessage());
            throw $e;
        }
    }

    public function checkout() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login per effettuare il pagamento");
            }

            // Assicura l'esistenza dell'account PaySteam
            $this->ensurePaySteamAccount();

            $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
            
            if (!$booking_id) {
                throw new Exception("Prenotazione non valida");
            }

            // Recupera i dettagli della prenotazione con JOIN
            $stmt = $this->db->prepare("
            SELECT p.*, 
                t.nome as treno_nome,
                s1.nome as stazione_partenza,
                s2.nome as stazione_arrivo,
                pu.saldo as saldo_disponibile
            FROM sft_prenotazione p
            JOIN sft_treno t ON p.treno_id = t.id
            JOIN sft_stazione s1 ON p.stazione_partenza_id = s1.id
            JOIN sft_stazione s2 ON p.stazione_arrivo_id = s2.id
            JOIN sys_utente su ON p.utente_id = su.id
            LEFT JOIN pay_utenti pu ON su.email = pu.email
            WHERE p.id = ? AND p.utente_id = ?
            AND p.stato = 'CONFERMATA'
            AND p.stato_pagamento = 'IN_ATTESA'
            ");
            
            $stmt->execute([$booking_id, Session::getUserId()]);
            $prenotazione = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prenotazione) {
                throw new Exception("Prenotazione non trovata o già pagata");
            }

            include 'views/payment/checkout.php';

        } catch (Exception $e) {
            error_log("Errore in checkout: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
            header('Location: index.php?page=trains');
            exit;
        }
    }

    public function process() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login per effettuare il pagamento");
            }
    
            // Assicura l'esistenza dell'account PaySteam
            $this->ensurePaySteamAccount();
    
            $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
            
            if (!$booking_id) {
                throw new Exception("Prenotazione non valida");
            }
    
            // Recupera l'email dell'utente
            $stmt = $this->db->prepare("SELECT email FROM sys_utente WHERE id = ?");
            $stmt->execute([Session::getUserId()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Recupera il saldo dell'utente
            $stmt = $this->db->prepare("
                SELECT * 
                FROM pay_utenti 
                WHERE email = ?
            ");
            $stmt->execute([$user['email']]);
            $conto = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$conto) {
                throw new Exception("Account PaySteam non trovato");
            }
    
            // Recupera la prenotazione
            $stmt = $this->db->prepare("
                SELECT * FROM sft_prenotazione
                WHERE id = ? AND utente_id = ?
                AND stato = 'CONFERMATA'
                AND stato_pagamento = 'IN_ATTESA'
            ");
            $stmt->execute([$booking_id, Session::getUserId()]);
            $prenotazione = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$prenotazione) {
                throw new Exception("Prenotazione non trovata o già pagata");
            }
    
            if ($conto['saldo'] < $prenotazione['importo']) {
                throw new Exception("Saldo insufficiente");
            }
    
            $this->db->beginTransaction();
    
            try {
                // 1. Sottrai il saldo dall'utente
                $stmt = $this->db->prepare("
                    UPDATE pay_utenti 
                    SET saldo = saldo - ? 
                    WHERE email = ?
                ");
                $stmt->execute([$prenotazione['importo'], $user['email']]);
    
                // 2. Aggiungi il saldo all'esercente
                $stmt = $this->db->prepare("
                    UPDATE pay_esercenti 
                    SET saldo = saldo + ? 
                    WHERE email = 'admin@sft.it'
                ");
                $stmt->execute([$prenotazione['importo']]);
    
                // 3. Registra la transazione
                $stmt = $this->db->prepare("
                    INSERT INTO pay_transazioni (
                        codice_transazione,
                        esercente_id,
                        cliente_email,
                        importo,
                        descrizione,
                        stato,
                        url_origine,
                        url_ritorno
                    ) VALUES (
                        ?,
                        (SELECT id FROM pay_esercenti WHERE email = 'admin@sft.it'),
                        ?,
                        ?,
                        ?,
                        'COMPLETED',
                        'http://localhost/index.php?page=payment&action=callback',
                        'http://localhost/index.php?page=payment&action=confirm'
                    )
                ");
    
                $codice_transazione = 'SFT_' . $booking_id . '_' . time();
                $stmt->execute([
                    $codice_transazione,
                    $user['email'],
                    $prenotazione['importo'],
                    'Prenotazione biglietto #' . $booking_id
                ]);
    
                // 4. Aggiorna lo stato della prenotazione
                $stmt = $this->db->prepare("
                    UPDATE sft_prenotazione
                    SET stato_pagamento = 'PAGATO'
                    WHERE id = ?
                ");
                $stmt->execute([$booking_id]);
    
                $this->db->commit();
    
                // Reindirizza alla pagina di conferma
                header("Location: index.php?page=payment&action=confirm&booking_id=" . $booking_id);
                exit;
    
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
    
        } catch (Exception $e) {
            error_log("Errore in process: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
            header('Location: index.php?page=payment&action=checkout&booking_id=' . $booking_id);
            exit;
        }
    }

    public function confirm() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Accesso non autorizzato");
            }
    
            $booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
            
            if (!$booking_id) {
                throw new Exception("Prenotazione non valida");
            }
    
            // Recupera i dettagli completi del biglietto con la colonna corretta
            $stmt = $this->db->prepare("
                SELECT 
                    p.*,
                    t.nome as nome_treno,
                    s1.nome as stazione_partenza,
                    s1.km as km_partenza,
                    s2.nome as stazione_arrivo,
                    s2.km as km_arrivo,
                    tr.codice_transazione,        
                    tr.data_creazione as data_pagamento,
                    CONCAT('TKT-', p.id) as codice_biglietto
                FROM sft_prenotazione p
                JOIN sft_treno t ON p.treno_id = t.id
                JOIN sft_stazione s1 ON p.stazione_partenza_id = s1.id
                JOIN sft_stazione s2 ON p.stazione_arrivo_id = s2.id
                LEFT JOIN pay_transazioni tr ON tr.descrizione = CONCAT('Prenotazione biglietto #', p.id)
                WHERE p.id = ? 
                AND p.utente_id = ?
                AND p.stato_pagamento = 'PAGATO'
            ");
            
            $stmt->execute([$booking_id, Session::getUserId()]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$ticket) {
                throw new Exception("Biglietto non trovato o non pagato");
            }
    
            // Log per debug
            error_log("Dati biglietto recuperati: " . print_r($ticket, true));
    
            include 'views/payment/confirmation.php';
    
        } catch (Exception $e) {
            error_log("Errore in confirm: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
            header('Location: index.php?page=trains');
            exit;
        }
    }

    
}