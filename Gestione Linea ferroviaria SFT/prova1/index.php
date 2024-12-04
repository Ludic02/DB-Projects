<?php
// Configurazione errori per sviluppo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurazioni di sessione
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Imposta a 1 se usi HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);

// Inclusione configurazioni
require_once 'config/database.php';
require_once 'config/session.php';

// Inizializzazione sessione
Session::init();

// Debug della sessione
if (isset($_GET['debug'])) {
    Session::debug();
}

// Funzione di autoload
function customAutoloader($class_name) {
    $paths = [
        'models/',
        'controllers/',
        'config/'
    ];
    
    foreach($paths as $path) {
        $file = __DIR__ . '/' . $path . $class_name . '.php';
        if(file_exists($file)) {
            require_once $file;
            return;
        }
    }
}

// Registra l'autoloader
spl_autoload_register('customAutoloader');

// Gestione routing con sanitizzazione
$page = isset($_GET['page']) ? htmlspecialchars(strip_tags($_GET['page'])) : 'home';
$action = isset($_GET['action']) ? htmlspecialchars(strip_tags($_GET['action'])) : 'index';

// Header HTML comune
include 'views/layouts/header.php';

// Routing principale
try {
    switch($page) {
        
        case 'home':
            if (Session::isLoggedIn()) {
                $userType = Session::getUserType();
                
                if ($userType === 'esercizio') {
                    require_once 'controllers/BackofficeController.php';
                    $controller = new BackofficeController();
                    
                    // Recupera i dati dal controller
                    $data = $controller->getEsercizioData();
                    
                    // Estrai le variabili dall'array per renderle disponibili alla vista
                    $materiale_rotabile = $data['materiale_rotabile'] ?? [];
                    $orari = $data['orari'] ?? [];
                    $richieste = $data['richieste'] ?? [];
                    
                    // Include la vista
                    require 'views/backoffice/home_esercizio.php';
                } 
                else if ($userType === 'admin') {
                    require_once 'controllers/BackofficeController.php';
                    $controller = new BackofficeController();
                    $controller->index();
                }
                else if ($userType === 'esercente') {
                    require_once 'controllers/merchantController.php';
                    $controller = new MerchantController();
                    
                    try {
                        // Recupera dati dell'esercente
                        $merchantData = $controller->getMerchantData();
                        require 'views/merchant/dashboard.php';
                    } catch (Exception $e) {
                        Session::setErrorMessage($e->getMessage());
                        include 'views/home.php';
                    }
                }
                else {
                    include 'views/home.php';
                }
            } else {
                include 'views/home.php';
            }
            break;
        
            case 'backoffice':
                if (!Session::isLoggedIn()) {
                    Session::setErrorMessage("Devi effettuare il login");
                    header('Location: index.php?page=auth&action=login');
                    exit;
                }
            
                $userType = Session::getUserType();
                require_once 'controllers/BackofficeController.php';
                $controller = new BackofficeController();
            
                if ($userType === 'admin') {
                    switch($action) {
                        case 'richiediCessazione':
                            $controller->richiediCessazione();
                            break;
                        case 'richiediStraordinario':
                            $controller->richiediStraordinario();
                            break;
                        default:
                            $controller->index();
                            break;
                    }
                } elseif ($userType === 'esercizio') {
                    // Aggiungi questa sezione per gestire la richiesta AJAX
                    if ($action === 'getOrario') {
                        // Previeni qualsiasi output
                        ob_clean();
                        header('Content-Type: application/json');
                        $controller->getOrario();
                        exit; // Importante: esci dopo aver inviato la risposta JSON
                    }
            
                    switch($action) {
                        case 'componiConvoglio':
                            $controller->componiConvoglio();
                            break;
                        case 'aggiungiOrario':
                            $controller->aggiungiOrario();
                            break;
                        case 'eliminaOrario':
                            $controller->eliminaOrario();
                            break;
                            case 'modificaOrario':
                                $controller->modificaOrario();
                                break;
                        
                            case 'eliminaConvoglio':
                            $controller->eliminaConvoglio();
                            break;
                        case 'rispondiRichiesta':
                            $controller->rispondiRichiesta();
                            break;
                        default:
                            $data = $controller->getEsercizioData();
                            extract($data);
                            require 'views/backoffice/home_esercizio.php';
                            break;
                    }
                } else {
                    Session::setErrorMessage("Accesso non autorizzato");
                    header('Location: index.php');
                    exit;
                }
                break;
        
        case 'auth':
            require_once 'controllers/AuthController.php';
            $controller = new AuthController();
            
            switch($action) {
                case 'login':
                    $controller->login();
                    break;
                case 'logout':
                    $controller->logout();
                    break;
                case 'register':
                    $controller->register();
                    break;
                default:
                    include 'views/auth/login.php';
            }
            break;

        case 'pay':
            if (!Session::isLoggedIn() && $action !== 'info') {
                Session::setErrorMessage("Devi effettuare il login per accedere a PaySteam");
                header('Location: index.php?page=auth&action=login');
                exit;
            }
            
            require_once 'controllers/PayController.php';
            $controller = new PayController();
            
            try {
                switch($action) {
                    case 'addCard':
                        $controller->addCard();
                        break;
                    case 'saveCard':
                        $controller->saveCard();
                        break;
                    case 'removeCard':
                        $controller->removeCard();
                        break;
                    case 'ricarica':
                        $controller->ricarica();
                        break;
                    case 'info':
                        $controller->info();
                        break;
                    default:
                        $controller->index();
                        break;
                }
            } catch (Exception $e) {
                Session::setErrorMessage("Errore in PaySteam: " . $e->getMessage());
                header('Location: index.php');
                exit;
            }
            break;

        case 'payment':
                if (!Session::isLoggedIn()) {
                    Session::setErrorMessage("Devi effettuare il login per effettuare pagamenti");
                    header('Location: index.php?page=auth&action=login');
                    exit;
                }
                
                require_once 'controllers/PaymentController.php';
                $controller = new PaymentController();
                
                try {
                    switch($action) {
                        case 'checkout':
                            $controller->checkout();
                            break;
                        case 'process':
                            $controller->process();
                            break;
                        case 'confirm':
                            $controller->confirm();
                            break;
                        default:
                            header('Location: index.php');
                            exit;
                    }
                } catch (Exception $e) {
                    error_log("Errore nel pagamento: " . $e->getMessage());
                    Session::setErrorMessage("Errore durante il pagamento: " . $e->getMessage());
                    header('Location: index.php?page=trains');
                    exit;
                }
                break;
        
        case 'api':
            require_once 'controllers/ApiController.php';
            $controller = new ApiController();
            
            switch($action) {
                case 'payment':
                    $controller->processPayment();
                    break;
                case 'payment_status':
                    $controller->getPaymentStatus();
                    break;
                default:
                    header('HTTP/1.0 404 Not Found');
                    echo json_encode(['error' => 'Endpoint not found']);
                    exit;
            }
            break;
        
        case 'trains':
                require_once 'controllers/TrainController.php';
                $controller = new TrainController();
                
                try {
                    switch($action) {
                        case 'view':
                            $controller->view();
                            break;
                        case 'prenota':
                            $controller->prenota();
                            break;
                        case 'prenota_conferma':
                            $controller->prenota_conferma();
                            break;
                        case 'conferma':
                            $controller->conferma();
                            break;
                        case 'cancel_booking':
                            $controller->cancel_booking();
                            break;
                        default:
                            $controller->index();
                            break;
                    }
                } catch (Exception $e) {
                    error_log("Errore in trains controller: " . $e->getMessage());
                    Session::setErrorMessage($e->getMessage());
                    header('Location: index.php');
                    exit;
                }
                break;
           
            
        case 'merchant':
            if (!Session::isLoggedIn() || Session::getUserType() !== 'esercente') {
                Session::setErrorMessage("Accesso non autorizzato");
                header('Location: index.php');
                exit;
            }
            
            require_once 'controllers/MerchantController.php';
            $controller = new MerchantController();
            
            switch($action) {
                case 'dashboard':
                    $controller->dashboard();
                    break;
                case 'transactions':
                    $controller->transactions();
                    break;
                case 'withdraw':
                    $controller->withdraw();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'transactions':
            if (!Session::isLoggedIn()) {
                Session::setErrorMessage("Devi effettuare il login per visualizzare le transazioni");
                header('Location: index.php?page=auth&action=login');
                exit;
            }
            
            require_once 'controllers/TransactionController.php';
            $controller = new TransactionController();
            
            switch($action) {
                case 'history':
                    $controller->history();
                    break;
                case 'details':
                    $controller->details();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'account':
            if (!Session::isLoggedIn()) {
                Session::setErrorMessage("Devi effettuare il login per gestire il tuo account");
                header('Location: index.php?page=auth&action=login');
                exit;
            }
            
            require_once 'controllers/AccountController.php';
            $controller = new AccountController();
            
            switch($action) {
                case 'profile':
                    $controller->profile();
                    break;
                case 'settings':
                    $controller->settings();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

            case 'dashboard':
                if (!Session::isLoggedIn()) {
                    Session::setErrorMessage("Devi effettuare il login");
                    header('Location: index.php?page=auth&action=login');
                    exit;
                }
            
                $type = $_GET['type'] ?? '';
                $userType = Session::getUserType();
            
                if ($type === 'esercizio' && $userType === 'esercizio') {
                    require_once 'controllers/BackofficeController.php';
                    $controller = new BackofficeController();
                    $controller->homeEsercizio();
                } else {
                    Session::setErrorMessage("Accesso non autorizzato");
                    header('Location: index.php');
                    exit;
                }
                break;


        default:
            // 404 - Pagina non trovata
            header("HTTP/1.0 404 Not Found");
            include 'views/errors/404.php';
            break;
    }
} catch (Exception $e) {
    // Log dell'errore
    error_log("Errore nell'applicazione: " . $e->getMessage());
    
    // Mostra un messaggio di errore generico all'utente
    Session::setErrorMessage("Si è verificato un errore. Riprova più tardi.");
    header('Location: index.php');
    exit;
}

// Footer HTML comune
include 'views/layouts/footer.php';

// Pulizia buffer di output
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>