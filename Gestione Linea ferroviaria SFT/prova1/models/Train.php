<?php
// File: models/Train.php
class Train {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getTrains($date) {
        $query = "SELECT t.*, 
                        sp.nome as stazione_partenza,
                        sa.nome as stazione_arrivo,
                        (SELECT COUNT(*) FROM sft_prenotazione WHERE treno_id = t.id) as posti_prenotati
                 FROM sft_treno t
                 JOIN sft_stazione sp ON t.stazione_partenza = sp.id
                 JOIN sft_stazione sa ON t.stazione_arrivo = sa.id
                 WHERE t.data = ?
                 ORDER BY t.partenza";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrain($id) {
        $query = "SELECT t.*, 
                        sp.nome as stazione_partenza,
                        sa.nome as stazione_arrivo,
                        (t.posti_disponibili - COALESCE((SELECT SUM(posti) FROM sft_prenotazione WHERE treno_id = t.id), 0)) as posti_rimasti
                 FROM sft_treno t
                 JOIN sft_stazione sp ON t.stazione_partenza = sp.id
                 JOIN sft_stazione sa ON t.stazione_arrivo = sa.id
                 WHERE t.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createBooking($train_id, $user_id, $num_seats) {
        try {
            $this->conn->beginTransaction();

            // Verifica disponibilità posti
            $train = $this->getTrain($train_id);
            if($train['posti_rimasti'] < $num_seats) {
                throw new Exception("Posti non disponibili");
            }

            // Crea prenotazione
            $query = "INSERT INTO sft_prenotazione (treno_id, utente_id, posti) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$train_id, $user_id, $num_seats]);

            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function createTrain($data) {
        $query = "INSERT INTO sft_treno (
                    data, direzione, partenza, arrivo,
                    stazione_partenza, stazione_arrivo, posti_disponibili
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                $data['data'],
                $data['direzione'],
                $data['partenza'],
                $data['arrivo'],
                $data['stazione_partenza'],
                $data['stazione_arrivo'],
                $data['posti_disponibili'] ?? 50
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>