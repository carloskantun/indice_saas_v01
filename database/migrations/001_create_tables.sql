-- Migración mínima: tablas base para Indice SaaS
CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(150) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	is_active TINYINT(1) DEFAULT 1,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE companies (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	plan_id INT NOT NULL,
	created_by INT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE units (
	id INT AUTO_INCREMENT PRIMARY KEY,
	company_id INT NOT NULL,
	name VARCHAR(100) NOT NULL
);

CREATE TABLE businesses (
	id INT AUTO_INCREMENT PRIMARY KEY,
	unit_id INT NOT NULL,
	name VARCHAR(100) NOT NULL
);

CREATE TABLE plans (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(50) NOT NULL,
	description TEXT,
	price_monthly DECIMAL(10,2) DEFAULT 0,
	users_max INT DEFAULT 3,
	businesses_max INT DEFAULT 1,
	units_max INT DEFAULT 1,
	storage_max_mb INT DEFAULT 100,
	modules_included JSON,
	is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE user_companies (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	company_id INT NOT NULL,
	role VARCHAR(30) NOT NULL
);

CREATE TABLE permissions (
	id INT AUTO_INCREMENT PRIMARY KEY,
	`key` VARCHAR(50) NOT NULL,
	description VARCHAR(255)
);

CREATE TABLE role_permissions (
	id INT AUTO_INCREMENT PRIMARY KEY,
	role VARCHAR(30) NOT NULL,
	permission_id INT NOT NULL
);

CREATE TABLE invitations (
	id INT AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(150) NOT NULL,
	company_id INT NOT NULL,
	unit_id INT,
	business_id INT,
	role VARCHAR(30) NOT NULL,
	token VARCHAR(64) NOT NULL,
	status ENUM('pending','accepted','expired') DEFAULT 'pending',
	sent_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	expiration_date TIMESTAMP
);

CREATE TABLE notifications (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	company_id INT NOT NULL,
	message VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
