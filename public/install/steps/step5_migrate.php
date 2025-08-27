<?php
/**
 * Paso 5: Ejecutar migraciones y seeds
 */
echo '<h2>Ejecutar migraciones y seeds</h2>';
if (isset($_POST['migrate'])) {
	echo '<pre>';
	passthru('php ' . escapeshellarg(__DIR__ . '/../../../database/migrate.php'));
	passthru('php ' . escapeshellarg(__DIR__ . '/../../../database/seeds/root_admin_seed.php'));
	echo '</pre>';
	echo '<p style="color:green">Migraciones y seeds ejecutados</p>';
} else {
	echo '<form method="post"><button name="migrate" type="submit">Ejecutar migraciones y seeds</button></form>';
}
