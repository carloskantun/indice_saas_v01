<?php
/**
 * Grid de módulos - Landing post-login
 */
require_once __DIR__ . '/../core/helpers.php';

// Autenticar usuario y obtener scope
$user = auth();
[$company_id, $unit_id, $business_id] = resolveScope();

// Iniciar buffer de salida
ob_start();

// Obtener módulos disponibles según el plan
$db = getDB();
$stmt = $db->prepare("
    SELECT modules_included 
    FROM companies c 
    JOIN plans p ON c.plan_id = p.id 
    WHERE c.id = ?
");
$stmt->execute([$company_id]);
$modules_included = json_decode($stmt->fetchColumn() ?? '[]', true);

// Lista de todos los módulos del sistema
$available_modules = [
    'expenses' => [
        'name' => t('modules.expenses'),
        'description' => t('modules.expenses.description'),
        'icon' => 'bi bi-receipt',
        'permission' => 'expenses.view'
    ],
    'human-resources' => [
        'name' => t('modules.hr'),
        'description' => t('modules.hr.description'),
        'icon' => 'bi bi-people',
        'permission' => 'hr.view'
    ],
    // Agregar más módulos aquí
];

// Filtrar módulos según permisos y plan
$user_modules = [];
foreach ($available_modules as $slug => $module) {
    if (hasPermission($module['permission']) && 
        (in_array('*', $modules_included) || in_array($slug, $modules_included))) {
        $user_modules[$slug] = $module;
    }
}
?>

<div class="container">
    <!-- Menú hamburguesa -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title"><?= t('menu.title') ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <?php if ($company_id): ?>
                <div class="mb-4">
                    <h6><?= t('menu.my_scope') ?></h6>
                    <!-- Selector de empresa si tiene más de una -->
                    <select class="form-select mb-2" id="company-selector">
                        <?php
                        $stmt = $db->prepare("SELECT c.id, c.name FROM user_companies uc JOIN companies c ON uc.company_id = c.id WHERE uc.user_id = ?");
                        $stmt->execute([$user['id']]);
                        while ($company = $stmt->fetch()) {
                            $selected = $company['id'] == $company_id ? 'selected' : '';
                            echo "<option value='{$company['id']}' {$selected}>{$company['name']}</option>";
                        }
                        ?>
                    </select>
                    
                    <!-- Selector de unidad si tiene acceso -->
                    <?php if (hasPermission('units.view')): ?>
                        <select class="form-select mb-2" id="unit-selector">
                            <option value=""><?= t('menu.select_unit') ?></option>
                            <?php
                            $stmt = $db->prepare("SELECT id, name FROM units WHERE company_id = ?");
                            $stmt->execute([$company_id]);
                            while ($unit = $stmt->fetch()) {
                                $selected = $unit['id'] == $unit_id ? 'selected' : '';
                                echo "<option value='{$unit['id']}' {$selected}>{$unit['name']}</option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                    
                    <!-- Selector de negocio si tiene acceso -->
                    <?php if ($unit_id && hasPermission('businesses.view')): ?>
                        <select class="form-select mb-2" id="business-selector">
                            <option value=""><?= t('menu.select_business') ?></option>
                            <?php
                            $stmt = $db->prepare("SELECT id, name FROM businesses WHERE unit_id = ?");
                            $stmt->execute([$unit_id]);
                            while ($business = $stmt->fetch()) {
                                $selected = $business['id'] == $business_id ? 'selected' : '';
                                echo "<option value='{$business['id']}' {$selected}>{$business['name']}</option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filtro de visibilidad -->
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" id="show-my-scope" checked>
                <label class="form-check-label" for="show-my-scope">
                    <?= t('menu.show_my_scope') ?>
                </label>
            </div>
            
            <!-- Enlaces rápidos -->
            <hr>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="notifications-toggle">
                        <i class="bi bi-bell"></i> <?= t('menu.notifications') ?>
                        <span class="badge bg-danger rounded-pill" id="notifications-count">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/profile">
                        <i class="bi bi-person"></i> <?= t('menu.profile') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/settings">
                        <i class="bi bi-gear"></i> <?= t('menu.settings') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/auth/logout.php">
                        <i class="bi bi-box-arrow-right"></i> <?= t('menu.logout') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Grid de módulos -->
    <div class="row mb-4">
        <div class="col">
            <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="d-inline-block ms-2"><?= t('modules.title') ?></h1>
        </div>
        <div class="col-auto">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="module-search" placeholder="<?= t('modules.search') ?>">
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="modules-grid">
        <?php foreach ($user_modules as $slug => $module): ?>
            <div class="col module-card">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="<?= $module['icon'] ?> fs-4 me-2"></i>
                            <h5 class="card-title mb-0"><?= $module['name'] ?></h5>
                        </div>
                        <p class="card-text"><?= $module['description'] ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="/module/<?= $slug ?>" class="btn btn-primary">
                            <?= t('modules.enter') ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Búsqueda de módulos
document.getElementById('module-search').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.module-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(search) ? '' : 'none';
    });
});

// Actualizar alcance al cambiar selectors
['company', 'unit', 'business'].forEach(type => {
    const selector = document.getElementById(type + '-selector');
    if (selector) {
        selector.addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set(type + '_id', this.value);
            window.location.search = params.toString();
        });
    }
});
</script>
<?php
// Obtener el contenido generado
$content = ob_get_clean();

// Definir el título
$title = t('modules.title');

// Renderizar el layout principal
require __DIR__ . '/../layouts/main.php';
