<?php
// ajax_getOrario.php

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/session.php';


// Previeni qualsiasi output
ob_start();

// Imposta header JSON
header('Content-Type: application/json');

try {
    // Verifica autenticazione
    if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
        throw new Exception("Non autorizzato");
    }

    $orario_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$orario_id) {
        throw new Exception("ID orario non valido");
    }

    $db = Database::getInstance();

    $stmt = $db->prepare("
        SELECT 
            o.id,
            COALESCE(om.orario, TIME_FORMAT(o.orario, '%H:%i')) as orario,
            COALESCE(om.giorni, DATE_FORMAT(o.giorni, '%Y-%m-%d')) as giorni
        FROM sft_orario o
        LEFT JOIN sft_orario_modificato om ON o.id = om.orario_originale_id
        WHERE o.id = ?
    ");
    
    $stmt->execute([$orario_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        throw new Exception("Orario non trovato");
    }

    ob_clean();
    echo json_encode($result);

} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
exit;