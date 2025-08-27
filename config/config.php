<?php
/**
 * Configuración base: carga .env y helpers globales
 */

// Cargar variables de entorno
function env($key, $default = null) {
	static $vars = null;
	if ($vars === null) {
		$vars = parse_ini_file(__DIR__ . '/../.env');
	}
	return $vars[$key] ?? $default;
}

// Cargar helpers
require_once __DIR__ . '/../src/core/helpers.php';
