<?php
/**
 * Paso para ejecutar migraciones
 */
echo '<h2>Ejecutar migraciones</h2>';
if (isset($_POST['migrate'])) {
	echo '<pre>';
	passthru('php ' . escapeshellarg(__DIR__ . '/../../../database/migrate.php'));
	echo '</pre>';
	echo '<p style="color:green">Migraciones ejecutadas</p>';
} else {
	echo '<form method="post"><button name="migrate" type="submit">Ejecutar migraciones</button></form>';
}
