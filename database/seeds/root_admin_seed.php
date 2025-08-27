<?php
/**
 * Seed inicial: crea usuarios root/admin y planes demo
 */
require_once __DIR__ . '/../../config/config.php';
$db = getDB();

// Crear usuario root
$rootHash = password_hash('root123', PASSWORD_DEFAULT);
$db->prepare("INSERT INTO users(name,email,password_hash,is_active) VALUES(?,?,?,1)")
	->execute(['Root','root@dominio.com',$rootHash]);
$rootId = $db->lastInsertId();

// Crear planes demo
$db->exec("INSERT INTO plans(name,price_monthly,users_max,units_max,businesses_max,modules_included,is_active) VALUES
('Free',0,3,1,1,'[\"expenses\",\"human-resources\"]',1),
('Pro',75,25,10,25,'[\"*\"]',1)");

// Crear empresa demo
$db->exec("INSERT INTO companies(name,plan_id,created_by) VALUES('Demo Company',1,$rootId)");

// Crear usuario admin demo
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$db->prepare("INSERT INTO users(name,email,password_hash,is_active) VALUES(?,?,?,1)")
	->execute(['Admin Demo','admin@dominio.com',$adminHash]);
$adminId = $db->lastInsertId();

// Asociar root y admin a la empresa demo
$db->prepare("INSERT INTO user_companies(user_id,company_id,role) VALUES(?,?,?)")
	->execute([$rootId, 1, 'root']);
$db->prepare("INSERT INTO user_companies(user_id,company_id,role) VALUES(?,?,?)")
	->execute([$adminId, 1, 'admin']);
