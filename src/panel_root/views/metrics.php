<?php
/**
 * Vista de métricas generales
 */

$db = getDB();

// Obtener estadísticas generales
$stats = [
    'total_companies' => $db->query("SELECT COUNT(*) FROM companies")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_active_plans' => $db->query("SELECT COUNT(*) FROM plans WHERE is_active = 1")->fetchColumn(),
    'monthly_revenue' => $db->query("
        SELECT COALESCE(SUM(p.price_monthly), 0)
        FROM companies c
        JOIN plans p ON c.plan_id = p.id
    ")->fetchColumn()
];

// Obtener distribución de planes
$plan_distribution = $db->query("
    SELECT p.name, COUNT(*) as total
    FROM companies c
    JOIN plans p ON c.plan_id = p.id
    GROUP BY p.id, p.name
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener empresas más grandes (por usuarios)
$top_companies = $db->query("
    SELECT 
        c.name,
        p.name as plan_name,
        COUNT(uc.id) as total_users
    FROM companies c
    JOIN plans p ON c.plan_id = p.id
    JOIN user_companies uc ON c.id = uc.company_id
    GROUP BY c.id, c.name, p.name
    ORDER BY total_users DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row g-4">
    <!-- Estadísticas generales -->
    <div class="col-12">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Empresas</h6>
                        <h2 class="card-title mb-0"><?= number_format($stats['total_companies']) ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Usuarios</h6>
                        <h2 class="card-title mb-0"><?= number_format($stats['total_users']) ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Planes Activos</h6>
                        <h2 class="card-title mb-0"><?= number_format($stats['total_active_plans']) ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Ingresos Mensuales</h6>
                        <h2 class="card-title mb-0">$<?= number_format($stats['monthly_revenue'], 2) ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Distribución de planes -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución de Planes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th class="text-end">Empresas</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plan_distribution as $plan): ?>
                                <tr>
                                    <td><?= htmlspecialchars($plan['name']) ?></td>
                                    <td class="text-end"><?= number_format($plan['total']) ?></td>
                                    <td class="text-end">
                                        <?= number_format(($plan['total'] / $stats['total_companies']) * 100, 1) ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top empresas -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Empresas por Usuarios</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Plan</th>
                                <th class="text-end">Usuarios</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_companies as $company): ?>
                                <tr>
                                    <td><?= htmlspecialchars($company['name']) ?></td>
                                    <td><?= htmlspecialchars($company['plan_name']) ?></td>
                                    <td class="text-end"><?= number_format($company['total_users']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
