<?php
/**
 * Helpers principales: autenticación, roles, permisos, conexión DB, emails, notificaciones
 */

// Asegurar que no hay salida antes de iniciar la sesión
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Envía un email usando PHPMailer
 */
function sendMail(string $to, string $subject, string $html): bool {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
        $mail->Port = env('MAIL_PORT', 587);
        $mail->CharSet = 'UTF-8';

        // Configurar remitente
        $mail->setFrom(env('MAIL_FROM_EMAIL'), env('MAIL_FROM_NAME'));
        
        // Agregar destinatario
        $mail->addAddress($to);
        
        // Configurar contenido
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        
        // Enviar email
        return $mail->send();
    } catch (Exception $e) {
        error_log("Error enviando email: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el idioma actual del usuario
 */
function getCurrentLanguage(): string {
    return $_SESSION['lang'] ?? 'es';
}

/**
 * Cambia el idioma del usuario
 */
function setLanguage(string $lang): bool {
    $available_languages = ['es', 'en']; // Agregar más idiomas aquí cuando estén disponibles
    if (in_array($lang, $available_languages)) {
        $_SESSION['lang'] = $lang;
        return true;
    }
    return false;
}

/**
 * Obtiene el texto traducido para una clave
 */
function t(string $key): string {
    $lang = getCurrentLanguage();
    static $cache = [];
    if (!isset($cache[$lang])) {
        $file = __DIR__ . "/../../lang/{$lang}.php";
        $cache[$lang] = file_exists($file) ? include $file : [];
    }
    return $cache[$lang][$key] ?? $key;
}

/**
 * Obtiene todos los idiomas disponibles
 */
function getAvailableLanguages(): array {
    return [
        'es' => 'Español',
        'en' => 'English'
    ];
}

function getDB() {
    static $db = null;
    if ($db === null) {
        $env_file = __DIR__ . '/../../.env';
        if (!file_exists($env_file)) {
            throw new Exception('El archivo .env no existe');
        }
        
        $env = parse_ini_file($env_file);
        if ($env === false) {
            throw new Exception('Error al leer el archivo .env');
        }

        // Verificar variables requeridas
        $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        foreach ($required as $var) {
            if (!isset($env[$var])) {
                throw new Exception("Variable de entorno {$var} no encontrada en .env");
            }
        }

        try {
            $db = new PDO(
                "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
                $env['DB_USER'],
                $env['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception('Error de conexión a la base de datos. Verifica las credenciales en el archivo .env');
        }
    }
    return $db;
}

function getCurrentUser() {
    if (empty($_SESSION['user_id'])) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function auth() {
    if (empty($_SESSION['user_id'])) {
        header('Location: /auth/login.php');
        exit;
    }
    return getCurrentUser();
}

function checkRole(array $roles) {
    if (!isset($_SESSION['current_role']) || !in_array($_SESSION['current_role'], $roles)) {
        http_response_code(403);
        exit('Access denied: insufficient role');
    }
}

function hasPermission(string $key): bool {
    if (!isset($_SESSION['current_role'])) return false;
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM role_permissions rp JOIN permissions p ON rp.permission_id = p.id WHERE rp.role = ? AND p.key = ? LIMIT 1");
    $stmt->execute([$_SESSION['current_role'], $key]);
    return (bool) $stmt->fetchColumn();
}

function resolveScope(): array {
    if (empty($_SESSION['user_id'])) return [null, null, null];
    
    $company_id = $_SESSION['current_company_id'] ?? null;
    $unit_id = $_SESSION['current_unit_id'] ?? null;
    $business_id = $_SESSION['current_business_id'] ?? null;
    
    return [$company_id, $unit_id, $business_id];
}

function checkPlanLimit(int $company_id, string $type): bool {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            CASE 
                WHEN ? = 'users' THEN p.users_max
                WHEN ? = 'units' THEN p.units_max
                WHEN ? = 'businesses' THEN p.businesses_max
            END as limit_value,
            (
                CASE 
                    WHEN ? = 'users' THEN (SELECT COUNT(*) FROM user_companies WHERE company_id = c.id)
                    WHEN ? = 'units' THEN (SELECT COUNT(*) FROM units WHERE company_id = c.id)
                    WHEN ? = 'businesses' THEN (SELECT COUNT(*) FROM businesses b JOIN units u ON b.unit_id = u.id WHERE u.company_id = c.id)
                END
            ) as current_count
        FROM companies c
        JOIN plans p ON c.plan_id = p.id
        WHERE c.id = ?
    ");
    
    $stmt->execute([$type, $type, $type, $type, $type, $type, $company_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result && $result['current_count'] < $result['limit_value'];
}

function planAllowsModule(int $company_id, string $module_slug): bool {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT modules_included 
        FROM companies c 
        JOIN plans p ON c.plan_id = p.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$company_id]);
    $modules = json_decode($stmt->fetchColumn() ?? '[]', true);
    
    return in_array('*', $modules) || in_array($module_slug, $modules);
}

function createNotification(int $user_id, int $company_id, string $message): bool {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, company_id, message)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$user_id, $company_id, $message]);
}

function getNotifications(int $user_id, int $limit = 20): array {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Determina la URL del panel según el rol del usuario
 */
function getUserPanel(string $role): string {
    // Determinar si estamos en producción por el dominio
    $isProduction = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'indiceapp.com') !== false);
    
    // Definir la ruta base según el entorno
    $baseRoute = $isProduction ? '?route=' : '/';
    
    switch ($role) {
        case 'root':
        case 'support':
            return $baseRoute . 'panel_root';
        case 'superadmin':
        case 'admin':
            return $baseRoute . 'admin';
        case 'moderator':
            return $baseRoute . 'moderator';
        case 'user':
            return $baseRoute . 'dashboard';
        default:
            return $baseRoute;
    }
}
