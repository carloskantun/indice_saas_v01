<?php
/**
 * Paso 4: Configuración de email
 */
echo '<h2>Configuración de email SMTP</h2>';
echo '<form method="post">';
echo 'MAIL_HOST: <input name="mail_host" value="smtp.gmail.com"><br>';
echo 'MAIL_PORT: <input name="mail_port" value="587"><br>';
echo 'MAIL_USERNAME: <input name="mail_username"><br>';
echo 'MAIL_PASSWORD: <input name="mail_password" type="password"><br>';
echo 'MAIL_FROM_EMAIL: <input name="mail_from_email"><br>';
echo 'MAIL_FROM_NAME: <input name="mail_from_name" value="Indice SaaS"><br>';
echo '<button type="submit">Guardar</button>';
echo '</form>';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	echo '<p style="color:green">Configuración guardada (simulado)</p>';
}
