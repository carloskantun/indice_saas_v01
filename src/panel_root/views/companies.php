<?php
/**
 * Vista de gestión de empresas
 */

// Obtener todas las empresas con su plan y estadísticas
$db = getDB();
$companies = $db->query("
    SELECT 
        c.*,
        p.name as plan_name,
        p.price_monthly,
        (SELECT COUNT(*) FROM user_companies WHERE company_id = c.id) as total_users,
        (SELECT COUNT(*) FROM units WHERE company_id = c.id) as total_units,
        (SELECT COUNT(*) FROM businesses b 
         JOIN units u ON b.unit_id = u.id 
         WHERE u.company_id = c.id) as total_businesses,
        u.name as created_by_name,
        u.email as created_by_email
    FROM companies c
    JOIN plans p ON c.plan_id = p.id
    JOIN users u ON c.created_by = u.id
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title h5 mb-0"><?= t('root.companies') ?></h2>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Empresa</th>
                        <th>Plan</th>
                        <th>Usuarios</th>
                        <th>Unidades</th>
                        <th>Negocios</th>
                        <th>Creado por</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($company['name']) ?></strong>
                                <br>
                                <small class="text-muted">ID: <?= $company['id'] ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($company['plan_name']) ?>
                                <br>
                                <small class="text-muted">
                                    $<?= number_format($company['price_monthly'], 2) ?>/mes
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= $company['total_users'] ?> usuarios
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $company['total_units'] ?> unidades
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $company['total_businesses'] ?> negocios
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($company['created_by_name']) ?>
                                <br>
                                <small class="text-muted"><?= $company['created_by_email'] ?></small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($company['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                            data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="viewDetails(<?= $company['id'] ?>)">
                                                <i class="bi bi-info-circle"></i> Ver detalles
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="changePlan(<?= $company['id'] ?>)">
                                                <i class="bi bi-arrow-repeat"></i> Cambiar plan
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" 
                                               onclick="suspendCompany(<?= $company['id'] ?>)">
                                                <i class="bi bi-pause-circle"></i> Suspender
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de detalles -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido se cargará dinámicamente -->
                <div class="text-center">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de cambio de plan -->
<div class="modal fade" id="changePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changePlanForm" method="post">
                    <input type="hidden" name="company_id" id="changePlanCompanyId">
                    <div class="mb-3">
                        <label class="form-label">Nuevo plan</label>
                        <select class="form-select" name="new_plan_id" required>
                            <?php
                            $plans = $db->query("SELECT id, name, price_monthly FROM plans WHERE is_active = 1 ORDER BY price_monthly ASC")
                                        ->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($plans as $plan) {
                                echo "<option value='{$plan['id']}'>{$plan['name']} (\${$plan['price_monthly']}/mes)</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Ver detalles de empresa
function viewDetails(companyId) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    modal.show();
    
    // Aquí iría la lógica para cargar los detalles vía AJAX
    // Por ahora es un placeholder
}

// Cambiar plan
function changePlan(companyId) {
    document.getElementById('changePlanCompanyId').value = companyId;
    const modal = new bootstrap.Modal(document.getElementById('changePlanModal'));
    modal.show();
}

// Suspender empresa
function suspendCompany(companyId) {
    if (confirm('¿Estás seguro de que deseas suspender esta empresa?')) {
        // Aquí iría la lógica para suspender la empresa
        // Por ahora es un placeholder
    }
}

// Manejar formulario de cambio de plan
document.getElementById('changePlanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Aquí iría la lógica para cambiar el plan
    // Por ahora es un placeholder
    alert('Funcionalidad en desarrollo');
});
</script>
