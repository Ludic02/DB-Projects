<?php
class PayController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index() {
        if (!Session::isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        try {
            // Recupera email utente
            $stmt = $this->db->prepare("SELECT email FROM sys_utente WHERE id = ?");
            $stmt->execute([Session::getUserId()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("Utente non trovato");
            }

            // Verifica/crea account PaySteam
            $stmt = $this->db->prepare("SELECT * FROM pay_utenti WHERE email = ?");
            $stmt->execute([$user['email']]);
            $payUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payUser) {
                $stmt = $this->db->prepare("INSERT INTO pay_utenti (email, saldo) VALUES (?, 0.00)");
                $stmt->execute([$user['email']]);
                
                $stmt = $this->db->prepare("SELECT * FROM pay_utenti WHERE email = ?");
                $stmt->execute([$user['email']]);
                $payUser = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Recupera le carte
            $stmt = $this->db->prepare("
                SELECT *, 
                    CONCAT('**** **** **** ', RIGHT(numero, 4)) as numero_mascherato
                FROM pay_carte_credito 
                WHERE email = ?
                ORDER BY data_aggiunta DESC
            ");
            $stmt->execute([$user['email']]);
            $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Recupera transazioni recenti
            $stmt = $this->db->prepare("
                SELECT 
                    t.codice_transazione,
                    t.importo,
                    t.descrizione,
                    t.stato,
                    t.data_creazione,
                    e.nome_esercente
                FROM pay_transazioni t
                LEFT JOIN pay_esercenti e ON t.esercente_id = e.id
                WHERE t.cliente_email = ?
                ORDER BY t.data_creazione DESC
                LIMIT 10
            ");
            $stmt->execute([$user['email']]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $balance = $payUser['saldo'];

            include 'views/pay/dashboard.php';

        } catch (Exception $e) {
            error_log("Errore in PayController::index: " . $e->getMessage());
            Session::setErrorMessage("Errore nel recupero dei dati");
            header('Location: index.php');
            exit;
        }
    }

    public function addCard() {
        if (!Session::isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        include 'views/pay/add_card.php';
    }

    public function saveCard() {
        if (!Session::isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        try {
            $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING);
            $scadenza = filter_input(INPUT_POST, 'scadenza', FILTER_SANITIZE_STRING);
            $cvv = filter_input(INPUT_POST, 'cvv', FILTER_SANITIZE_STRING);

            if (!$numero || !$scadenza || !$cvv) {
                throw new Exception("Tutti i campi sono obbligatori");
            }

            // Recupera email utente
            $stmt = $this->db->prepare("SELECT email FROM sys_utente WHERE id = ?");
            $stmt->execute([Session::getUserId()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("
                INSERT INTO pay_carte_credito (email, numero, scadenza, cvv)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user['email'], $numero, $scadenza, $cvv]);

            Session::setSuccessMessage("Carta aggiunta con successo");

        } catch (Exception $e) {
            Session::setErrorMessage($e->getMessage());
        }

        header('Location: index.php?page=pay');
        exit;
    }

    public function ricarica() {
        if (!Session::isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $importo = filter_input(INPUT_POST, 'importo', FILTER_VALIDATE_FLOAT);
                $carta_id = filter_input(INPUT_POST, 'card_id', FILTER_VALIDATE_INT);

                if (!$importo || $importo <= 0) {
                    throw new Exception("Importo non valido");
                }

                if (!$carta_id) {
                    throw new Exception("Seleziona una carta");
                }

                $this->db->beginTransaction();

                try {
                    // Recupera email utente
                    $stmt = $this->db->prepare("SELECT email FROM sys_utente WHERE id = ?");
                    $stmt->execute([Session::getUserId()]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Verifica che esista l'esercente SFT
                    $stmt = $this->db->prepare("SELECT id FROM pay_esercenti WHERE email = 'admin@sft.it'");
                    $stmt->execute();
                    $esercente = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$esercente) {
                        $stmt = $this->db->prepare("
                            INSERT INTO pay_esercenti (email, nome_esercente, saldo)
                            VALUES ('admin@sft.it', 'Sistema Ferroviario Turistico', 0.00)
                        ");
                        $stmt->execute();
                        $esercente_id = $this->db->lastInsertId();
                    } else {
                        $esercente_id = $esercente['id'];
                    }

                    // Aggiorna il saldo dell'utente
                    $stmt = $this->db->prepare("
                        UPDATE pay_utenti 
                        SET saldo = saldo + ? 
                        WHERE email = ?
                    ");
                    $stmt->execute([$importo, $user['email']]);

                    // Registra la transazione
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
                            CONCAT('RIC_', ?),
                            ?,
                            ?,
                            ?,
                            'Ricarica conto',
                            'COMPLETED',
                            '',
                            ''
                        )
                    ");
                    
                    $stmt->execute([
                        uniqid(),
                        $esercente_id,
                        $user['email'],
                        $importo
                    ]);

                    $this->db->commit();
                    Session::setSuccessMessage("Ricarica di €" . number_format($importo, 2) . " effettuata con successo");
                    header('Location: index.php?page=pay');
                    exit;

                } catch (Exception $e) {
                    $this->db->rollBack();
                    throw $e;
                }

            } catch (Exception $e) {
                Session::setErrorMessage($e->getMessage());
                header('Location: index.php?page=pay&action=ricarica');
                exit;
            }
        }

        // Recupera le carte per il form
        $stmt = $this->db->prepare("
            SELECT *, 
                CONCAT('**** **** **** ', RIGHT(numero, 4)) as numero_mascherato
            FROM pay_carte_credito 
            WHERE email = (SELECT email FROM sys_utente WHERE id = ?)
            ORDER BY data_aggiunta DESC
        ");
        $stmt->execute([Session::getUserId()]);
        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include 'views/pay/ricarica.php';
    }

    public function removeCard() {
        if (!Session::isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        try {
            $carta_id = filter_input(INPUT_POST, 'card_id', FILTER_VALIDATE_INT);
            
            if (!$carta_id) {
                throw new Exception("ID carta non valido");
            }

            $stmt = $this->db->prepare("
                DELETE FROM pay_carte_credito 
                WHERE id = ? AND email = (
                    SELECT email FROM sys_utente WHERE id = ?
                )
            ");
            $stmt->execute([$carta_id, Session::getUserId()]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Carta non trovata o non autorizzato");
            }

            Session::setSuccessMessage("Carta rimossa con successo");

        } catch (Exception $e) {
            Session::setErrorMessage($e->getMessage());
        }

        header('Location: index.php?page=pay');
        exit;
    }
}
