<?php
class BackofficeController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Dashboard amministrativa
    public function index() {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'admin') {
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }

        try {
            // Recupera tutti i treni con la loro occupazione
            $stmt = $this->db->prepare("
                SELECT 
                    t.id as id_treno,
                    t.nome as nome_treno,
                    t.posti_totali,
                    COUNT(DISTINCT p.id) as numero_prenotazioni,
                    COALESCE(SUM(p.numero_posti), 0) as posti_occupati,
                    GROUP_CONCAT(DISTINCT mt.nome ORDER BY c.ordine SEPARATOR ', ') as composizione
                FROM sft_treno t
                LEFT JOIN sft_composizione c ON t.id = c.convoglio_id
                LEFT JOIN sft_treno mt ON c.materiale_id = mt.id
                LEFT JOIN sft_prenotazione p ON t.id = p.treno_id 
                    AND p.stato = 'CONFERMATA'
                    AND p.stato_pagamento = 'PAGATO'
                WHERE t.attivo = 1
                GROUP BY t.id, t.nome, t.posti_totali
                ORDER BY t.nome
            ");
            $stmt->execute();
            $treni = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Recupera le richieste inviate
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    t.nome as nome_treno
                FROM sft_richieste r
                LEFT JOIN sft_treno t ON r.treno_id = t.id
                ORDER BY r.data_richiesta DESC
            ");
            $stmt->execute();
            $richieste = $stmt->fetchAll(PDO::FETCH_ASSOC);

            include __DIR__ . '/../views/backoffice/admin_dashboard.php';

        } catch (Exception $e) {
            error_log("Errore nel backoffice admin: " . $e->getMessage());
            Session::setErrorMessage("Errore nel recupero dei dati");
            header('Location: index.php');
            exit;
        }
    }

    // Dashboard esercizio
    
    public function getEsercizioData() {
        try {
            error_log("Inizio recupero dati esercizio");
    
            // Recupera materiale rotabile (locomotive, carrozze e bagagliai)
            $stmt = $this->db->prepare("
                SELECT 
                    t.id,
                    t.nome,
                    t.tipo,
                    t.posti_totali,
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 
                            FROM sft_composizione c
                            WHERE c.materiale_id = t.id
                        ) THEN 0
                        ELSE 1 
                    END as disponibile
                FROM sft_treno t
                WHERE t.attivo = 1
                AND t.tipo != 'CONVOGLIO'
                ORDER BY 
                    CASE t.tipo 
                        WHEN 'locomotiva' THEN 1
                        WHEN 'automotrice' THEN 2
                        WHEN 'carrozza' THEN 3 
                        WHEN 'bagagliaio' THEN 4
                        ELSE 5 
                    END,
                    t.nome
            ");
            $stmt->execute();
            $materiale_rotabile = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Materiale rotabile recuperato: " . count($materiale_rotabile) . " elementi");
    
            // Debug di ogni elemento del materiale rotabile
            foreach ($materiale_rotabile as $item) {
                error_log("Item: " . print_r($item, true));
            }
    
            $stmt = $this->db->prepare("
    SELECT 
        c.id,
        c.nome,
        c.posti_totali,
        GROUP_CONCAT(
            DISTINCT CONCAT(
                m.nome,
                CASE 
                    WHEN m.tipo IN ('carrozza', 'bagagliaio') 
                    THEN CONCAT(' (', m.posti_totali, ' posti)')
                    ELSE ''
                END
            ) 
            ORDER BY comp.ordine SEPARATOR ' + '
        ) as composizione,
        COUNT(DISTINCT o.id) as num_orari
    FROM sft_treno c
    LEFT JOIN sft_composizione comp ON c.id = comp.convoglio_id
    LEFT JOIN sft_treno m ON comp.materiale_id = m.id
    LEFT JOIN sft_orario o ON c.id = o.treno_id
    WHERE c.tipo = 'CONVOGLIO'
    GROUP BY c.id, c.nome, c.posti_totali
    ORDER BY c.nome
");
$stmt->execute();
$convogli = $stmt->fetchAll(PDO::FETCH_ASSOC);
error_log("Convogli recuperati: " . count($convogli) . " elementi");
            
            
            // Query specifica per locomotive e automotrici disponibili
            $stmt = $this->db->prepare("
            SELECT 
                t.id,
                t.nome,
                t.tipo,
                t.posti_totali
            FROM sft_treno t
            WHERE t.attivo = 1
            AND (t.tipo = 'locomotiva' OR t.tipo = 'automotrice')
            AND t.id NOT IN (
                SELECT DISTINCT materiale_id 
                FROM sft_composizione 
                WHERE materiale_id IS NOT NULL
            )
            ORDER BY 
                CASE t.tipo
                    WHEN 'locomotiva' THEN 1
                    WHEN 'automotrice' THEN 2
                END,
                t.nome ASC
            ");
            
            $stmt->execute();
            $locomotive_disponibili = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Locomotive disponibili recuperate: " . count($locomotive_disponibili) . " elementi");
    
            // Recupera orari
$stmt = $this->db->prepare("
SELECT 
    o.*,
    t.nome AS nome_treno,
    s.nome AS nome_stazione,
    COALESCE(om.orario, o.orario) as orario_effettivo,
    COALESCE(om.giorni, o.giorni) as giorni_effettivi
FROM sft_orario o
LEFT JOIN sft_orario_modificato om ON o.id = om.orario_originale_id
JOIN sft_treno t ON o.treno_id = t.id
JOIN sft_stazione s ON o.stazione_id = s.id
WHERE t.tipo = 'CONVOGLIO'
ORDER BY t.nome, o.orario, o.tipo
");

$stmt->execute();
$orari = $stmt->fetchAll(PDO::FETCH_ASSOC);
error_log("Orari recuperati: " . count($orari) . " elementi");
    
            // Recupera stazioni
            $stmt = $this->db->prepare("
                SELECT id, nome, km 
                FROM sft_stazione 
                ORDER BY km ASC
            ");
            $stmt->execute();
            $stazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Stazioni recuperate: " . count($stazioni) . " elementi");
    
            // Recupera richieste pendenti
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    t.nome as nome_treno
                FROM sft_richieste r
                JOIN sft_treno t ON r.treno_id = t.id
                WHERE r.stato = 'PENDING'
                ORDER BY r.data_richiesta DESC
            ");
            $stmt->execute();
            $richieste = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Richieste recuperate: " . count($richieste) . " elementi");
    
            // Prepara i dati da restituire
            $data = [
                'materiale_rotabile' => $materiale_rotabile,
                'locomotive_disponibili' => $locomotive_disponibili,
                'convogli' => $convogli,  // Aggiungi questa linea
                'orari' => $orari,
                'stazioni' => $stazioni,
                'richieste' => $richieste
            ];
    
            error_log("Dati pronti per essere restituiti");
            return $data;
    
        } catch (Exception $e) {
            error_log("Errore nel recupero dati esercizio: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'materiale_rotabile' => [],
                'locomotive_disponibili' => [], // Aggiunto il nuovo array vuoto
                'orari' => [],
                'stazioni' => [],
                'richieste' => []
            ];
        }
        
    }
    public function homeEsercizio() {
        header('Location: index.php');
        exit;
    }
    
    
    
    public function richiediCessazione() {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'admin') {
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }
    
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Prima fase: mostra form di conferma
                if (isset($_POST['richiedi_cessazione'])) {
                    $treno_id = filter_input(INPUT_POST, 'treno_id', FILTER_VALIDATE_INT);
                    
                    if (!$treno_id) {
                        throw new Exception("ID treno non valido");
                    }
    
                    // Verifica che il treno esista e non abbia prenotazioni
                    $stmt = $this->db->prepare("
                        SELECT 
                            t.id as id_treno,
                            t.nome as nome_treno,
                            COUNT(p.id) as num_prenotazioni
                        FROM sft_treno t
                        LEFT JOIN sft_prenotazione p ON t.id = p.treno_id 
                            AND p.stato = 'CONFERMATA'
                            AND p.stato_pagamento = 'PAGATO'
                        WHERE t.id = ?
                        GROUP BY t.id, t.nome
                    ");
                    $stmt->execute([$treno_id]);
                    $treno = $stmt->fetch(PDO::FETCH_ASSOC);
    
                    if (!$treno) {
                        throw new Exception("Treno non trovato");
                    }
    
                    if ($treno['num_prenotazioni'] > 0) {
                        throw new Exception("Impossibile richiedere la cessazione: ci sono prenotazioni attive");
                    }
    
                    $_SESSION['show_cessazione_form'] = true;
                    $_SESSION['cessazione_treno'] = [
                        'id_treno' => $treno['id_treno'],
                        'nome_treno' => $treno['nome_treno']
                    ];
                    
                    header("Location: index.php?page=backoffice");
                    exit;
                }
    
                // Seconda fase: processamento richiesta
                if (isset($_POST['conferma_cessazione'])) {
                    $treno_id = filter_input(INPUT_POST, 'treno_id', FILTER_VALIDATE_INT);
                    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
    
                    if (!$treno_id || !$motivo) {
                        throw new Exception("Dati mancanti");
                    }
    
                    // Inserisci la richiesta
                    $stmt = $this->db->prepare("
                        INSERT INTO sft_richieste (
                            tipo, treno_id, motivo, stato, data_richiesta
                        ) VALUES (
                            'CESSAZIONE', ?, ?, 'PENDING', NOW()
                        )
                    ");
                    $stmt->execute([$treno_id, $motivo]);
    
                    // Pulisci le variabili di sessione
                    unset($_SESSION['show_cessazione_form']);
                    unset($_SESSION['cessazione_treno']);
    
                    Session::setSuccessMessage("Richiesta di cessazione inviata con successo");
                }
            }
    
        } catch (Exception $e) {
            Session::setErrorMessage($e->getMessage());
        }
    
        header("Location: index.php?page=backoffice");
        exit;
    }

    public function richiediStraordinario() {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'admin') {
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }
    
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $treno_id = filter_input(INPUT_POST, 'treno_id', FILTER_VALIDATE_INT);
                $data_prevista = filter_input(INPUT_POST, 'data_richiesta', FILTER_SANITIZE_STRING);
                $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
    
                if (!$treno_id || !$data_prevista || !$motivo) {
                    throw new Exception("Tutti i campi sono obbligatori");
                }
    
                // Verifica che la data sia futura
                if (strtotime($data_prevista) <= strtotime(date('Y-m-d'))) {
                    throw new Exception("La data deve essere futura");
                }
    
                // Inserisci la richiesta
                $stmt = $this->db->prepare("
                    INSERT INTO sft_richieste (
                        tipo, 
                        treno_id, 
                        data_prevista, 
                        motivo, 
                        stato, 
                        data_richiesta
                    ) VALUES (
                        'STRAORDINARIO',
                        ?,
                        ?,
                        ?,
                        'PENDING',
                        NOW()
                    )
                ");
                $stmt->execute([$treno_id, $data_prevista, $motivo]);
    
                Session::setSuccessMessage("Richiesta treno straordinario inviata con successo");
            }
    
        } catch (Exception $e) {
            Session::setErrorMessage($e->getMessage());
        }
    
        header("Location: index.php?page=backoffice");
        exit;
    }
    
    // Gestione risposte alle richieste
    public function rispondiRichiesta() {
        error_log("=== INIZIO RISPOSTA RICHIESTA ===");
        error_log("POST ricevuto: " . print_r($_POST, true));
    
        if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
            error_log("Utente non autorizzato");
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }
    
        try {
            // Validazione input
            $richiesta_id = isset($_POST['richiesta_id']) ? (int)$_POST['richiesta_id'] : 0;
            $azione = isset($_POST['azione']) ? $_POST['azione'] : '';
            $nota_risposta = isset($_POST['nota_risposta']) ? trim($_POST['nota_risposta']) : '';
    
            error_log("Dati elaborati:");
            error_log("Richiesta ID: $richiesta_id");
            error_log("Azione: $azione");
            error_log("Nota: $nota_risposta");
    
            // Validazione
            if (!$richiesta_id || !in_array($azione, ['APPROVA', 'RIFIUTA']) || empty($nota_risposta)) {
                error_log("Validazione fallita");
                throw new Exception("Dati non validi");
            }
    
            // Query diretta per debug
            error_log("Esecuzione query di verifica");
            $check = $this->db->prepare("SELECT * FROM sft_richieste WHERE id = ?");
            $check->execute([$richiesta_id]);
            $richiesta = $check->fetch(PDO::FETCH_ASSOC);
            error_log("Richiesta trovata: " . print_r($richiesta, true));
    
            // Aggiornamento
            error_log("Tentativo di aggiornamento");
            $stmt = $this->db->prepare("
                UPDATE sft_richieste 
                SET 
                    stato = ?, 
                    nota_risposta = ?, 
                    data_risposta = NOW() 
                WHERE id = ?
            ");
    
            $nuovo_stato = ($azione === 'APPROVA') ? 'APPROVATA' : 'RIFIUTATA';
            
            $result = $stmt->execute([
                $nuovo_stato,
                $nota_risposta,
                $richiesta_id
            ]);
    
            error_log("Risultato aggiornamento: " . ($result ? 'successo' : 'fallito'));
            error_log("Righe modificate: " . $stmt->rowCount());
    
            if ($result && $stmt->rowCount() > 0) {
                error_log("Aggiornamento completato con successo");
                Session::setSuccessMessage("Richiesta " . strtolower($nuovo_stato) . " con successo");
            } else {
                error_log("Nessuna riga aggiornata");
                throw new Exception("Impossibile aggiornare la richiesta");
            }
    
        } catch (Exception $e) {
            error_log("ERRORE: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            Session::setErrorMessage($e->getMessage());
        }
    
        error_log("=== FINE RISPOSTA RICHIESTA ===");
        header('Location: index.php?page=backoffice&action=esercizio');
        exit;
    }

    // Gestione composizione convogli
    public function componiConvoglio() {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }
    
        try {
            $nome = htmlspecialchars(strip_tags($_POST['nome_convoglio']));
            $locomotiva_id = filter_input(INPUT_POST, 'locomotiva_id', FILTER_VALIDATE_INT);
            $carrozze = isset($_POST['carrozze']) ? array_map('intval', $_POST['carrozze']) : [];
    
            // Debug
            error_log("Tentativo di composizione convoglio:");
            error_log("Nome: " . $nome);
            error_log("Locomotiva ID: " . $locomotiva_id);
            error_log("Carrozze: " . print_r($carrozze, true));
    
            if (!$nome || !$locomotiva_id || empty($carrozze)) {
                throw new Exception("Tutti i campi sono obbligatori");
            }
    
            $this->db->beginTransaction();
    
            try {
                // Verifica disponibilità materiale
                $materiale_ids = array_merge([$locomotiva_id], $carrozze);
                $placeholders = str_repeat('?,', count($materiale_ids) - 1) . '?';
                
                $stmt = $this->db->prepare("
                    SELECT id, nome, tipo 
                    FROM sft_treno 
                    WHERE id IN ($placeholders)
                    AND attivo = 1
                    AND tipo IN ('locomotiva', 'automotrice', 'carrozza', 'bagagliaio')
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM sft_composizione c
                        WHERE c.materiale_id = sft_treno.id
                    )
                ");
                $stmt->execute($materiale_ids);
                $materiale = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                error_log("Materiale trovato: " . print_r($materiale, true));
    
                if (count($materiale) != count($materiale_ids)) {
                    throw new Exception("Parte del materiale rotabile selezionato non è disponibile");
                }
    
                // Calcola posti totali
                $stmt = $this->db->prepare("
                    SELECT SUM(posti_totali) as totale_posti
                    FROM sft_treno
                    WHERE id IN (" . implode(',', $carrozze) . ")
                ");
                $stmt->execute();
                $posti = $stmt->fetch(PDO::FETCH_ASSOC);
    
                // Crea il convoglio
                $stmt = $this->db->prepare("
                    INSERT INTO sft_treno (nome, tipo, posti_totali, attivo)
                    VALUES (?, 'CONVOGLIO', ?, 1)
                ");
                $stmt->execute([$nome, $posti['totale_posti']]);
                $convoglio_id = $this->db->lastInsertId();
    
                // Registra composizione
                $stmt = $this->db->prepare("
                    INSERT INTO sft_composizione (convoglio_id, materiale_id, ordine)
                    VALUES (?, ?, ?)
                ");
    
                // Inserisci locomotiva
                $stmt->execute([$convoglio_id, $locomotiva_id, 0]);
    
                // Inserisci carrozze
                foreach ($carrozze as $index => $carrozza_id) {
                    $stmt->execute([$convoglio_id, $carrozza_id, $index + 1]);
                }
    
                $this->db->commit();
                Session::setSuccessMessage("Convoglio composto con successo");
    
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
    
        } catch (Exception $e) {
            error_log("Errore nella composizione del convoglio: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
        }
    
        header('Location: index.php?page=backoffice&action=esercizio');
        exit;
    }

    public function eliminaConvoglio() {
        try {
            $convoglio_id = filter_input(INPUT_POST, 'convoglio_id', FILTER_VALIDATE_INT);
            
            if (!$convoglio_id) {
                throw new Exception("ID convoglio non valido");
            }
    
            $this->db->beginTransaction();
    
            try {
                // Prima elimina le richieste associate
                $stmt = $this->db->prepare("DELETE FROM sft_richieste WHERE treno_id = ?");
                $stmt->execute([$convoglio_id]);
    
                // Poi elimina le composizioni
                $stmt = $this->db->prepare("DELETE FROM sft_composizione WHERE convoglio_id = ?");
                $stmt->execute([$convoglio_id]);
    
                // Elimina gli orari
                $stmt = $this->db->prepare("DELETE FROM sft_orario WHERE treno_id = ?");
                $stmt->execute([$convoglio_id]);
    
                // Infine elimina il convoglio
                $stmt = $this->db->prepare("DELETE FROM sft_treno WHERE id = ?");
                $stmt->execute([$convoglio_id]);
    
                $this->db->commit();
                Session::setSuccessMessage("Convoglio eliminato con successo");
    
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
    
        } catch (Exception $e) {
            Session::setErrorMessage($e->getMessage());
        }
    
        header('Location: index.php?page=backoffice&action=esercizio');
        exit;
    }

    // Gestione orari
    public function aggiungiOrario() {
        error_log("=== INIZIO AGGIUNGI ORARIO ===");
        error_log("POST ricevuti: " . print_r($_POST, true));
    
        if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }
    
        try {
            // Validazione input
            $treno_id = filter_input(INPUT_POST, 'treno_id', FILTER_VALIDATE_INT);
            $stazione_id = filter_input(INPUT_POST, 'stazione_id', FILTER_VALIDATE_INT);
            $orario = filter_input(INPUT_POST, 'orario', FILTER_SANITIZE_STRING);
            $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
            $data_viaggio = filter_input(INPUT_POST, 'booking_date', FILTER_SANITIZE_STRING);
    
            error_log("Dati filtrati:");
            error_log("Treno ID: " . $treno_id);
            error_log("Stazione ID: " . $stazione_id);
            error_log("Orario: " . $orario);
            error_log("Tipo: " . $tipo);
            error_log("Data Viaggio: " . $data_viaggio);
    
            // Validazione
            if (!$treno_id || !$stazione_id || !$orario || !$tipo || !$data_viaggio) {
                throw new Exception("Tutti i campi sono obbligatori");
            }
    
            // Verifica che la data sia futura
            $data = new DateTime($data_viaggio);
            $oggi = new DateTime();
            if ($data < $oggi) {
                throw new Exception("La data deve essere futura");
            }
    
            // Inserimento orario usando la struttura esistente
            $stmt = $this->db->prepare("
                INSERT INTO sft_orario (
                    treno_id,
                    stazione_id,
                    orario,
                    tipo,
                    giorni
                ) VALUES (?, ?, ?, ?, ?)
            ");
    
            $result = $stmt->execute([
                $treno_id,
                $stazione_id,
                $orario,
                $tipo,
                $data_viaggio  // Usiamo il campo giorni per salvare la data
            ]);
    
            if ($result) {
                error_log("Orario inserito con successo");
                Session::setSuccessMessage("Orario aggiunto con successo");
            } else {
                error_log("Errore nell'inserimento: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Errore nell'inserimento dell'orario");
            }
    
        } catch (Exception $e) {
            error_log("ERRORE: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
        }
    
        header('Location: index.php?page=backoffice&action=esercizio');
        exit;
    }
    public function eliminaOrario() {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
            Session::setErrorMessage("Accesso non autorizzato");
            header('Location: index.php');
            exit;
        }
    
        try {
            $orario_id = filter_input(INPUT_POST, 'orario_id', FILTER_VALIDATE_INT);
            
            if (!$orario_id) {
                throw new Exception("Orario non valido");
            }
            
            // Verifica che non ci siano prenotazioni future per questo orario
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as num_prenotazioni
                FROM sft_prenotazione p
                JOIN sft_orario o ON p.treno_id = o.treno_id
                WHERE o.id = ? 
                AND p.stato_pagamento = 'PAGATO'
                AND p.data_viaggio >= CURDATE()
            ");
            $stmt->execute([$orario_id]);
            
            if ($stmt->fetch(PDO::FETCH_ASSOC)['num_prenotazioni'] > 0) {
                throw new Exception("Non è possibile eliminare un orario con prenotazioni attive");
            }
    
            // Inizia la transazione
            $this->db->beginTransaction();
    
            try {
                // Prima elimina le modifiche associate all'orario
                $stmt = $this->db->prepare("DELETE FROM sft_orario_modificato WHERE orario_originale_id = ?");
                $stmt->execute([$orario_id]);
    
                // Poi elimina l'orario
                $stmt = $this->db->prepare("DELETE FROM sft_orario WHERE id = ?");
                $stmt->execute([$orario_id]);
    
                // Conferma le modifiche
                $this->db->commit();
                Session::setSuccessMessage("Orario eliminato con successo");
    
            } catch (Exception $e) {
                // In caso di errore, annulla tutte le modifiche
                $this->db->rollBack();
                throw new Exception("Errore durante l'eliminazione dell'orario");
            }
    
        } catch (Exception $e) {
            error_log("Errore nell'eliminazione dell'orario: " . $e->getMessage());
            Session::setErrorMessage($e->getMessage());
        }
    
        header('Location: index.php?page=backoffice&action=esercizio');
        exit;
    }

    public function getOrariConvoglio() {
        if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
            http_response_code(403);
            echo json_encode(['error' => 'Non autorizzato']);
            exit;
        }
    
        try {
            $convoglio_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            
            if (!$convoglio_id) {
                throw new Exception("ID convoglio non valido");
            }
    
            $stmt = $this->db->prepare("
                SELECT 
                    sp.nome as stazione_partenza,
                    sa.nome as stazione_arrivo,
                    TIME_FORMAT(o.orario_partenza, '%H:%i') as orario_partenza,
                    TIME_FORMAT(o.orario_arrivo, '%H:%i') as orario_arrivo,
                    o.giorni
                FROM sft_orario o
                JOIN sft_stazione sp ON o.stazione_partenza_id = sp.id
                JOIN sft_stazione sa ON o.stazione_arrivo_id = sa.id
                WHERE o.treno_id = ?
                ORDER BY o.orario_partenza
            ");
            $stmt->execute([$convoglio_id]);
            $orari = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            header('Content-Type: application/json');
            echo json_encode($orari);
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getOrario() {
        try {
            // Evita qualsiasi output
            ob_clean();
            
            // Imposta gli headers
            header('Content-Type: application/json');
            
            // Verifica autenticazione
            if (!Session::isLoggedIn()) {
                throw new Exception("Non autorizzato");
            }
    
            // Ottieni l'ID e verifica
            $orario_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$orario_id) {
                throw new Exception("ID orario non valido");
            }
    
            // Query per recuperare l'orario
            $stmt = $this->db->prepare("
                SELECT 
                    o.id,
                    COALESCE(om.orario, TIME_FORMAT(o.orario, '%H:%i')) as orario,
                    COALESCE(om.giorni, o.giorni) as giorni
                FROM sft_orario o
                LEFT JOIN sft_orario_modificato om ON o.id = om.orario_originale_id
                WHERE o.id = ?
            ");
            
            $stmt->execute([$orario_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception("Orario non trovato");
            }
    
            // Converti la data nel formato corretto
            if (isset($result['giorni'])) {
                $result['giorni'] = date('Y-m-d', strtotime($result['giorni']));
            }
    
            echo json_encode($result);
            exit;
    
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    public function modificaOrario() {
        error_log("=== INIZIO MODIFICA ORARIO ===");
        error_log("POST ricevuti: " . print_r($_POST, true));
        
        try {
            if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
                throw new Exception("Accesso non autorizzato");
            }
    
            // Validazione input
            $orario_id = filter_input(INPUT_POST, 'orario_id', FILTER_VALIDATE_INT);
            $nuovo_orario = filter_input(INPUT_POST, 'orario', FILTER_SANITIZE_STRING);
            $nuova_data = filter_input(INPUT_POST, 'giorni', FILTER_SANITIZE_STRING);
    
            error_log("Dati da salvare:");
            error_log("ID: " . $orario_id);
            error_log("Nuovo orario: " . $nuovo_orario);
            error_log("Nuova data: " . $nuova_data);
    
            if (!$orario_id || !$nuovo_orario || !$nuova_data) {
                throw new Exception("Dati mancanti o non validi");
            }
    
            $this->db->beginTransaction();
    
            try {
                // Verifica se esiste già una modifica
                $stmt = $this->db->prepare("
                    SELECT id 
                    FROM sft_orario_modificato 
                    WHERE orario_originale_id = ?
                ");
                $stmt->execute([$orario_id]);
                $modifica_esistente = $stmt->fetch();
    
                if ($modifica_esistente) {
                    error_log("Aggiornamento modifica esistente");
                    $stmt = $this->db->prepare("
                        UPDATE sft_orario_modificato 
                        SET orario = ?,
                            giorni = ?,
                            data_modifica = CURRENT_TIMESTAMP
                        WHERE orario_originale_id = ?
                    ");
                    $result = $stmt->execute([$nuovo_orario, $nuova_data, $orario_id]);
                    error_log("Update result: " . ($result ? 'success' : 'fail'));
                } else {
                    error_log("Inserimento nuova modifica");
                    $stmt = $this->db->prepare("
                        INSERT INTO sft_orario_modificato 
                        (orario_originale_id, orario, giorni)
                        VALUES (?, ?, ?)
                    ");
                    $result = $stmt->execute([$orario_id, $nuovo_orario, $nuova_data]);
                    error_log("Insert result: " . ($result ? 'success' : 'fail'));
                }
    
                $this->db->commit();
                error_log("Transazione completata con successo");
                Session::setSuccessMessage("Orario modificato con successo");
    
            } catch (Exception $e) {
                $this->db->rollBack();
                throw new Exception("Errore nel salvataggio: " . $e->getMessage());
            }
    
        } catch (Exception $e) {
            error_log("ERRORE: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            Session::setErrorMessage($e->getMessage());
        }
    
        header('Location: index.php?page=backoffice&action=esercizio');
        exit;
    }
    
    
} // Chiusura della classe
    ?>