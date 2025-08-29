<?php
/**
 * Panel Root - Gestión de planes y empresas
 */
// Los helpers ya están incluidos en el index.php principal

// Verificar autenticación y rol root
auth();
checkRole(['root']);

// Obtener mensaje de éxito si existe
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Obtener acción
$action = $_GET['action'] ?? 'plans';

// Subnavegación del Panel Root
ob_start();
?>
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $action === 'plans' ? 'active' : '' ?>" href="?action=plans">
            <i class="bi bi-grid-3x3-gap"></i> <?= t('root.plans') ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $action === 'companies' ? 'active' : '' ?>" href="?action=companies">
            <i class="bi bi-building"></i> <?= t('root.companies') ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $action === 'metrics' ? 'active' : '' ?>" href="?action=metrics">
            <i class="bi bi-graph-up"></i> <?= t('root.metrics') ?>
        </a>
    </li>
</ul>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
// Cargar la vista correspondiente
$view = __DIR__ . "/views/{$action}.php";
if (file_exists($view)) {
    require $view;
} else {
    echo '<div class="alert alert-danger">Vista no encontrada</div>';
}

// Obtener el contenido generado
$content = ob_get_clean();

// Renderizar el layout principal
$title = t('root.title');
require __DIR__ . '/../layouts/main.php';
