<?php

/**
 * Paso 2: Configuración de la base de datos
 */

echo '<h2>Configuración de la base de datos</h2>';
echo '<form method="post">';
echo 'Host: <input name="db_host" value="localhost"><br>';
echo 'Nombre: <input name="db_name" value="indice_saas"><br>';
echo 'Usuario: <input name="db_user" value="root"><br>';
echo 'Contraseña: <input name="db_pass" type="password"><br>';
echo '<button type="submit">Probar conexión</button>';
echo '</form>';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/../Installer.php';
        $ok = Installer::testDb($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass']);
        echo $ok ? '<p style="color:green">Conexión exitosa</p>' : '<p style="color:red">Error de conexión</p>';
    if ($ok) {
            $envVars = [
                    'DB_HOST' => $_POST['db_host'],
                    'DB_NAME' => $_POST['db_name'],
                    'DB_USER' => $_POST['db_user'],
                    'DB_PASS' => $_POST['db_pass'],
            ];
            if (Installer::writeEnv($envVars)) {
                    echo '<p style="color:green">Archivo .env creado correctamente.</p>';
                    echo '<p><a href="?step=3" class="btn btn-primary">Siguiente</a></p>';
            } else {
                    echo '<p style="color:red">No se pudo crear el archivo .env.</p>';
            }
    }
}
