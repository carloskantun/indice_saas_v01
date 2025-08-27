<?php
/**
 * Installer — Helper para instalación y configuración inicial
 *
 * Escribe .env, testea conexión DB, etc.
 */
class Installer {
	/**
	 * Escribe el archivo .env a partir de .env.example y los valores del wizard
	 */
	public static function writeEnv(array $vars): bool {
		$tpl = file_get_contents(__DIR__ . '/../../.env.example');
		foreach ($vars as $k => $v) {
			$tpl = preg_replace('/^'.preg_quote($k,'/').'.*$/m', "$k=$v", $tpl);
		}
		return (bool) file_put_contents(__DIR__ . '/../../.env', $tpl);
	}

	/**
	 * Testea la conexión a la base de datos
	 */
	public static function testDb($host, $name, $user, $pass): bool {
		try {
			new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
			return true;
		} catch(Throwable $e){
			return false;
		}
	}
}
