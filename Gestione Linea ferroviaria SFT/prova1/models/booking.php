<?php
class Booking {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Crea una nuova prenotazione
     */
    public function createBooking($userId, $trainId, $stationFrom, $stationTo, $bookingDate, $seats = 1) {
        try {
            // Verifica disponibilità posti
            if (!$this->checkAvailability($trainId, $bookingDate, $seats)) {
                throw new Exception("Posti non disponibili per la data selezionata");
            }

            // Verifica validità stazioni
            if (!$this->validateStations($trainId, $stationFrom, $stationTo)) {
                throw new Exception("Percorso non valido");
            }

            $sql = "INSERT INTO sft_prenotazione (id_utente, id_treno, stazione_partenza, 
                    stazione_arrivo, data_prenotazione, numero_posti, stato)
                    VALUES (:userId, :trainId, :stationFrom, :stationTo, :bookingDate, :seats, 'CONFERMATA')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':userId' => $userId,
                ':trainId' => $trainId,
                ':stationFrom' => $stationFrom,
                ':stationTo' => $stationTo,
                ':bookingDate' => $bookingDate,
                ':seats' => $seats
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Errore durante la creazione della prenotazione: " . $e->getMessage());
        }
    }

    /**
     * Verifica disponibilità posti
     */
    private function checkAvailability($trainId, $date, $seatsRequested) {
        $sql = "SELECT t.posti_totali - COALESCE(SUM(p.numero_posti), 0) as posti_disponibili
                FROM sft_treno t
                LEFT JOIN sft_prenotazione p ON t.id = p.id_treno 
                    AND p.data_prenotazione = :date
                    AND p.stato = 'CONFERMATA'
                WHERE t.id = :trainId
                GROUP BY t.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':trainId' => $trainId, ':date' => $date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($result && $result['posti_disponibili'] >= $seatsRequested);
    }

    /**
     * Valida il percorso tra le stazioni
     */
    private function validateStations($trainId, $stationFrom, $stationTo) {
        $sql = "SELECT COUNT(*) as valid
                FROM sft_orario
                WHERE id_treno = :trainId 
                AND id_stazione_partenza = :stationFrom
                AND id_stazione_arrivo = :stationTo
                AND orario_partenza < orario_arrivo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':trainId' => $trainId,
            ':stationFrom' => $stationFrom,
            ':stationTo' => $stationTo
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['valid'] > 0;
    }

    /**
     * Ottiene le prenotazioni di un utente
     */
    public function getUserBookings($userId) {
        $sql = "SELECT p.*, t.nome as nome_treno, 
                s1.nome as stazione_partenza_nome,
                s2.nome as stazione_arrivo_nome
                FROM sft_prenotazione p
                JOIN sft_treno t ON p.id_treno = t.id
                JOIN sft_stazione s1 ON p.stazione_partenza = s1.id
                JOIN sft_stazione s2 ON p.stazione_arrivo = s2.id
                WHERE p.id_utente = :userId
                ORDER BY p.data_prenotazione DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cancella una prenotazione
     */
    public function cancelBooking($bookingId, $userId) {
        // Verifica che la prenotazione appartenga all'utente
        $sql = "UPDATE sft_prenotazione 
                SET stato = 'CANCELLATA'
                WHERE id = :bookingId 
                AND id_utente = :userId
                AND data_prenotazione > CURRENT_DATE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':bookingId' => $bookingId,
            ':userId' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Ottiene statistiche prenotazioni per il backoffice
     */
    public function getBookingStats() {
        $sql = "SELECT 
                    COUNT(*) as totale_prenotazioni,
                    SUM(numero_posti) as totale_posti_prenotati,
                    AVG(numero_posti) as media_posti_per_prenotazione,
                    COUNT(DISTINCT id_utente) as utenti_unici
                FROM sft_prenotazione
                WHERE stato = 'CONFERMATA'
                AND data_prenotazione >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}