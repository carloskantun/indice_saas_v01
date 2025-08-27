<?php
/**
 * Paso para ejecutar seeders
 */
echo '<h2>Ejecutar seeders</h2>';
if (isset($_POST['seed'])) {
	echo '<pre>';
	passthru('php ' . escapeshellarg(__DIR__ . '/../../../database/seeds/root_admin_seed.php'));
	echo '</pre>';
	echo '<p style="color:green">Seeders ejecutados</p>';
} else {
	echo '<form method="post"><button name="seed" type="submit">Ejecutar seeders</button></form>';
}
