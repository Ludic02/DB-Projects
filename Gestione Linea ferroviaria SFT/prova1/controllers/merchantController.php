<?php
class MerchantController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    private function isAuthorized() {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'esercente') {
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }
        return true;
    }

    public function index() {
        if (!$this->isAuthorized()) {
            return;
        }

        try {
            // Recupera dati dell'esercente
            $stmt = $this->db->prepare("
                SELECT * FROM pay_esercenti 
                WHERE email = ?
            ");
            $stmt->execute([Session::getUserEmail()]);
            $merchantData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$merchantData) {
                // Se l'esercente non esiste, crealo
                $stmt = $this->db->prepare("
                    INSERT INTO pay_esercenti (email, nome_esercente, saldo)
                    VALUES (?, 'Merchant SFT', 0.00)
                ");
                $stmt->execute([Session::getUserEmail()]);
                
                $stmt = $this->db->prepare("SELECT * FROM pay_esercenti WHERE email = ?");
                $stmt->execute([Session::getUserEmail()]);
                $merchantData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Recupera transazioni recenti
        $stmt = $this->db->prepare("
        SELECT 
            t.codice_transazione,
            t.importo,
            t.descrizione,
            t.stato,
            t.data_creazione
        FROM pay_transazioni t
        WHERE t.esercente_id = ?
        ORDER BY t.data_creazione DESC
        LIMIT 5
    ");
    $stmt->execute([$merchantData['id']]);
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require 'views/merchant/dashboard.php';

} catch (Exception $e) {
    error_log("Errore nel recupero dati merchant: " . $e->getMessage());
    Session::setErrorMessage($e->getMessage());
    header('Location: index.php');
    exit;
}
}

    public function withdraw() {
        if (!$this->isAuthorized()) {
            return;
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = $_POST['amount'] ?? '';
            $description = $_POST['description'] ?? '';
    
            if (empty($amount)) {
                Session::setErrorMessage("Inserisci un importo");
                require 'views/merchant/withdraw.php';
                return;
            }
    
            if (empty($description)) {
                Session::setErrorMessage("Inserisci una causale");
                require 'views/merchant/withdraw.php';
                return;
            }
    
            Session::setSuccessMessage("Transazione autorizzata con successo");
            header('Location: index.php?page=merchant');
            exit;
        }
    
        require 'views/merchant/withdraw.php';
    }

    public function transactions() {
        if (!$this->isAuthorized()) {
            return;
        }

        try {
            // Recupera ID esercente
            $stmt = $this->db->prepare("SELECT id FROM pay_esercenti WHERE email = ?");
            $stmt->execute([Session::getUserEmail()]);
            $merchant = $stmt->fetch(PDO::FETCH_ASSOC);

            // Recupera tutte le transazioni
            $stmt = $this->db->prepare("
                SELECT t.*
                FROM pay_transazioni t
                WHERE t.esercente_id = ?
                ORDER BY t.data_creazione DESC
            ");
            $stmt->execute([$merchant['id']]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require 'views/merchant/transactions.php';

        } catch (Exception $e) {
            Session::setErrorMessage($e->getMessage());
            header('Location: index.php?page=merchant');
            exit;
        }
    }

    public function getMerchantData() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM pay_esercenti 
                WHERE email = ?
            ");
            $stmt->execute([Session::getUserEmail()]);
            $merchantData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$merchantData) {
                // Se l'esercente non esiste, crealo
                $stmt = $this->db->prepare("
                    INSERT INTO pay_esercenti (email, nome_esercente, saldo)
                    VALUES (?, 'Merchant SFT', 0.00)
                ");
                $stmt->execute([Session::getUserEmail()]);
                
                $stmt = $this->db->prepare("SELECT * FROM pay_esercenti WHERE email = ?");
                $stmt->execute([Session::getUserEmail()]);
                $merchantData = $stmt->fetch(PDO::FETCH_ASSOC);
            }
    
            return $merchantData;
        } catch (Exception $e) {
            throw new Exception("Errore nel recupero dati esercente: " . $e->getMessage());
        }
    }
    
    public function getRecentTransactions($esercente_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT *
                FROM pay_transazioni
                WHERE esercente_id = ?
                ORDER BY data_creazione DESC
                LIMIT 5
            ");
            $stmt->execute([$esercente_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Errore nel recupero transazioni recenti: " . $e->getMessage());
        }
    }
    
    public function processWithdraw($amount, $description, $merchant_id) {
        try {
            $this->db->beginTransaction();
    
            // Aggiorna saldo esercente
            $stmt = $this->db->prepare("
                UPDATE pay_esercenti 
                SET saldo = saldo - ? 
                WHERE id = ?
            ");
            $stmt->execute([$amount, $merchant_id]);
    
            // Registra la transazione
            $codice_transazione = 'WD_' . time() . '_' . $merchant_id;
            $stmt = $this->db->prepare("
                INSERT INTO pay_transazioni (
                    codice_transazione,
                    esercente_id,
                    importo,
                    descrizione,
                    stato
                ) VALUES (?, ?, ?, ?, 'COMPLETED')
            ");
            
            $stmt->execute([
                $codice_transazione,
                $merchant_id,
                -$amount,
                $description
            ]);
    
            $this->db->commit();
            return true;
    
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Errore nel processare la transazione: " . $e->getMessage());
        }
    }
}