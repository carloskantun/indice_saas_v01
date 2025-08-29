<?php
/**
 * Vista de gestión de planes
 */
require_once __DIR__ . '/../controllers/plans_controller.php';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            // Eliminar plan
            deletePlan($_POST['delete']);
            $_SESSION['success'] = t('root.plan_deleted');
        } else {
            // Crear o actualizar plan
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price_monthly' => $_POST['price_monthly'],
                'users_max' => $_POST['users_max'],
                'units_max' => $_POST['units_max'],
                'businesses_max' => $_POST['businesses_max'],
                'storage_max_mb' => $_POST['storage_max_mb'],
                'all_modules' => isset($_POST['all_modules']),
                'modules' => $_POST['modules'] ?? [],
                'is_active' => isset($_POST['is_active'])
            ];
            
            if (isset($_POST['id'])) {
                updatePlan($_POST['id'], $data);
                $_SESSION['success'] = t('root.plan_updated');
            } else {
                createPlan($data);
                $_SESSION['success'] = t('root.plan_created');
            }
        }
        header('Location: ?action=plans');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener plan para editar si se especifica
$plan_to_edit = null;
if (isset($_GET['edit'])) {
    $plan_to_edit = getPlan($_GET['edit']);
}

// Obtener lista de planes
$plans = listPlans();

// Obtener lista de módulos disponibles
$available_modules = [
    'expenses' => t('modules.expenses'),
    'human-resources' => t('modules.hr'),
    // Agregar más módulos aquí conforme se vayan creando
];

// Si es un POST, procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Eliminar plan
        $stmt = $db->prepare("DELETE FROM plans WHERE id = ?");
        if ($stmt->execute([$_POST['delete']])) {
            $_SESSION['success'] = t('root.plan_deleted');
            header('Location: ?action=plans');
            exit;
        }
    } else {
        // Crear o actualizar plan
        $modules = isset($_POST['modules']) ? json_encode($_POST['modules']) : '[]';
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price_monthly' => $_POST['price_monthly'],
            'users_max' => $_POST['users_max'],
            'units_max' => $_POST['units_max'],
            'businesses_max' => $_POST['businesses_max'],
            'storage_max_mb' => $_POST['storage_max_mb'],
            'modules_included' => $modules,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if (isset($_POST['id'])) {
            // Actualizar
            $sql = "UPDATE plans SET 
                    name = :name,
                    description = :description,
                    price_monthly = :price_monthly,
                    users_max = :users_max,
                    units_max = :units_max,
                    businesses_max = :businesses_max,
                    storage_max_mb = :storage_max_mb,
                    modules_included = :modules_included,
                    is_active = :is_active
                    WHERE id = :id";
            $data['id'] = $_POST['id'];
        } else {
            // Crear
            $sql = "INSERT INTO plans 
                    (name, description, price_monthly, users_max, units_max, businesses_max, 
                     storage_max_mb, modules_included, is_active)
                    VALUES 
                    (:name, :description, :price_monthly, :users_max, :units_max, :businesses_max,
                     :storage_max_mb, :modules_included, :is_active)";
        }
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute($data)) {
            $_SESSION['success'] = t('root.plan_saved');
            header('Location: ?action=plans');
            exit;
        }
    }
}

// Si hay ID en GET, cargar plan para editar
$plan_to_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $plan_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!-- Botón crear plan -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= t('root.plans') ?></h2>
    <?php if (!$plan_to_edit): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal">
            <i class="bi bi-plus-lg"></i> <?= t('root.create_plan') ?>
        </button>
    <?php endif; ?>
</div>

<!-- Lista de planes -->
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php foreach ($plans as $plan): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= htmlspecialchars($plan['name']) ?></h5>
                    <span class="badge bg-<?= $plan['is_active'] ? 'success' : 'danger' ?>">
                        <?= $plan['is_active'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="card-text"><?= htmlspecialchars($plan['description']) ?></p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-currency-dollar"></i> <?= number_format($plan['price_monthly'], 2) ?>/mes</li>
                        <li><i class="bi bi-people"></i> <?= $plan['users_max'] ?> usuarios</li>
                        <li><i class="bi bi-building"></i> <?= $plan['units_max'] ?> unidades</li>
                        <li><i class="bi bi-shop"></i> <?= $plan['businesses_max'] ?> negocios</li>
                        <li><i class="bi bi-hdd"></i> <?= $plan['storage_max_mb'] ?> MB</li>
                    </ul>
                    <div class="mt-3">
                        <strong>Módulos incluidos:</strong>
                        <?php 
                        $modules = json_decode($plan['modules_included'], true);
                        if (in_array('*', $modules)) {
                            echo '<span class="badge bg-success">Todos los módulos</span>';
                        } else {
                            foreach ($modules as $module) {
                                echo '<span class="badge bg-primary me-1">' . ($available_modules[$module] ?? $module) . '</span>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100">
                        <a href="?action=plans&edit=<?= $plan['id'] ?>" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> <?= t('root.edit_plan') ?>
                        </a>
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="deletePlan(<?= $plan['id'] ?>, '<?= htmlspecialchars($plan['name']) ?>')">
                            <i class="bi bi-trash"></i> <?= t('root.delete_plan') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal de plan -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <?php if ($plan_to_edit): ?>
                    <input type="hidden" name="id" value="<?= $plan_to_edit['id'] ?>">
                <?php endif; ?>
                
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?= $plan_to_edit ? t('root.edit_plan') : t('root.create_plan') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= t('root.plan_name') ?></label>
                            <input type="text" class="form-control" name="name" required
                                   value="<?= htmlspecialchars($plan_to_edit['name'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?= t('root.plan_price') ?></label>
                            <input type="number" class="form-control" name="price_monthly" step="0.01" required
                                   value="<?= $plan_to_edit['price_monthly'] ?? '0.00' ?>">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label"><?= t('root.plan_description') ?></label>
                            <textarea class="form-control" name="description" rows="2"><?= 
                                htmlspecialchars($plan_to_edit['description'] ?? '') 
                            ?></textarea>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label"><?= t('root.plan_users') ?></label>
                            <input type="number" class="form-control" name="users_max" required
                                   value="<?= $plan_to_edit['users_max'] ?? '3' ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label"><?= t('root.plan_units') ?></label>
                            <input type="number" class="form-control" name="units_max" required
                                   value="<?= $plan_to_edit['units_max'] ?? '1' ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label"><?= t('root.plan_businesses') ?></label>
                            <input type="number" class="form-control" name="businesses_max" required
                                   value="<?= $plan_to_edit['businesses_max'] ?? '1' ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label"><?= t('root.plan_storage') ?></label>
                            <input type="number" class="form-control" name="storage_max_mb" required
                                   value="<?= $plan_to_edit['storage_max_mb'] ?? '100' ?>">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label"><?= t('root.plan_modules') ?></label>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="all_modules" name="modules[]" 
                                       value="*" <?= $plan_to_edit && in_array('*', json_decode($plan_to_edit['modules_included'], true)) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="all_modules">Todos los módulos</label>
                            </div>
                            <div id="modulesList" class="row g-2" <?= $plan_to_edit && in_array('*', json_decode($plan_to_edit['modules_included'], true)) ? 'style="display:none"' : '' ?>>
                                <?php 
                                $included_modules = $plan_to_edit ? json_decode($plan_to_edit['modules_included'], true) : [];
                                foreach ($available_modules as $slug => $name): 
                                ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input module-check" 
                                                   name="modules[]" value="<?= $slug ?>" id="module_<?= $slug ?>"
                                                   <?= in_array($slug, $included_modules) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="module_<?= $slug ?>"><?= $name ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                       <?= ($plan_to_edit['is_active'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active"><?= t('root.plan_is_active') ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para el formulario -->
<script>
// Mostrar modal si hay plan para editar
<?php if ($plan_to_edit): ?>
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('planModal')).show();
    });
<?php endif; ?>

// Toggle de módulos
document.getElementById('all_modules').addEventListener('change', function() {
    const modulesList = document.getElementById('modulesList');
    modulesList.style.display = this.checked ? 'none' : '';
    document.querySelectorAll('.module-check').forEach(check => {
        check.disabled = this.checked;
    });
});

// Confirmar eliminación
function deletePlan(id, name) {
    if (confirm('<?= t('root.confirm_delete') ?>\n\n' + name)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="delete" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
