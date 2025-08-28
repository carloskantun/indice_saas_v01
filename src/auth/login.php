<?php
/**
 * Login de usuarios
 */
require_once __DIR__ . '/../core/helpers.php';

// Si ya está autenticado, redirigir al panel correspondiente
if (!empty($_SESSION['user_id']) && !empty($_SESSION['current_role'])) {
    $baseUrl = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'indiceapp.com') !== false) 
        ? 'https://app.indiceapp.com/?route='
        : '/';
    
    header('Location: ' . $baseUrl . ltrim(getUserPanel($_SESSION['current_role']), '/'));
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if ($email && $password) {
        $db = getDB();
        
        // Prevenir ataques de fuerza bruta
        $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 5) {
            $error = t('login.too_many_attempts');
        } else {
            // Registrar intento de login
            $stmt = $db->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
            $stmt->execute([$email, $_SERVER['REMOTE_ADDR']]);
            
            // Verificar credenciales
            $stmt = $db->prepare("
                SELECT u.*, uc.company_id, uc.role, uc.last_accessed
                FROM users u
                LEFT JOIN user_companies uc ON u.id = uc.user_id
                WHERE u.email = ? AND u.status = 'active'
                ORDER BY uc.last_accessed DESC
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Limpiar intentos fallidos
                $stmt = $db->prepare("DELETE FROM login_attempts WHERE email = ?");
                $stmt->execute([$email]);
                
                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['current_company_id'] = $user['company_id'];
                $_SESSION['current_role'] = $user['role'];
                
                // Actualizar último acceso
                $stmt = $db->prepare("
                    UPDATE user_companies 
                    SET last_accessed = NOW() 
                    WHERE user_id = ? AND company_id = ?
                ");
                $stmt->execute([$user['id'], $user['company_id']]);
                
                // Si seleccionó "recordarme", crear token
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $stmt = $db->prepare("
                        INSERT INTO remember_tokens (user_id, token, expires_at)
                        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
                    ");
                    $stmt->execute([$user['id'], $token]);
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', true, true);
                }

                // Determinar la URL base según el entorno
                $baseUrl = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'indiceapp.com') !== false) 
                    ? 'https://app.indiceapp.com/?route='
                    : '/';
                
                // Redirigir al panel correspondiente
                $panel = getUserPanel($user['role']);
                header('Location: ' . $baseUrl . ltrim($panel, '/'));
                exit;
            } else {
                $error = t('login.error');
            }
        }
    } else {
        $error = t('login.invalid_data');
    }
}

// Iniciar buffer de salida
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h1 class="h3"><?= t('login.welcome') ?></h1>
                    <p class="text-muted"><?= t('login.subtitle') ?></p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label"><?= t('login.email') ?></label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               value="<?= htmlspecialchars($email) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><?= t('login.password') ?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember"><?= t('login.remember_me') ?></label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <?= t('login.submit') ?>
                    </button>

                    <div class="text-center">
                        <a href="/auth/forgot-password" class="text-decoration-none">
                            <?= t('login.forgot_password') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.querySelector('form').addEventListener('submit', function(event) {
    if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>

<?php
$content = ob_get_clean();
$title = t('login.title');
require __DIR__ . '/../layouts/main.php';
