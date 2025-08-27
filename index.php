

<?php
// Front-controller principal
require_once __DIR__ . '/src/core/helpers.php';
require_once __DIR__ . '/config/routes.php';

function renderPage($title, $content) {
	echo '<!DOCTYPE html><html lang="es">';
	echo '<head>';
	echo '<meta charset="UTF-8">';
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
	echo '<title>' . htmlspecialchars($title) . '</title>';
	echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
	echo '</head><body>';
	echo '<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4"><div class="container-fluid"><a class="navbar-brand" href="?route=home">Indice SaaS</a></div></nav>';
	echo '<main class="container">';
	echo $content;
	echo '</main>';
	echo '<footer class="text-center mt-5 mb-3 text-muted">&copy; ' . date('Y') . ' Indice SaaS</footer>';
	echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>';
	echo '</body></html>';
}

$route = $_GET['route'] ?? 'home';
switch ($route) {
	case 'admin':
		ob_start();
		require __DIR__ . '/src/admin/index.php';
		$content = ob_get_clean();
		renderPage('Panel Admin', $content);
		break;
	case 'auth':
		ob_start();
		require __DIR__ . '/src/auth/index.php';
		$content = ob_get_clean();
		renderPage('Login', $content);
		break;
	case 'panel_root':
		ob_start();
		require __DIR__ . '/src/panel_root/index.php';
		$content = ob_get_clean();
		renderPage('Panel Root', $content);
		break;
	case 'home':
		renderPage('Inicio', '<div class="d-flex flex-column align-items-center justify-content-center" style="min-height:60vh;">'
			.'<div class="card shadow-lg p-4 mb-4" style="max-width:500px; width:100%;">'
			.'<h1 class="display-5 text-center mb-3">Bienvenido a Indice SaaS</h1>'
			.'<p class="lead text-center mb-4">Gestiona empresas, roles y módulos en una plataforma SaaS moderna y escalable.</p>'
			.'<div class="d-grid gap-2">'
			.'<a href="?route=auth" class="btn btn-primary btn-lg">Ingresar</a>'
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
