<?php
/**
 * Rutas básicas (micro-MVC)
 */

$routes = [
    // Rutas principales
    '/' => 'src/dashboard.php',
    'dashboard' => 'src/dashboard.php',
    '/dashboard' => 'src/dashboard.php',
    '/install' => 'public/install/index.php',
    
    // Rutas de autenticación
    'auth' => 'src/auth/index.php',
    '/auth/login' => 'src/auth/index.php',
    
    // Paneles principales
    'panel_root' => 'src/panel_root/index.php',
    '/panel_root' => 'src/panel_root/index.php',
    '/auth/register' => 'src/auth/register.php',
    '/auth/forgot-password' => 'src/auth/forgot_password.php',
    '/auth/reset-password' => 'src/auth/reset_password.php',
    '/auth/accept-invitation' => 'src/auth/accept_invitation.php',
    
    // Paneles principales
    'admin' => 'src/admin/index.php',
    '/admin' => 'src/admin/index.php',
    'panel_root' => 'src/panel_root/index.php',
    '/panel_root' => 'src/panel_root/index.php',
    'dashboard' => 'src/dashboard/index.php',
    '/dashboard' => 'src/dashboard/index.php',
    'moderator' => 'src/moderator/index.php',
    '/moderator' => 'src/moderator/index.php',
    
    // Módulos
    'expenses' => 'src/modules/expenses/index.php',
    '/modules/expenses' => 'src/modules/expenses/index.php',
];

function route($uri) {
    global $routes;
    
    // Obtener ruta del query string o del path
    if (isset($_GET['route'])) {
        $route = $_GET['route'];
    } else {
        $path = parse_url($uri, PHP_URL_PATH);
        $route = $path ?: '/';
    }
    
    // Limpiar la ruta
    $route = trim($route, '/');
    if (empty($route)) $route = '/';
    
    // Buscar la ruta con y sin slash inicial
    if (isset($routes[$route])) {
        require $routes[$route];
        exit;
    }
    
    if (isset($routes['/' . $route])) {
        require $routes['/' . $route];
        exit;
    }
    
    // Si no se encuentra la ruta
    http_response_code(404);
    echo '404 Not Found';
}
