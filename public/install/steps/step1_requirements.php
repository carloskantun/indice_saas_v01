<?php

/**
 * Paso 1: Verificación de requisitos del sistema
 */

$requirements = [
    'PHP >= 8.0' => version_compare(PHP_VERSION, '8.0', '>='),
    'Extensión PDO' => extension_loaded('pdo'),
    'Extensión pdo_mysql' => extension_loaded('pdo_mysql'),
    'Extensión openssl' => extension_loaded('openssl'),
    'Extensión mbstring' => extension_loaded('mbstring'),
    'Extensión json' => extension_loaded('json'),
];
echo '<h2>Requisitos del sistema</h2>';
echo '<ul>';
foreach ($requirements as $label => $ok) {
        echo '<li>' . $label . ': ' . (
            $ok ? '<span style="color:green">✔</span>' : '<span style="color:red">✖</span>'
        ) . '</li>';
}
echo '</ul>';
echo '<p><a href="?step=2" class="btn btn-primary mt-3">Continuar</a></p>';
