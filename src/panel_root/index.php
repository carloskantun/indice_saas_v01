<?php
/**
 * Panel root: gestión de planes SaaS
 */
require_once __DIR__ . '/../core/helpers.php';
auth();
checkRole(['root']);

echo '<h2>Panel Root — Gestión de Planes SaaS</h2>';
echo '<div class="row justify-content-center mt-5">';
echo '<div class="col-md-8 col-lg-6">';
echo '<div class="card shadow">';
echo '<div class="card-body">';
echo '<h2 class="mb-4 text-center">Panel Root — Gestión de Planes SaaS</h2>';
echo '<p class="lead">Aquí puedes crear, editar y administrar los planes SaaS disponibles para las empresas.</p>';
// Aquí iría la lógica para crear/editar planes
echo '<div class="alert alert-info">Funcionalidad de gestión de planes próximamente...</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
