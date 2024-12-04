<?php
// File: models/User.php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT * FROM sys_utente WHERE email = ? AND password = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email, $password]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}