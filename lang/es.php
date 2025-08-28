<?php
return [
    // Login
    'login.title' => 'Iniciar sesión',
    'login.welcome' => 'Bienvenido a Indice SaaS',
    'login.subtitle' => 'Inicia sesión para continuar',
    'login.email' => 'Correo electrónico',
    'login.password' => 'Contraseña',
    'login.remember_me' => 'Recordarme',
    'login.submit' => 'Iniciar sesión',
    'login.error' => 'Credenciales inválidas',
    'login.forgot_password' => '¿Olvidaste tu contraseña?',
    'login.too_many_attempts' => 'Demasiados intentos fallidos. Por favor, espera 15 minutos.',
    'login.invalid_data' => 'Por favor, ingresa un email y contraseña válidos',

    // Auth común
    'auth.name' => 'Nombre completo',
    'auth.email' => 'Correo electrónico',
    'auth.password' => 'Contraseña',
    'auth.password_confirm' => 'Confirmar contraseña',
    'auth.back_to_login' => 'Volver al inicio de sesión',
    'auth.name_too_short' => 'El nombre debe tener al menos 3 caracteres',
    'auth.password_too_short' => 'La contraseña debe tener al menos 8 caracteres',
    'auth.password_mismatch' => 'Las contraseñas no coinciden',
    'auth.registration_error' => 'Error al registrar el usuario',

    // Invitaciones
    'invite.new_title' => 'Nueva invitación',
    'invite.email' => 'Correo electrónico',
    'invite.role' => 'Rol',
    'invite.select_role' => 'Seleccionar rol',
    'invite.unit' => 'Unidad',
    'invite.business' => 'Negocio',
    'invite.all_units' => 'Todas las unidades',
    'invite.all_businesses' => 'Todos los negocios',
    'invite.submit' => 'Enviar invitación',
    'invite.pending_title' => 'Invitaciones pendientes',
    'invite.no_pending' => 'No hay invitaciones pendientes',
    'invite.sent_success' => 'Invitación enviada correctamente',
    'invite.email_error' => 'Error al enviar el email de invitación',
    'invite.db_error' => 'Error al crear la invitación',
    'invite.plan_limit_reached' => 'Has alcanzado el límite de usuarios de tu plan',
    'invite.email_exists' => 'Este email ya está registrado',
    'invite.invalid_data' => 'Datos de invitación inválidos',
    'invite.resent_success' => 'Invitación reenviada correctamente',
    'invite.cancelled_success' => 'Invitación cancelada correctamente',
    'invite.confirm_cancel' => '¿Estás seguro de que deseas cancelar esta invitación?',
    'invite.table.email' => 'Email',
    'invite.table.role' => 'Rol',
    'invite.table.scope' => 'Alcance',
    'invite.table.sent' => 'Enviado',
    'invite.table.expires' => 'Expira',
    'invite.all_company' => 'Toda la empresa',
    'invite.notification' => 'Nueva invitación enviada a %s',
    'invite.accepted_notification' => '%s ha aceptado la invitación',

    // Email de invitación
    'invite.email.subject' => 'Invitación a Indice SaaS',
    'invite.email.title' => '¡Te han invitado a unirte!',
    'invite.email.greeting' => '¡Hola!',
    'invite.email.message' => 'Has sido invitado a unirte a nuestra plataforma. Por favor, haz clic en el botón de abajo para completar tu registro.',
    'invite.email.button' => 'Aceptar invitación',

    // Página de aceptación
    'invite.accept_title' => 'Aceptar invitación',
    'invite.no_token' => 'No se ha proporcionado un token de invitación',
    'invite.token_invalid' => 'La invitación no es válida o ha expirado',
    'invite.welcome_message' => 'Has sido invitado a unirte a %s como %s%s%s',
    'invite.complete_registration' => 'Completar registro',
    'invite.registration_success' => 'Registro completado correctamente. Redirigiendo...',

    // Roles
    'roles.admin' => 'Administrador',
    'roles.moderator' => 'Moderador',
    'roles.user' => 'Usuario',

    // Admin Panel
    'admin.panel' => 'Panel de Administración',
    'admin.description' => 'Gestiona usuarios, roles y permisos',
    'admin.placeholder' => 'Próximamente más funcionalidades',
    
    // Dashboard
    'dashboard.title' => 'Mi Dashboard',
    'dashboard.welcome' => 'Bienvenido a tu Dashboard',
    'dashboard.my_modules' => 'Mis Módulos',
    'dashboard.search_modules' => 'Buscar módulos...',
    'dashboard.manage_modules' => 'Gestionar Módulos',
    'dashboard.modules' => 'Módulos disponibles',
    'dashboard.coming_soon' => 'Próximamente',
    'common.access' => 'Acceder',

    // Módulos
    'modules.expenses' => 'Gastos',
    'modules.expenses_desc' => 'Gestión de gastos y presupuestos',
    'modules.hr' => 'Recursos Humanos',
    'modules.hr_desc' => 'Gestión de personal y pase de lista',
    'modules.analytics' => 'Analítica',
    'modules.analytics_desc' => 'Reportes y métricas de negocio',
    'modules.crm' => 'CRM',
    'modules.crm_desc' => 'Gestión de relaciones con clientes',

    // Moderator Panel
    'moderator.panel' => 'Panel de Moderación',
    'moderator.content' => 'Moderación de Contenido',
    'moderator.content_desc' => 'Revisa y modera el contenido de la plataforma',
    'moderator.stats' => 'Estadísticas',
    'moderator.stats_desc' => 'Visualiza métricas y estadísticas de moderación',
    'moderator.pending_reviews' => 'Revisiones Pendientes',
    'moderator.reported_content' => 'Contenido Reportado',
    'moderator.activity_log' => 'Registro de Actividad',
    'moderator.moderation_metrics' => 'Métricas de Moderación'
];
