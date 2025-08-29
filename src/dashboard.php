<?php
/**
 * Grid de módulos - Landing principal post-login
 */
require_once __DIR__ . '/core/helpers.php';

// Verificar autenticación
$user = auth();

// Obtener lista de módulos disponibles según el plan y permisos
$db = getDB();
$stmt = $db->prepare("
    SELECT modules_included 
    FROM companies c 
    JOIN plans p ON c.plan_id = p.id 
    WHERE c.id = ?
");
$stmt->execute([$_SESSION['current_company_id']]);
$modules_included = json_decode($stmt->fetchColumn() ?? '[]', true);

// Lista de todos los módulos instalados
$available_modules = [
    'expenses' => [
        'name' => t('modules.expenses'),
        'description' => t('modules.expenses_desc'),
        'icon' => 'bi bi-wallet2',
        'enabled' => true
    ],
    'human-resources' => [
        'name' => t('modules.hr'),
        'description' => t('modules.hr_desc'),
        'icon' => 'bi bi-people',
        'enabled' => true
    ],
    // Módulos en desarrollo
    'analytics' => [
        'name' => t('modules.analytics'),
        'description' => t('modules.analytics_desc'),
        'icon' => 'bi bi-graph-up',
        'enabled' => false
    ],
    'crm' => [
        'name' => t('modules.crm'),
        'description' => t('modules.crm_desc'),
        'icon' => 'bi bi-person-lines-fill',
        'enabled' => false
    ]
];

// Filtrar módulos según el plan y permisos
$user_modules = [];
foreach ($available_modules as $slug => $module) {
    if (in_array('*', $modules_included) || in_array($slug, $modules_included)) {
        if (hasPermission($slug . '.view')) {
            $user_modules[$slug] = $module;
        }
    }
}

// Iniciar buffer de salida
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= t('dashboard.my_modules') ?></h1>
        <div class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" class="form-control" id="moduleSearch" 
                       placeholder="<?= t('dashboard.search_modules') ?>">
            </div>
            <?php if ($_SESSION['current_role'] === 'superadmin'): ?>
                <a href="/admin/modules" class="btn btn-primary">
                    <i class="bi bi-gear me-1"></i>
                    <?= t('dashboard.manage_modules') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4" id="modulesGrid">
        <?php foreach ($user_modules as $slug => $module): ?>
            <div class="col-12 col-md-6 col-lg-4 col-xl-3 module-card" 
                 data-module-name="<?= htmlspecialchars($module['name']) ?>"
                 data-module-description="<?= htmlspecialchars($module['description']) ?>">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="<?= $module['icon'] ?> fs-4 me-2"></i>
                            <h5 class="card-title mb-0"><?= htmlspecialchars($module['name']) ?></h5>
                        </div>
                        <p class="card-text text-muted">
                            <?= htmlspecialchars($module['description']) ?>
                        </p>
                        <div class="mt-auto">
                            <?php if ($module['enabled']): ?>
                                <a href="/modules/<?= $slug ?>/" class="btn btn-primary w-100">
                                    <?= t('common.access') ?>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <?= t('dashboard.coming_soon') ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Búsqueda de módulos
document.getElementById('moduleSearch').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.module-card').forEach(card => {
        const name = card.dataset.moduleName.toLowerCase();
        const description = card.dataset.moduleDescription.toLowerCase();
        const matches = name.includes(search) || description.includes(search);
        card.style.display = matches ? '' : 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
$title = t('dashboard.title');
require __DIR__ . '/layouts/main.php';
