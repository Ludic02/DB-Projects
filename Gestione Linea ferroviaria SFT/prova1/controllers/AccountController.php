<?php
class AccountController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index() {
        try {
            // Recupera il saldo
            $stmt = $this->db->prepare("
                SELECT pu.* 
                FROM pay_utenti pu 
                JOIN sys_utente su ON su.email = pu.email 
                WHERE su.id = ?
            ");
            $stmt->execute([Session::getUserId()]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            // Recupera le transazioni
            $stmt = $this->db->prepare("
                SELECT 
                    t.*, 
                    DATE_FORMAT(t.data_creazione, '%d/%m/%Y %H:%i') as created_at 
                FROM pay_transazioni t 
                JOIN pay_utenti pu ON (t.utente_id = pu.id OR t.esercente_id = pu.id)
                JOIN sys_utente su ON su.email = pu.email 
                WHERE su.id = ? 
                ORDER BY t.data_creazione DESC 
                LIMIT 10
            ");
            $stmt->execute([Session::getUserId()]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Recupera le carte
            $stmt = $this->db->prepare("
                SELECT * 
                FROM pay_carte_credito 
                WHERE utente_id = ? AND attiva = 1
            ");
            $stmt->execute([Session::getUserId()]);
            $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

            include 'views/account/index.php';

        } catch (Exception $e) {
            error_log("Errore nel recupero dati account: " . $e->getMessage());
            $_SESSION['error_message'] = "Errore nel recupero dei dati";
            header('Location: index.php');
            exit;
        }
    }

    public function addCard() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login per aggiungere una carta");
            }
    
            // Recupera e valida i dati
            $numero_carta = filter_input(INPUT_POST, 'numero_carta', FILTER_SANITIZE_STRING);
            $scadenza = filter_input(INPUT_POST, 'scadenza', FILTER_SANITIZE_STRING);
    
            error_log("Tentativo di aggiunta carta: " . substr($numero_carta, -4)); // Log sicuro, mostra solo ultimi 4 numeri
    
            // Validazioni
            if (!$numero_carta || strlen($numero_carta) !== 16 || !ctype_digit($numero_carta)) {
                throw new Exception("Numero carta non valido");
            }
    
            if (!$scadenza || !preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $scadenza)) {
                throw new Exception("Data di scadenza non valida");
            }
    
            // Recupera l'ID utente da pay_utenti
            $stmt = $this->db->prepare("
                SELECT pu.id 
                FROM pay_utenti pu
                JOIN sys_utente su ON su.email = pu.email
                WHERE su.id = ?
            ");
            $stmt->execute([Session::getUserId()]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception("Utente non trovato nel sistema di pagamento");
            }
            
            $pay_utente_id = $result['id'];
    
            // Verifica che la carta non sia già memorizzata
            $stmt = $this->db->prepare("
                SELECT id 
                FROM pay_carte_credito 
                WHERE utente_id = ? AND numero_carta = ? AND attiva = 1
            ");
            $stmt->execute([$pay_utente_id, $numero_carta]);
            if ($stmt->fetch()) {
                throw new Exception("Questa carta è già stata memorizzata");
            }
    
            // Inserisci la nuova carta
            $stmt = $this->db->prepare("
                INSERT INTO pay_carte_credito (
                    utente_id, 
                    numero_carta, 
                    scadenza, 
                    attiva
                ) VALUES (?, ?, ?, 1)
            ");
    
            $result = $stmt->execute([
                $pay_utente_id,
                $numero_carta,
                $scadenza
            ]);
    
            if (!$result) {
                error_log("Errore nell'inserimento della carta: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Errore nel salvataggio della carta");
            }
    
            error_log("Carta aggiunta con successo per l'utente ID: " . $pay_utente_id);
            $_SESSION['success_message'] = "Carta aggiunta con successo";
            header('Location: index.php?page=account');
            exit;
    
        } catch (Exception $e) {
            error_log("Errore nell'aggiunta della carta: " . $e->getMessage());
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: index.php?page=account');
            exit;
        }
    }

    public function removeCard() {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("Devi effettuare il login per rimuovere una carta");
            }

            $card_id = filter_input(INPUT_POST, 'card_id', FILTER_VALIDATE_INT);
            if (!$card_id) {
                throw new Exception("ID carta non valido");
            }

            // Disattiva la carta invece di eliminarla
            $stmt = $this->db->prepare("
                UPDATE pay_carte_credito 
                SET attiva = 0 
                WHERE id = ? AND utente_id = ?
            ");
            $stmt->execute([$card_id, Session::getUserId()]);

            $_SESSION['success_message'] = "Carta rimossa con successo";
            header('Location: index.php?page=account');
            exit;

        } catch (Exception $e) {
            error_log("Errore nella rimozione della carta: " . $e->getMessage());
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: index.php?page=account');
            exit;
        }
    }

    public function ricarica() {
        try {
            $importo = filter_input(INPUT_POST, 'importo', FILTER_VALIDATE_FLOAT);
            $metodo = filter_input(INPUT_POST, 'metodo_pagamento', FILTER_SANITIZE_STRING);

            if (!$importo || $importo < 10 || $importo > 1000) {
                throw new Exception("Importo non valido");
            }

            $this->db->beginTransaction();

            // Crea la transazione
            $stmt = $this->db->prepare("
                INSERT INTO pay_transazioni (
                    id_transazione, utente_id, importo, descrizione, stato
                ) VALUES (
                    ?, 
                    (SELECT id FROM pay_utenti pu JOIN sys_utente su ON su.email = pu.email WHERE su.id = ?),
                    ?, 'Ricarica conto', 'completed'
                )
            ");
            $stmt->execute([
                'RIC_' . uniqid(),
                Session::getUserId(),
                $importo
            ]);

            // Aggiorna il saldo
            $stmt = $this->db->prepare("
                UPDATE pay_utenti pu
                JOIN sys_utente su ON su.email = pu.email
                SET pu.saldo = pu.saldo + ?
                WHERE su.id = ?
            ");
            $stmt->execute([$importo, Session::getUserId()]);

            $this->db->commit();
            Session::updateUserBalance(Session::getUserBalance() + $importo);

            $_SESSION['success_message'] = "Ricarica effettuata con successo";
            header('Location: index.php?page=account');
            exit;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Errore nella ricarica: " . $e->getMessage());
            $_SESSION['error_message'] = "Errore durante la ricarica";
            header('Location: index.php?page=account');
            exit;
        }
    }
}