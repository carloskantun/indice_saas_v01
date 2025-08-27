
<?php
// Front-controller principal

// Cargar helpers y configuración
require_once __DIR__ . '/src/core/helpers.php';
require_once __DIR__ . '/config/routes.php';

// Ejemplo de router básico
$route = $_GET['route'] ?? 'home';
switch ($route) {
	case 'admin':
		require_once __DIR__ . '/src/admin/index.php';
		break;
	case 'auth':
		require_once __DIR__ . '/src/auth/index.php';
		break;
	case 'panel_root':
		require_once __DIR__ . '/src/panel_root/index.php';
		break;
	default:
		echo 'Bienvenido a la app Indice SaaS';
		break;
}
