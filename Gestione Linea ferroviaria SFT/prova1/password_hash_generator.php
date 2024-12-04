<?php
// File: password_hash_generator.php
// Posizione: /xampp/htdocs/sft/password_hash_generator.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$password = "Password123!";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Generatore Hash Password</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash generato:</strong> " . htmlspecialchars($hash) . "</p>";

// Verifica che l'hash funzioni
$verify = password_verify($password, $hash);
echo "<p><strong>Verifica hash:</strong> " . ($verify ? 'Valido' : 'Non valido') . "</p>";

// Genera query SQL per l'aggiornamento
echo "<h3>Query SQL per aggiornare gli utenti:</h3>";
echo "<pre>";
echo "UPDATE sys_utente SET password = '" . $hash . "' WHERE email IN ('admin@sft.it', 'esercizio@sft.it', 'utente@esempio.it', 'cliente@esempio.it');\n";
echo "</pre>";

// Genera query SQL per l'inserimento
echo "<h3>Query SQL per inserire nuovi utenti:</h3>";
echo "<pre>";
echo "INSERT INTO sys_utente (email, password, nome, cognome, tipo) VALUES\n";
echo "('admin@sft.it', '" . $hash . "', 'Admin', 'SFT', 'admin'),\n";
echo "('esercizio@sft.it', '" . $hash . "', 'Capo', 'Esercizio', 'esercizio'),\n";
echo "('utente@esempio.it', '" . $hash . "', 'Mario', 'Rossi', 'registrato'),\n";
echo "('cliente@esempio.it', '" . $hash . "', 'Luigi', 'Verdi', 'registrato');\n";
echo "</pre>";
?>