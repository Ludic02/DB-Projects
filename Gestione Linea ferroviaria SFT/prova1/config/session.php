<?php
class Session {
    private static $userBalance = null;

    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        error_log("Session initialized - ID: " . session_id());
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
        error_log("Session set - Key: $key, Value: " . print_r($value, true));
    }

    public static function get($key) {
        $value = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        error_log("Session get - Key: $key, Value: " . print_r($value, true));
        return $value;
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            error_log("Session key removed: $key");
            return true;
        }
        return false;
    }

    public static function destroy() {
        error_log("Destroying session - ID: " . session_id());
        
        // Backup del cookie name prima della distruzione
        $sessionName = session_name();
        
        // Distruggi tutte le variabili di sessione
        $_SESSION = array();
        
        // Distruggi il cookie di sessione se esiste
        if (isset($_COOKIE[$sessionName])) {
            setcookie($sessionName, '', time()-3600, '/');
        }
        
        // Distruggi la sessione
        session_destroy();
        
        // Reset del saldo utente
        self::$userBalance = null;
        
        error_log("Session destroyed");
    }

    public static function isLoggedIn() {
        $isLogged = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        error_log("Session isLoggedIn check: " . ($isLogged ? 'true' : 'false'));
        return $isLogged;
    }

    public static function getUserId() {
        return self::get('user_id');
    }

    public static function getUserType() {
        return self::get('user_type');
    }

    public static function getUserEmail() {
        return self::get('user_email');
    }

    public static function getUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => self::getUserId(),
            'type' => self::getUserType(),
            'email' => self::getUserEmail()
        ];
    }

    public static function getUserBalance() {
        if(self::$userBalance === null && self::isLoggedIn()) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT pu.saldo 
                    FROM pay_utenti pu
                    JOIN sys_utente su ON su.email = pu.email
                    WHERE su.id = ?
                ");
                $stmt->execute([self::getUserId()]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                self::$userBalance = $result ? floatval($result['saldo']) : 0.00;
                error_log("User balance loaded from DB: " . self::$userBalance);
            } catch (Exception $e) {
                error_log("Error loading user balance: " . $e->getMessage());
                self::$userBalance = 0.00;
            }
        }
        return self::$userBalance ?? 0.00;
    }

    public static function updateUserBalance($newBalance) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                UPDATE pay_utenti pu
                JOIN sys_utente su ON su.email = pu.email
                SET pu.saldo = ?
                WHERE su.id = ?
            ");
            $stmt->execute([$newBalance, self::getUserId()]);
            self::$userBalance = floatval($newBalance);
            error_log("User balance updated to: " . self::$userBalance);
            return true;
        } catch (Exception $e) {
            error_log("Error updating user balance: " . $e->getMessage());
            return false;
        }
    }

    public static function incrementUserBalance($amount) {
        $currentBalance = self::getUserBalance();
        $success = self::updateUserBalance($currentBalance + $amount);
        error_log("Balance increment attempted: Amount: $amount, Success: " . ($success ? 'true' : 'false'));
        return $success;
    }

    public static function decrementUserBalance($amount) {
        $currentBalance = self::getUserBalance();
        if ($currentBalance >= $amount) {
            $success = self::updateUserBalance($currentBalance - $amount);
            error_log("Balance decrement attempted: Amount: $amount, Success: " . ($success ? 'true' : 'false'));
            return $success;
        }
        error_log("Balance decrement failed: Insufficient funds");
        return false;
    }

    public static function setLoginData($userId, $userType, $userEmail) {
        error_log("Setting login data - ID: $userId, Type: $userType, Email: $userEmail");
        self::set('user_id', $userId);
        self::set('user_type', $userType);
        self::set('user_email', $userEmail);
        self::set('last_activity', time());
        self::$userBalance = null; // Reset balance to force reload
    }

    public static function clearLoginData() {
        error_log("Clearing login data");
        self::remove('user_id');
        self::remove('user_type');
        self::remove('user_email');
        self::remove('last_activity');
        self::$userBalance = null;
    }

    public static function setErrorMessage($message) {
        self::set('error_message', $message);
    }

    public static function getErrorMessage() {
        $message = self::get('error_message');
        self::remove('error_message');
        return $message;
    }

    public static function setSuccessMessage($message) {
        self::set('success_message', $message);
    }

    public static function getSuccessMessage() {
        $message = self::get('success_message');
        self::remove('success_message');
        return $message;
    }

    public static function hasMessage() {
        return isset($_SESSION['error_message']) || isset($_SESSION['success_message']);
    }

    public static function validateSession() {
        if (!self::isLoggedIn()) {
            return false;
        }

        if (!isset($_SESSION['last_activity'])) {
            self::destroy();
            return false;
        }

        // Timeout dopo 1 ora di inattività
        if (time() - $_SESSION['last_activity'] > 3600) {
            self::destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function debug() {
        error_log("-------- SESSION DEBUG --------");
        error_log("Session ID: " . session_id());
        error_log("Session Status: " . session_status());
        error_log("User ID: " . self::getUserId());
        error_log("User Type: " . self::getUserType());
        error_log("User Email: " . self::getUserEmail());
        error_log("User Balance: " . self::getUserBalance());
        error_log("Is Logged In: " . (self::isLoggedIn() ? 'Yes' : 'No'));
        error_log("Session Data: " . print_r($_SESSION, true));
        error_log("-----------------------------");
    }
}
?>