<?php
/**
 * Layout principal de la aplicación
 * 
 * @param string $title Título de la página
 * @param string $content Contenido principal
 */

// Asegurarse de que las variables existen
$title = $title ?? 'Indice SaaS';
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - Indice SaaS</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand {
            font-weight: 600;
            letter-spacing: -0.5px;
        }
        .btn-primary {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        .form-control {
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
        }
        .card {
            border: none;
            border-radius: 10px;
        }
        .card-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Indice SaaS</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/modules">
                                <i class="bi bi-grid-3x3-gap"></i> <?= t('modules.title') ?>
                            </a>
                        </li>
                        <?php if (in_array($_SESSION['current_role'] ?? '', ['superadmin', 'admin'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin">
                                    <i class="bi bi-gear"></i> <?= t('admin.panel') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (($_SESSION['current_role'] ?? '') === 'root'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/panel_root">
                                    <i class="bi bi-shield-lock"></i> <?= t('root.title') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <!-- Notificaciones -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="notifications-toggle">
                                <i class="bi bi-bell"></i>
                                <span class="badge bg-danger rounded-pill" id="notifications-count">0</span>
                            </a>
                        </li>
                        
                        <!-- Selector de idioma -->
                        <li class="nav-item">
                            <?php require __DIR__ . '/../partials/language_selector.php'; ?>
                        </li>
                        
                        <!-- Perfil/Logout -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?= htmlspecialchars(getCurrentUser()['name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/profile">
                                        <i class="bi bi-person"></i> <?= t('menu.profile') ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/settings">
                                        <i class="bi bi-gear"></i> <?= t('menu.settings') ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/auth/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> <?= t('menu.logout') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Selector de idioma para usuarios no autenticados -->
                <div class="ms-auto">
                    <?php require __DIR__ . '/../partials/language_selector.php'; ?>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="text-center mt-5 mb-3 text-muted">
        &copy; <?= date('Y') ?> Indice SaaS
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
    // Cargar contador de notificaciones
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            document.getElementById('notifications-count').textContent = data.length;
        });
    </script>
    <?php endif; ?>
</body>
</html>
