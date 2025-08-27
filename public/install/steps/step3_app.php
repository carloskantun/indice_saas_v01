<?php
/**
 * Paso 3: Configuración de la aplicación
 */
echo '<h2>Configuración de la aplicación</h2>';
echo '<form method="post">';
echo 'APP_URL: <input name="app_url" value="http://localhost:8000"><br>';
echo 'APP_ENV: <select name="app_env"><option value="production">production</option><option value="development">development</option></select><br>';
echo 'APP_DEBUG: <select name="app_debug"><option value="false">false</option><option value="true">true</option></select><br>';
echo '<button type="submit">Guardar</button>';
echo '</form>';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	echo '<p style="color:green">Configuración guardada (simulado)</p>';
}
