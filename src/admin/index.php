<?php
/**
 * Panel admin de empresa + invitaciones
 */
require_once __DIR__ . '/../core/helpers.php';
auth();
checkRole(['admin','superadmin']);

echo '<h2>' . t('admin.panel') . '</h2>';
echo '<div class="row justify-content-center mt-5">';
echo '<div class="col-md-8 col-lg-6">';
echo '<div class="card shadow">';
echo '<div class="card-body">';
echo '<h2 class="mb-4 text-center">' . t('admin.panel') . '</h2>';
echo '<p class="lead">' . t('admin.description') . '</p>';
// Aquí iría la lógica para enviar invitaciones y administrar la empresa
echo '<div class="alert alert-info">' . t('admin.placeholder') . '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
