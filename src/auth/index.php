<?php
/**
 * Login, registro y logout básicos
 */
session_start();
require_once __DIR__ . '/../core/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = $_POST['email'] ?? '';
	$pass = $_POST['password'] ?? '';
	$db = getDB();
	$stmt = $db->prepare('SELECT id, password_hash FROM users WHERE email = ? AND is_active = 1');
	$stmt->execute([$email]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($user && password_verify($pass, $user['password_hash'])) {
		$_SESSION['user_id'] = $user['id'];
		header('Location: /panel_root/index.php');
		exit;
	} else {
		echo '<p style="color:red">Login incorrecto</p>';
	}
}
echo '<div class="row justify-content-center mt-5">';
echo '<div class="col-md-6 col-lg-4">';
echo '<div class="card shadow">';
echo '<div class="card-body">';
echo '<h2 class="mb-4 text-center">Login</h2>';
echo '<form method="post">';
echo '<div class="mb-3">';
echo '<label for="email" class="form-label">Email</label>';
echo '<input name="email" id="email" class="form-control" autocomplete="username">';
echo '</div>';
echo '<div class="mb-3">';
echo '<label for="password" class="form-label">Password</label>';
echo '<input name="password" id="password" type="password" class="form-control" autocomplete="current-password">';
echo '</div>';
echo '<button type="submit" class="btn btn-primary w-100">Ingresar</button>';
echo '</form>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
