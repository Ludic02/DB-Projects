<?php
// File: config/database.php

class Database {
    private static $instance = null;
    private $conn;
    
    // Credenziali database
    private $host = "localhost";
    private $db_name = "lu_dicampli";
    private $username = "lu.dicampli";
    private $password = "jnBpp2f9";

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $e) {
            error_log("Errore di connessione al database: " . $e->getMessage());
            die("Errore di connessione al database. Per favore, controlla il log per i dettagli.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }

    // Metodo per testare la connessione
    public static function testConnection() {
        try {
            $db = self::getInstance();
            $db->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            error_log("Test connessione fallito: " . $e->getMessage());
            return false;
        }
    }

    // Previene la clonazione dell'oggetto
    private function __clone() {}

    // Previene la deserializzazione dell'oggetto - ora pubblico
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    // Metodo per chiudere la connessione
    public static function closeConnection() {
        self::$instance = null;
    }
}