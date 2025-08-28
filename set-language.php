<?php
/**
 * Controlador para cambiar el idioma
 */
require_once __DIR__ . '/src/core/helpers.php';

$lang = $_GET['lang'] ?? 'es';
$redirect = $_GET['redirect'] ?? '/';

// Intentar cambiar el idioma
if (setLanguage($lang)) {
    // Éxito - redirigir a la página anterior
    header('Location: ' . $redirect);
    exit;
} else {
    // Error - idioma no válido
    http_response_code(400);
    echo 'Invalid language code';
    exit;
}
