<?php
/**
 * Front-controller principal
 * Maneja tanto rutas amigables (/auth/login) como query string (?route=auth)
 */

// Asegurarse de que no haya salida antes de cargar helpers y sesión
if (file_exists(__DIR__ . '/../.env')) {
    require_once __DIR__ . '/../src/core/helpers.php';
} else {
    header('Location: /install/');
    exit;
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/routes.php';

// Iniciar sesión si no está iniciada
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rutear la solicitud
route($_SERVER['REQUEST_URI']);
