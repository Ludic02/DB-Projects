<?php
class TrainController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Mostra lista treni disponibili
     */
    public function index() {
        try {
            error_log("TrainController::index() chiamato");

            // Query per recuperare tutti i treni
            $query = "SELECT DISTINCT t.* 
                     FROM sft_treno t
                     WHERE t.attivo = 1
                     ORDER BY t.tipo, t.nome";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $trains = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Trovati " . count($trains) . " treni");

            // Recupera le stazioni
            $stmt = $this->db->prepare("
                SELECT id, nome, km, descrizione
                FROM sft_stazione
                ORDER BY km ASC");
            $stmt->execute();
            $stazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Numero stazioni trovate: " . count($stazioni));

            // Passa il db alla vista per gli orari
            $db = $this->db;
            
            include 'views/trains/index.php';
        } catch (PDOException $e) {
            error_log("Errore nel recupero dei treni: " . $e->getMessage());
            Session::setErrorMessage("Errore nel caricamento degli orari");
            $trains = [];
            $stazioni = [];
            include 'views/trains/index.php';
        }
    }

    /**
     * Mostra dettagli di un treno specifico
     */
    public function view() {
        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID treno non valido");
            }

            error_log("Visualizzazione dettagli treno ID: " . $id);

            // Recupera dettagli del treno
            $stmt = $this->db->prepare("
                SELECT t.*, 
                       COUNT(DISTINCT o.stazione_id) as numero_fermate
                FROM sft_treno t
                LEFT JOIN sft_orario o ON t.id = o.treno_id
                WHERE t.id = ? AND t.attivo = 1
                GROUP BY t.id");
            $stmt->execute([$id]);
            $train = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$train) {
                throw new Exception("Treno non trovato");
            }

            // Recupera fermate e orari
            $stmt = $this->db->prepare("
                SELECT s.nome as stazione, s.km, s.descrizione,
                       TIME_FORMAT(o.orario, '%H:%i') as orario
                FROM sft_orario o
                JOIN sft_stazione s ON o.stazione_id = s.id
                WHERE o.treno_id = ?
                ORDER BY s.km ASC");
            $stmt->execute([$id]);
            $fermate = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Numero fermate trovate: " . count($fermate));

            include 'views/trains/view.php';
        } catch (Exception $e) {
            error_log("Errore in TrainController::view(): " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
            header('Location: index.php?page=trains');
            exit;
        }
    }

    /**
     * Form di prenotazione
     */
    public function prenota() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login per prenotare");
            }

            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("Treno non valido");
            }

            error_log("Avvio prenotazione per treno ID: " . $id);

            // Recupera dettagli del treno
            $stmt = $this->db->prepare("
                SELECT * FROM sft_treno 
                WHERE id = ? AND attivo = 1");
            $stmt->execute([$id]);
            $train = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$train) {
                throw new Exception("Treno non trovato");
            }

            // Recupera le stazioni
            $stmt = $this->db->prepare("
                SELECT id, nome, km 
                FROM sft_stazione 
                ORDER BY km ASC");
            $stmt->execute();
            $stazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

            include 'views/trains/prenota.php';
        } catch (Exception $e) {
            error_log("Errore in prenota: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
            header('Location: index.php?page=trains');
            exit;
        }
    }

    /**
     * Gestisce la conferma della prenotazione
     */
    public function prenota_conferma() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login per prenotare");
            }

            error_log("Inizio prenota_conferma");

            // Validazione input
            $treno_id = filter_input(INPUT_POST, 'treno_id', FILTER_VALIDATE_INT);
            $stazione_partenza = filter_input(INPUT_POST, 'stazione_partenza', FILTER_VALIDATE_INT);
            $stazione_arrivo = filter_input(INPUT_POST, 'stazione_arrivo', FILTER_VALIDATE_INT);
            $data_viaggio = htmlspecialchars(strip_tags($_POST['data_viaggio']));
            $numero_posti = filter_input(INPUT_POST, 'numero_posti', FILTER_VALIDATE_INT);

            error_log("Dati prenotazione ricevuti: " . print_r($_POST, true));

            // Validazioni base
            if (!$treno_id || !$stazione_partenza || !$stazione_arrivo || !$data_viaggio || !$numero_posti) {
                throw new Exception("Tutti i campi sono obbligatori");
            }

            if ($stazione_partenza === $stazione_arrivo) {
                throw new Exception("Le stazioni di partenza e arrivo devono essere diverse");
            }

            // Validazione data
            $data = DateTime::createFromFormat('Y-m-d', $data_viaggio);
            if (!$data || $data < new DateTime('today')) {
                throw new Exception("Data non valida");
            }

            // Verifica direzione del viaggio
            $stmt = $this->db->prepare("
                SELECT s1.km as km_partenza, s2.km as km_arrivo
                FROM sft_stazione s1, sft_stazione s2
                WHERE s1.id = ? AND s2.id = ?");
            $stmt->execute([$stazione_partenza, $stazione_arrivo]);
            $direzione = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($direzione['km_partenza'] >= $direzione['km_arrivo']) {
                throw new Exception("Il viaggio deve seguire la direzione della linea (km crescenti)");
            }

            $this->db->beginTransaction();

            try {
                // Verifica disponibilità posti
                $stmt = $this->db->prepare("
                    SELECT t.posti_totali,
                           COALESCE(SUM(p.numero_posti), 0) as posti_occupati
                    FROM sft_treno t
                    LEFT JOIN sft_prenotazione p ON t.id = p.treno_id 
                        AND p.data_viaggio = ? 
                        AND p.stato = 'CONFERMATA'
                    WHERE t.id = ?
                    GROUP BY t.id");
                
                $stmt->execute([$data_viaggio, $treno_id]);
                $disponibilita = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$disponibilita) {
                    throw new Exception("Treno non trovato");
                }

                $posti_disponibili = $disponibilita['posti_totali'] - $disponibilita['posti_occupati'];
                if ($numero_posti > $posti_disponibili) {
                    throw new Exception("Non ci sono abbastanza posti disponibili. Posti disponibili: " . $posti_disponibili);
                }

                // Inserisci la prenotazione
                error_log("Inserimento prenotazione in corso...");
                
                $stmt = $this->db->prepare("
                    INSERT INTO sft_prenotazione (
                        utente_id, 
                        treno_id, 
                        stazione_partenza_id, 
                        stazione_arrivo_id,
                        data_viaggio, 
                        numero_posti, 
                        stato, 
                        stato_pagamento, 
                        data_creazione
                    ) VALUES (?, ?, ?, ?, ?, ?, 'CONFERMATA', 'IN_ATTESA', NOW())");

                $stmt->execute([
                    Session::getUserId(),
                    $treno_id,
                    $stazione_partenza,
                    $stazione_arrivo,
                    $data_viaggio,
                    $numero_posti
                ]);

                $booking_id = $this->db->lastInsertId();
                
                $this->db->commit();
                error_log("Prenotazione creata con ID: " . $booking_id);

                // Reindirizza alla pagina di conferma
                header("Location: index.php?page=trains&action=conferma&id=" . $booking_id);
                exit;

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Errore in prenota_conferma: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
            if (isset($treno_id)) {
                header('Location: index.php?page=trains&action=prenota&id=' . $treno_id);
            } else {
                header('Location: index.php?page=trains');
            }
            exit;
        }
    }

    /**
     * Pagina di conferma prenotazione
     */
    public function conferma() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login per vedere la prenotazione");
            }
    
            $booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$booking_id) {
                throw new Exception("ID prenotazione non valido");
            }
    
            // Recupera i dettagli della prenotazione
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       t.nome as treno_nome, t.tipo as treno_tipo,
                       s1.nome as stazione_partenza, s1.km as km_partenza,
                       s2.nome as stazione_arrivo, s2.km as km_arrivo
                FROM sft_prenotazione p
                JOIN sft_treno t ON p.treno_id = t.id
                JOIN sft_stazione s1 ON p.stazione_partenza_id = s1.id
                JOIN sft_stazione s2 ON p.stazione_arrivo_id = s2.id
                WHERE p.id = ? AND p.utente_id = ?
                AND p.stato = 'CONFERMATA'
            ");
            
            $stmt->execute([$booking_id, Session::getUserId()]);
            $prenotazione = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$prenotazione) {
                throw new Exception("Prenotazione non trovata");
            }
    
            // Calcola il costo del biglietto
            $km_totali = abs($prenotazione['km_arrivo'] - $prenotazione['km_partenza']);
            $costo_per_km = 0.20; // €0.20 per km
            $costo_totale = $km_totali * $costo_per_km * $prenotazione['numero_posti'];
    
            // Aggiorna il costo nella prenotazione se non è già impostato
            $stmt = $this->db->prepare("
                UPDATE sft_prenotazione 
                SET importo = ? 
                WHERE id = ? 
                AND importo IS NULL
            ");
            $stmt->execute([$costo_totale, $booking_id]);
            
            error_log("Dettagli prenotazione recuperati - ID: " . $booking_id);
            error_log("Distanza: " . $km_totali . " km");
            error_log("Costo totale calcolato: " . $costo_totale . " EUR");
    
            include 'views/trains/conferma.php';
    
        } catch (Exception $e) {
            error_log("Errore in conferma prenotazione: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
            header('Location: index.php?page=trains');
            exit;
        }
    }

    /**
     * Cancella una prenotazione
     */
    public function cancel_booking() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login");
            }

            $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
            if (!$booking_id) {
                throw new Exception("Prenotazione non valida");
            }

            error_log("Tentativo di cancellazione prenotazione ID: " . $booking_id);

            // Verifica che la prenotazione appartenga all'utente
            $stmt = $this->db->prepare("
                UPDATE sft_prenotazione 
                SET stato = 'CANCELLATA'
                WHERE id = ? 
                AND utente_id = ? 
                AND data_viaggio > CURRENT_DATE");

            $stmt->execute([$booking_id, Session::getUserId()]);

            if ($stmt->rowCount() > 0) {
                error_log("Prenotazione cancellata con successo");
                Session::setSuccessMessage("Prenotazione cancellata con successo");
            } else {
                throw new Exception("Impossibile cancellare la prenotazione");
            }

        } catch (Exception $e) {
            error_log("Errore cancellazione: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
        }

        header('Location: index.php?page=bookings');
        exit;
    }
}