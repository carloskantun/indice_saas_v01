<?php
/**
 * Instalador web: router de pasos
 */
$steps = [
	'0' => 'step0_intro.php',
	'1' => 'step1_requirements.php',
	'2' => 'step2_database.php',
	'3' => 'step3_app.php',
	'4' => 'step4_mail.php',
	'5' => 'step5_migrate.php',
	'6' => 'step6_finish.php',
	'env' => 'step_env.php',
	'debug' => 'step_debug.php',
	'migrate' => 'step_migrate.php',
	'seed' => 'step_seed.php',
];
$step = $_GET['step'] ?? '0';
echo '<nav style="margin-bottom:20px">';
foreach ($steps as $k => $file) {
	echo '<a href="?step=' . $k . '" style="margin-right:10px">Paso ' . $k . '</a>';
}
echo '</nav>';
if (isset($steps[$step])) {
	require __DIR__ . '/steps/' . $steps[$step];
} else {
	echo '<p>Paso no encontrado.</p>';
}
