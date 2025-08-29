<?php
/**
 * Módulo Expenses — ejemplo
 */
// Los helpers ya están incluidos en el index.php principal
auth();
checkRole(['admin','superadmin','root']);
if (!hasPermission('expenses.view')) {
	http_response_code(403);
	exit('Access denied');
}
echo '<div class="row justify-content-center mt-5">';
echo '<div class="col-md-8 col-lg-6">';
echo '<div class="card shadow">';
echo '<div class="card-body">';
echo '<h2 class="mb-4 text-center">Módulo de Gastos</h2>';
echo '<p class="lead">Aquí puedes gestionar los gastos de la empresa.</p>';
// Aquí iría la lógica del módulo de gastos
echo '<div class="alert alert-info">Funcionalidad de gastos próximamente...</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
