<?php
/**
 * Página de aceptación de invitaciones
 */
require_once __DIR__ . '/../core/helpers.php';

// Obtener el token
$token = $_GET['token'] ?? '';
$error = '';
$invitation = null;
$success = false;

if ($token) {
    $db = getDB();
    
    // Verificar si la invitación existe y es válida
    $stmt = $db->prepare("
        SELECT i.*, c.name as company_name, u.name as unit_name, b.name as business_name 
        FROM invitations i
        JOIN companies c ON i.company_id = c.id
        LEFT JOIN units u ON i.unit_id = u.id
        LEFT JOIN businesses b ON i.business_id = b.id
        WHERE i.token = ? AND i.status = 'pending' AND i.expiration_date > NOW()
    ");
    $stmt->execute([$token]);
    $invitation = $stmt->fetch();

    if (!$invitation) {
        $error = t('invite.token_invalid');
    } else {
        // Procesar el formulario de registro
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (strlen($name) < 3) {
                $error = t('auth.name_too_short');
            } elseif (strlen($password) < 8) {
                $error = t('auth.password_too_short');
            } elseif ($password !== $password_confirm) {
                $error = t('auth.password_mismatch');
            } else {
                // Iniciar transacción
                $db->beginTransaction();
                try {
                    // Crear usuario
                    $stmt = $db->prepare("
                        INSERT INTO users (name, email, password, status)
                        VALUES (?, ?, ?, 'active')
                    ");
                    $stmt->execute([$name, $invitation['email'], password_hash($password, PASSWORD_DEFAULT)]);
                    $user_id = $db->lastInsertId();

                    // Crear relación usuario-compañía
                    $stmt = $db->prepare("
                        INSERT INTO user_companies (user_id, company_id, role)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$user_id, $invitation['company_id'], $invitation['role']]);

                    // Si hay unidad asignada, crear relación
                    if ($invitation['unit_id']) {
                        $stmt = $db->prepare("
                            INSERT INTO user_units (user_id, unit_id)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$user_id, $invitation['unit_id']]);
                    }

                    // Si hay negocio asignado, crear relación
                    if ($invitation['business_id']) {
                        $stmt = $db->prepare("
                            INSERT INTO user_businesses (user_id, business_id)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$user_id, $invitation['business_id']]);
                    }

                    // Marcar invitación como aceptada
                    $stmt = $db->prepare("
                        UPDATE invitations 
                        SET status = 'accepted', accepted_date = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$invitation['id']]);

                    // Crear notificación
                    $stmt = $db->prepare("
                        SELECT user_id FROM user_companies 
                        WHERE company_id = ? AND role IN ('admin', 'superadmin')
                    ");
                    $stmt->execute([$invitation['company_id']]);
                    $admins = $stmt->fetchAll();

                    foreach ($admins as $admin) {
                        createNotification($admin['user_id'], $invitation['company_id'], 
                            sprintf(t('invite.accepted_notification'), $invitation['email']));
                    }

                    // Confirmar transacción
                    $db->commit();
                    
                    // Iniciar sesión
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['current_company_id'] = $invitation['company_id'];
                    
                    $success = true;

                } catch (Exception $e) {
                    $db->rollBack();
                    $error = t('auth.registration_error');
                    error_log($e->getMessage());
                }
            }
        }
    }
} else {
    $error = t('invite.no_token');
}

// Iniciar buffer de salida
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="text-center mb-4"><?= t('invite.accept_title') ?></h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                        <?php if ($error === t('invite.token_invalid')): ?>
                            <p class="mb-0 mt-2">
                                <a href="/auth/login.php" class="alert-link">
                                    <?= t('auth.back_to_login') ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success">
                        <?= t('invite.registration_success') ?>
                        <meta http-equiv="refresh" content="3;url=/src/modules/index.php">
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-4">
                        <?= sprintf(
                            t('invite.welcome_message'),
                            htmlspecialchars($invitation['company_name']),
                            htmlspecialchars($invitation['role']),
                            $invitation['unit_name'] ? ' → ' . htmlspecialchars($invitation['unit_name']) : '',
                            $invitation['business_name'] ? ' → ' . htmlspecialchars($invitation['business_name']) : ''
                        ) ?>
                    </div>

                    <form method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label"><?= t('auth.name') ?></label>
                            <input type="text" class="form-control" name="name" required 
                                   minlength="3" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('auth.email') ?></label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($invitation['email']) ?>" 
                                   readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= t('auth.password') ?></label>
                            <input type="password" class="form-control" name="password" required minlength="8">
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?= t('auth.password_confirm') ?></label>
                            <input type="password" class="form-control" name="password_confirm" required minlength="8">
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">
                                <?= t('invite.complete_registration') ?>
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="/auth/login.php" class="text-muted">
                                <?= t('auth.back_to_login') ?>
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.querySelector('form.needs-validation')?.addEventListener('submit', function(event) {
    if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>

<?php
// Obtener el contenido generado
$content = ob_get_clean();

// Definir el título
$title = t('invite.accept_title');

// Renderizar el layout principal
require __DIR__ . '/../layouts/main.php';
