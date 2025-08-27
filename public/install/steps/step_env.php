<?php
/**
 * Paso para mostrar y editar .env
 */
$envPath = __DIR__ . '/../../../.env';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	file_put_contents($envPath, $_POST['env_content']);
	echo '<p style="color:green">.env actualizado</p>';
}
if (file_exists($envPath)) {
	$envContent = htmlspecialchars(file_get_contents($envPath));
	echo '<h2>Editar archivo .env</h2>';
	echo '<form method="post">';
	echo '<textarea name="env_content" rows="15" cols="80">' . $envContent . '</textarea><br>';
	echo '<button type="submit">Guardar cambios</button>';
	echo '</form>';
} else {
	echo '<p>No existe archivo .env</p>';
}
