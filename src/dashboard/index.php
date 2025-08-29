<?php
/**
 * Dashboard principal para usuarios regulares
 */
require_once __DIR__ . '/../core/helpers.php';

// Verificar autenticación
$user = auth();

// Verificar rol
checkRole(['user', 'moderator', 'admin', 'superadmin']);

// Lista de módulos disponibles para el usuario
$modules = [];

// Verificar módulos disponibles según el plan
if (planAllowsModule($_SESSION['current_company_id'], 'expenses')) {
    $modules[] = [
        'name' => 'Gastos',
        'description' => 'Gestión de gastos y presupuestos',
        'icon' => 'bi bi-wallet2',
        'url' => '/modules/expenses/'
    ];
}

// Puedes agregar más módulos aquí según el plan y permisos

// Incluir el layout principal
$page_title = 'Dashboard';
include __DIR__ . '/../layouts/main.php';
?>

<div class="container py-4">
    <h1 class="h3 mb-4"><?= t('dashboard.welcome') ?></h1>
    
    <div class="row g-4">
        <?php foreach ($modules as $module): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="<?= $module['icon'] ?> fs-4 me-2"></i>
                            <h5 class="card-title mb-0"><?= $module['name'] ?></h5>
                        </div>
                        <p class="card-text"><?= $module['description'] ?></p>
                        <a href="<?= $module['url'] ?>" class="btn btn-primary">
                            <?= t('common.access') ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
