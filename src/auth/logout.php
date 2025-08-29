<?php
/**
 * Logout de usuarios
 */
require_once __DIR__ . '/../core/helpers.php';

// Si hay sesión activa, crear notificación de logout
if (!empty($_SESSION['user_id']) && !empty($_SESSION['current_company_id'])) {
    createNotification(
        $_SESSION['user_id'], 
        $_SESSION['current_company_id'], 
        t('notifications.logout_success')
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: /auth/login');
exit;
