<?php
/**
 * Runner de migraciones: ejecuta todos los archivos SQL en migrations/
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();
$dir = __DIR__ . '/migrations';
$files = glob($dir . '/*.sql');
foreach ($files as $file) {
	$sql = file_get_contents($file);
	$db->exec($sql);
	echo "Migración ejecutada: $file\n";
}
echo "Todas las migraciones han sido ejecutadas.\n";
