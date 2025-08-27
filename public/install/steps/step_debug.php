<?php
/**
 * Paso de debug: muestra errores y estado actual de la instalación
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo '<h2>Debug & Errores</h2>';
if (file_exists(__DIR__ . '/../../../.env')) {
	echo '<p><strong>.env detectado:</strong></p>';
	echo '<pre>' . htmlspecialchars(file_get_contents(__DIR__ . '/../../../.env')) . '</pre>';
} else {
	echo '<p><strong>No se encontró .env</strong></p>';
}

echo '<p><strong>Variables de entorno:</strong></p>';
print_r($_ENV);

echo '<p><strong>Errores recientes:</strong></p>';
if (file_exists(__DIR__ . '/../../../error.log')) {
	echo '<pre>' . htmlspecialchars(file_get_contents(__DIR__ . '/../../../error.log')) . '</pre>';
} else {
	echo '<p>No hay error.log</p>';
}
