<?php
/**
 * Helpers principales: autenticación, roles, permisos, conexión DB
 */

session_start();

function getDB() {
	static $db = null;
	if ($db === null) {
		$env = parse_ini_file(__DIR__ . '/../../.env');
		$db = new PDO(
			"mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
			$env['DB_USER'],
			$env['DB_PASS'],
			[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
		);
	}
	return $db;
}

function auth() {
	if (empty($_SESSION['user_id'])) {
		header('Location: /auth/login.php');
		exit;
	}
}

function checkRole(array $roles) {
	if (!isset($_SESSION['current_role']) || !in_array($_SESSION['current_role'], $roles)) {
		http_response_code(403);
		exit('Access denied: insufficient role');
	}
}

function hasPermission(string $key): bool {
	if (!isset($_SESSION['current_role'])) return false;
	$db = getDB();
	$stmt = $db->prepare("SELECT 1 FROM role_permissions rp JOIN permissions p ON rp.permission_id = p.id WHERE rp.role = ? AND p.key = ? LIMIT 1");
	$stmt->execute([$_SESSION['current_role'], $key]);
	return (bool) $stmt->fetchColumn();
}
