<?php

/**
 * Rutas básicas (micro-MVC)
 */

$base = dirname(__DIR__);

$routes = [
    // Rutas principales
    '' => $base . '/src/dashboard.php',
    'dashboard' => $base . '/src/dashboard.php',
    'install' => $base . '/public/install/index.php',

    // Rutas de autenticación
    'auth' => $base . '/src/auth/index.php',
    'auth/login' => $base . '/src/auth/index.php',
    'auth/register' => $base . '/src/auth/register.php',
    'auth/forgot-password' => $base . '/src/auth/forgot_password.php',
    'auth/reset-password' => $base . '/src/auth/reset_password.php',
    'auth/accept-invitation' => $base . '/src/auth/accept_invitation.php',

    // Paneles principales
    'admin' => $base . '/src/admin/index.php',
    'panel_root' => $base . '/src/panel_root/index.php',
    'moderator' => $base . '/src/moderator/index.php',

    // Módulos
    'modules/expenses' => $base . '/src/modules/expenses/index.php',
];

function route($uri)
{
    global $routes;

    $path = trim(parse_url($uri, PHP_URL_PATH), '/');
    if (isset($_GET['route'])) {
        $path = trim($_GET['route'], '/');
    }

    if (array_key_exists($path, $routes)) {
        require $routes[$path];
        return;
    }

    http_response_code(404);
    echo '404 Not Found';
}
