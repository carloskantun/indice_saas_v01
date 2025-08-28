<?php
/**
 * Front-controller principal
 * Maneja tanto rutas amigables (/auth/login) como query string (?route=auth)
 */

// Verificar si estamos en producción
$isProduction = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'indiceapp.com') !== false);

// Cargar configuración y helpers
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/helpers.php';
require_once __DIR__ . '/../config/routes.php';

// Iniciar sesión si no está iniciada
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rutear la solicitud
route($_SERVER['REQUEST_URI']);
