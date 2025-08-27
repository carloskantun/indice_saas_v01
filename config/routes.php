<?php
/**
 * Rutas básicas (micro-MVC)
 */

$routes = [
	'/' => 'public/index.php',
	'/install' => 'public/install/index.php',
	'/auth/login' => 'src/auth/index.php',
	'/admin' => 'src/admin/index.php',
	'/panel_root' => 'src/panel_root/index.php',
	// Agrega más rutas según módulos
];

function route($uri) {
	global $routes;
	$path = parse_url($uri, PHP_URL_PATH);
	if (isset($routes[$path])) {
		require $routes[$path];
		exit;
	}
	http_response_code(404);
	echo '404 Not Found';
}
