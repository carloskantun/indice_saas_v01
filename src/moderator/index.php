<?php
/**
 * Panel de moderación
 */
require_once __DIR__ . '/../core/helpers.php';

// Verificar autenticación
$user = auth();

// Verificar rol
checkRole(['moderator', 'admin', 'superadmin']);

// Incluir el layout principal
$page_title = t('moderator.panel');
include __DIR__ . '/../layouts/main.php';
?>

<div class="container py-4">
    <h1 class="h3 mb-4"><?= t('moderator.panel') ?></h1>
    
    <div class="row g-4">
        <!-- Sección de moderación de contenido -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-shield-check me-2"></i>
                        <?= t('moderator.content') ?>
                    </h5>
                    <p class="card-text"><?= t('moderator.content_desc') ?></p>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <?= t('moderator.pending_reviews') ?>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <?= t('moderator.reported_content') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de estadísticas -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-graph-up me-2"></i>
                        <?= t('moderator.stats') ?>
                    </h5>
                    <p class="card-text"><?= t('moderator.stats_desc') ?></p>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <?= t('moderator.activity_log') ?>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <?= t('moderator.moderation_metrics') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
