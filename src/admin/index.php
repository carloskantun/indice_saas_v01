<?php
/**
 * Panel de administración — Gestión de empresa
 */
require_once __DIR__ . '/../core/helpers.php';

// Verificar autenticación y rol de admin
auth();
checkRole(['admin', 'superadmin']);

// Obtener datos necesarios
$db = getDB();
$user = getCurrentUser();
$company_id = $_SESSION['current_company_id'];

// Procesar el formulario de invitación
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'invite':
                $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
                $role = $_POST['role'];
                $unit_id = !empty($_POST['unit_id']) ? $_POST['unit_id'] : null;
                $business_id = !empty($_POST['business_id']) ? $_POST['business_id'] : null;

                if ($email && in_array($role, ['admin', 'moderator', 'user'])) {
                    // Verificar si el email ya está registrado
                    $stmt = $db->prepare("SELECT 1 FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if (!$stmt->fetchColumn()) {
                        // Verificar límite del plan
                        if (checkPlanLimit($company_id, 'users')) {
                            // Generar token único
                            $token = bin2hex(random_bytes(32));
                            
                            // Crear invitación
                            $stmt = $db->prepare("
                                INSERT INTO invitations 
                                (email, company_id, unit_id, business_id, role, token, expiration_date)
                                VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
                            ");
                            
                            if ($stmt->execute([$email, $company_id, $unit_id, $business_id, $role, $token])) {
                                // Enviar email
                                $invite_url = "http://{$_SERVER['HTTP_HOST']}/auth/accept_invitation.php?token=" . $token;
                                $html = "<h2>" . t('invite.email.title') . "</h2>";
                                $html .= "<p>" . t('invite.email.greeting') . "</p>";
                                $html .= "<p>" . t('invite.email.message') . "</p>";
                                $html .= "<p><a href='$invite_url'>" . t('invite.email.button') . "</a></p>";
                                
                                if (sendMail($email, t('invite.email.subject'), $html)) {
                                    $success = t('invite.sent_success');
                                    createNotification($user['id'], $company_id, 
                                        sprintf(t('invite.notification'), $email));
                                } else {
                                    $error = t('invite.email_error');
                                }
                            } else {
                                $error = t('invite.db_error');
                            }
                        } else {
                            $error = t('invite.plan_limit_reached');
                        }
                    } else {
                        $error = t('invite.email_exists');
                    }
                } else {
                    $error = t('invite.invalid_data');
                }
                break;

            case 'resend':
                $invitation_id = $_POST['invitation_id'];
                $stmt = $db->prepare("
                    UPDATE invitations 
                    SET sent_date = NOW(), expiration_date = DATE_ADD(NOW(), INTERVAL 7 DAY)
                    WHERE id = ? AND company_id = ? AND status = 'pending'
                ");
                if ($stmt->execute([$invitation_id, $company_id])) {
                    // Reenviar email
                    $stmt = $db->prepare("SELECT email, token FROM invitations WHERE id = ?");
                    $stmt->execute([$invitation_id]);
                    $inv = $stmt->fetch();
                    
                    $invite_url = "http://{$_SERVER['HTTP_HOST']}/auth/accept_invitation.php?token=" . $inv['token'];
                    $html = "<h2>" . t('invite.email.title') . "</h2>";
                    $html .= "<p>" . t('invite.email.greeting') . "</p>";
                    $html .= "<p>" . t('invite.email.message') . "</p>";
                    $html .= "<p><a href='$invite_url'>" . t('invite.email.button') . "</a></p>";
                    
                    if (sendMail($inv['email'], t('invite.email.subject'), $html)) {
                        $success = t('invite.resent_success');
                    } else {
                        $error = t('invite.email_error');
                    }
                }
                break;

            case 'cancel':
                $invitation_id = $_POST['invitation_id'];
                $stmt = $db->prepare("
                    DELETE FROM invitations 
                    WHERE id = ? AND company_id = ? AND status = 'pending'
                ");
                if ($stmt->execute([$invitation_id, $company_id])) {
                    $success = t('invite.cancelled_success');
                }
                break;
        }
    }
}

// Obtener invitaciones pendientes
$stmt = $db->prepare("
    SELECT i.*, u.name as unit_name, b.name as business_name
    FROM invitations i
    LEFT JOIN units u ON i.unit_id = u.id
    LEFT JOIN businesses b ON i.business_id = b.id
    WHERE i.company_id = ? AND i.status = 'pending'
    ORDER BY i.sent_date DESC
");
$stmt->execute([$company_id]);
$pending_invitations = $stmt->fetchAll();

// Obtener unidades y negocios para el selector
$stmt = $db->prepare("SELECT id, name FROM units WHERE company_id = ?");
$stmt->execute([$company_id]);
$units = $stmt->fetchAll();

$businesses = [];
if (!empty($units)) {
    $unit_ids = array_column($units, 'id');
    $stmt = $db->prepare("SELECT id, name, unit_id FROM businesses WHERE unit_id IN (" . implode(',', $unit_ids) . ")");
    $stmt->execute();
    $businesses = $stmt->fetchAll();
}

// Iniciar buffer de salida
ob_start();
?>

<div class="row">
    <!-- Formulario de invitación -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= t('invite.new_title') ?></h5>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="invite">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= t('invite.email') ?></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t('invite.role') ?></label>
                        <select class="form-select" name="role" required>
                            <option value=""><?= t('invite.select_role') ?></option>
                            <option value="admin"><?= t('roles.admin') ?></option>
                            <option value="moderator"><?= t('roles.moderator') ?></option>
                            <option value="user"><?= t('roles.user') ?></option>
                        </select>
                    </div>

                    <?php if (!empty($units)): ?>
                    <div class="mb-3">
                        <label class="form-label"><?= t('invite.unit') ?></label>
                        <select class="form-select" name="unit_id" id="unit-selector">
                            <option value=""><?= t('invite.all_units') ?></option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($businesses)): ?>
                    <div class="mb-3">
                        <label class="form-label"><?= t('invite.business') ?></label>
                        <select class="form-select" name="business_id" id="business-selector" disabled>
                            <option value=""><?= t('invite.all_businesses') ?></option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <?= t('invite.submit') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de invitaciones pendientes -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= t('invite.pending_title') ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_invitations)): ?>
                    <p class="text-muted text-center mb-0"><?= t('invite.no_pending') ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th><?= t('invite.table.email') ?></th>
                                    <th><?= t('invite.table.role') ?></th>
                                    <th><?= t('invite.table.scope') ?></th>
                                    <th><?= t('invite.table.sent') ?></th>
                                    <th><?= t('invite.table.expires') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_invitations as $inv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($inv['email']) ?></td>
                                        <td><span class="badge bg-secondary"><?= $inv['role'] ?></span></td>
                                        <td>
                                            <?php
                                            $scope = [];
                                            if ($inv['unit_name']) $scope[] = $inv['unit_name'];
                                            if ($inv['business_name']) $scope[] = $inv['business_name'];
                                            echo $scope ? htmlspecialchars(implode(' → ', $scope)) : t('invite.all_company');
                                            ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($inv['sent_date'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($inv['expiration_date'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="resend">
                                                    <input type="hidden" name="invitation_id" value="<?= $inv['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-primary">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </form>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="invitation_id" value="<?= $inv['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger" 
                                                            onclick="return confirm('<?= t('invite.confirm_cancel') ?>')">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Manejo de unidades y negocios
const unitSelector = document.getElementById('unit-selector');
const businessSelector = document.getElementById('business-selector');
const businesses = <?= json_encode($businesses) ?>;

if (unitSelector && businessSelector) {
    unitSelector.addEventListener('change', function() {
        const unitId = this.value;
        businessSelector.innerHTML = `<option value="">${<?= json_encode(t('invite.all_businesses')) ?>}</option>`;
        businessSelector.disabled = !unitId;
        
        if (unitId) {
            const unitBusinesses = businesses.filter(b => b.unit_id == unitId);
            unitBusinesses.forEach(business => {
                const option = document.createElement('option');
                option.value = business.id;
                option.textContent = business.name;
                businessSelector.appendChild(option);
            });
        }
    });
}

// Validación del formulario
document.querySelector('form.needs-validation').addEventListener('submit', function(event) {
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
$title = t('admin.panel');

// Renderizar el layout principal
require __DIR__ . '/../layouts/main.php';
