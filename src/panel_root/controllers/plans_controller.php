<?php
/**
 * Controlador de Planes - Panel Root
 */

function listPlans() {
    $db = getDB();
    return $db->query("
        SELECT p.*, 
               COUNT(DISTINCT c.id) as companies_count,
               COUNT(DISTINCT uc.user_id) as total_users
        FROM plans p
        LEFT JOIN companies c ON c.plan_id = p.id
        LEFT JOIN user_companies uc ON uc.company_id = c.id
        GROUP BY p.id
        ORDER BY p.price_monthly ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

function getPlan($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createPlan($data) {
    $db = getDB();
    
    // Validar datos requeridos
    $required = ['name', 'price_monthly', 'users_max', 'units_max', 'businesses_max', 'storage_max_mb'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception(t('validation.required_field', ['field' => $field]));
        }
    }
    
    // Preparar módulos incluidos
    $modules_included = isset($data['all_modules']) ? '["*"]' : 
        json_encode($data['modules'] ?? []);
    
    $stmt = $db->prepare("
        INSERT INTO plans (
            name, description, price_monthly, users_max, 
            units_max, businesses_max, storage_max_mb, 
            modules_included, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['description'] ?? '',
        $data['price_monthly'],
        $data['users_max'],
        $data['units_max'],
        $data['businesses_max'],
        $data['storage_max_mb'],
        $modules_included,
        $data['is_active'] ?? 1
    ]);
}

function updatePlan($id, $data) {
    $db = getDB();
    
    // Validar que el plan existe
    $plan = getPlan($id);
    if (!$plan) {
        throw new Exception(t('root.plan_not_found'));
    }
    
    // Preparar módulos incluidos
    $modules_included = isset($data['all_modules']) ? '["*"]' : 
        json_encode($data['modules'] ?? []);
    
    $stmt = $db->prepare("
        UPDATE plans SET 
            name = ?,
            description = ?,
            price_monthly = ?,
            users_max = ?,
            units_max = ?,
            businesses_max = ?,
            storage_max_mb = ?,
            modules_included = ?,
            is_active = ?
        WHERE id = ?
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['description'] ?? '',
        $data['price_monthly'],
        $data['users_max'],
        $data['units_max'],
        $data['businesses_max'],
        $data['storage_max_mb'],
        $modules_included,
        $data['is_active'] ?? 1,
        $id
    ]);
}

function deletePlan($id) {
    $db = getDB();
    
    // Verificar si hay empresas usando este plan
    $stmt = $db->prepare("SELECT COUNT(*) FROM companies WHERE plan_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception(t('root.plan_in_use'));
    }
    
    // Eliminar el plan
    $stmt = $db->prepare("DELETE FROM plans WHERE id = ?");
    return $stmt->execute([$id]);
}
