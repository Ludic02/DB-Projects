<?php

// Configurazione della sessione
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
ini_set('session.save_handler', 'files');
ini_set('session.save_path', sys_get_temp_dir());

// Verifica directory sessioni
$sessionPath = session_save_path();
if (!is_writable($sessionPath)) {
    error_log("ATTENZIONE: Directory sessioni non scrivibile: " . $sessionPath);
    @chmod($sessionPath, 0777);
}