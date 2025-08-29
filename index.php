<?php
// Prevenir cualquier salida antes de las redirecciones
ob_start();

// Front-controller principal
define('BASE_PATH', __DIR__);
require_once __DIR__ . '/src/core/helpers.php';
require_once __DIR__ . '/config/routes.php';

// Verificar si hay una sesión activa antes de cualquier salida
if (!isset($_SESSION['user_id']) && !in_array($_GET['route'] ?? '', ['auth', 'auth/login'])) {
    header('Location: ?route=auth');
    exit;
}

$route = $_GET['route'] ?? 'home';

function renderPage($title, $content) {
	echo '<!DOCTYPE html><html lang="es">';
	echo '<head>';
	echo '<meta charset="UTF-8">';
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
	echo '<title>' . htmlspecialchars($title) . '</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">';
        echo '<link href="/assets/css/main.css" rel="stylesheet">';
        echo '</head><body>';
	echo '<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4"><div class="container-fluid"><a class="navbar-brand" href="?route=home">Indice SaaS</a></div></nav>';
	echo '<main class="container">';
	echo $content;
	echo '</main>';
	echo '<footer class="text-center mt-5 mb-3 text-muted">&copy; ' . date('Y') . ' Indice SaaS</footer>';
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>';
    echo '<script src="/assets/js/main.js"></script>';
    echo '</body></html>';
}

// Procesar la ruta actual
switch ($route) {
    case 'admin':
        require __DIR__ . '/src/admin/index.php';
        $title = t('admin.panel');
        break;
    case 'auth':
    case 'auth/login':
        require __DIR__ . '/src/auth/index.php';
        $title = t('login.title');
        break;
    case 'panel_root':
        require __DIR__ . '/src/panel_root/index.php';
        $title = 'Panel Root';
        break;
    default:
        if (isset($_SESSION['user_id'])) {
            header('Location: ?route=' . getUserPanel($_SESSION['current_role']));
            exit;
        } else {
            header('Location: ?route=auth');
            exit;
        }
}// Inicio del buffer para capturar la salida
ob_start();

switch ($route) {
    case 'admin':
        require __DIR__ . '/src/admin/index.php';
        $title = t('admin.panel');
        break;
    case 'auth':
    case 'auth/login':
        require __DIR__ . '/src/auth/index.php';
        $title = t('login.title');
        break;
    case 'panel_root':
        require __DIR__ . '/src/panel_root/index.php';
        $title = 'Panel Root';
        break;
	case 'home':
		renderPage('Inicio', '<div class="d-flex flex-column align-items-center justify-content-center" style="min-height:60vh;">'
			.'<div class="card shadow-lg p-4 mb-4" style="max-width:500px; width:100%;">'
			.'<h1 class="display-5 text-center mb-3">Bienvenido a Indice SaaS</h1>'
			.'<p class="lead text-center mb-4">Gestiona empresas, roles y módulos en una plataforma SaaS moderna y escalable.</p>'
			.'<div class="d-grid gap-2">'
                        .'<a href="?route=auth" class="btn btn-primary btn-lg">' . t('login.submit') . '</a>'
			.'<a href="?route=admin" class="btn btn-outline-secondary">Ver demo admin</a>'
			.'<a href="?route=panel_root" class="btn btn-outline-secondary">Ver demo root</a>'
			.'</div>'
			.'</div>'
			.'<div class="text-center text-muted mt-4">&copy; '.date('Y').' Indice SaaS</div>'
		.'</div>');
		break;
	default:
		http_response_code(404);
		renderPage('404', '<div class="mt-5"><h2>Página no encontrada</h2><p>La ruta solicitada no existe.</p></div>');
		break;
}
