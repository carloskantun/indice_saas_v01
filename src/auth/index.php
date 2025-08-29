<?php
/**
 * Login, registro y logout básicos
 */

// Prevenir acceso directo
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}

// Cargar helpers si no están cargados
if (!function_exists('t')) {
    require_once BASE_PATH . '/src/core/helpers.php';
}
// Los helpers ya están incluidos en el index.php principal

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $pass = $_POST['password'] ?? '';
    
    if (!$email || empty($pass)) {
        $error = t('login.invalid_data');
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, password_hash as password FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($pass, $user['password'])) {
            // Obtener rol y empresa del usuario
            $stmt = $db->prepare("
                SELECT uc.role, uc.company_id 
                FROM user_companies uc 
                WHERE uc.user_id = ? 
                ORDER BY uc.id DESC LIMIT 1
            ");
            $stmt->execute([$user['id']]);
            $userRole = $stmt->fetch(PDO::FETCH_ASSOC);

            // Guardar datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['current_role'] = $userRole['role'] ?? 'user';
            $_SESSION['current_company_id'] = $userRole['company_id'] ?? null;

            // Redirigir según el rol
            $redirectTo = getUserPanel($_SESSION['current_role']);
            header('Location: ' . $redirectTo);
            exit;
        } else {
            $error = t('login.error');
        }
    }
}
// Iniciar buffer de salida para el layout
ob_start();
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <!-- Logo y título -->
            <div class="text-center mb-4">
                <!-- TODO: Agrega el logo de la aplicación aquí -->
                <h1 class="h3 mb-3 fw-normal"><?= t('login.welcome') ?></h1>
                <p class="text-muted"><?= t('login.subtitle') ?></p>
            </div>

            <!-- Tarjeta de login -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="<?= t('login.email') ?>"
                                   required>
                            <label for="email"><?= t('login.email') ?></label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="<?= t('login.password') ?>"
                                   required>
                            <label for="password"><?= t('login.password') ?></label>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-primary btn-lg" type="submit">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                <?= t('login.submit') ?>
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="/auth/recover" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i>
                                <?= t('login.forgot_password') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    &copy; <?= date('Y') ?> Indice SaaS
                </small>
            </div>
        </div>
    </div>
</div>

<?php
// No necesitamos el layout aquí, el index.php principal se encargará de eso
