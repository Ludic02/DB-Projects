<?php
class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            try {
                $stmt = $this->db->prepare("SELECT * FROM sys_utente WHERE email = ? AND password = ? AND attivo = 1");
                $stmt->execute([$email, $password]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    Session::setLoginData($user['id'], $user['tipo'], $user['email']);
                    Session::setSuccessMessage("Login effettuato con successo");
                    
                    // Reindirizzamento semplificato
                    header("Location: index.php");
                    exit();
                } else {
                    Session::setErrorMessage("Credenziali non valide");
                }
            } catch (Exception $e) {
                error_log("Database error: " . $e->getMessage());
                Session::setErrorMessage("Errore durante il login");
            }
        }
        
        include 'views/auth/login.php';
    }

    public function logout() {
        try {
            if (Session::isLoggedIn()) {
                Session::clearLoginData();
                Session::destroy();
                
                session_start();
                Session::setSuccessMessage("Logout effettuato con successo");
            }
            
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            error_log("Errore durante il logout: " . $e->getMessage());
            Session::setErrorMessage("Errore durante il logout");
            header('Location: index.php');
            exit;
        }
    }

    public function register() {
        error_log("Register method called");

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = $_POST['email'];
                $password = $_POST['password'];
                $nome = $_POST['nome'] ?? '';
                $cognome = $_POST['cognome'] ?? '';

                error_log("Registration attempt for: $email");

                // Verifica se l'email esiste già
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM sys_utente WHERE email = ?");
                $stmt->execute([$email]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if($result['count'] > 0) {
                    throw new Exception("Email già registrata");
                }

                // Inserisci nuovo utente
                $stmt = $this->db->prepare("
                    INSERT INTO sys_utente (email, password, nome, cognome, tipo, attivo) 
                    VALUES (?, ?, ?, ?, 'registrato', 1)
                ");
                $stmt->execute([$email, $password, $nome, $cognome]);

                $_SESSION['success_message'] = "Registrazione completata con successo";
                header("Location: index.php?page=auth&action=login");
                exit();

            } catch(Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $_SESSION['error_message'] = $e->getMessage();
            }
        }

        include 'views/auth/register.php';
    }
}
?>