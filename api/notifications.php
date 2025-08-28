<?php
/**
 * API de notificaciones
 */
require_once __DIR__ . '/../src/core/helpers.php';

// Verificar autenticación
auth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener notificaciones del usuario
    $notifications = getNotifications($_SESSION['user_id']);
    echo json_encode($notifications);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Marcar notificaciones como leídas
    $db = getDB();
    $stmt = $db->prepare("
        UPDATE notifications 
        SET read_at = CURRENT_TIMESTAMP 
        WHERE user_id = ? AND read_at IS NULL
    ");
    $success = $stmt->execute([$_SESSION['user_id']]);
    
    echo json_encode(['success' => $success]);
    exit;
}

// Método no permitido
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
