# README.md — Indice SaaS • Instalador & Starter Kit (v1.0.0)

Este paquete convierte **Indice** en un **producto instalable** (PHP + MySQL) listo para subir a cualquier hosting compartido o VPS. Incluye un **asistente de instalación web** (wizard), migraciones, *seeders* y cuentas iniciales para **root** y **admin**.

---

## 🧭 Resumen
- **Multi-empresa / multi-rol** con jerarquías (empresa → unidad → negocio) y **permisos granulares**.
- **Módulos** activables (p. ej. *expenses*, *human-resources*) con estructura estándar.
- **Panel Root** para crear/editar **planes SaaS** y asignarlos a empresas.
- **Sistema de invitaciones** con tokens y aceptación.
- **Instalador web** que configura DB, APP_URL, SMTP y crea **usuarios iniciales**.

> Objetivo: que con **subir el ZIP** y abrir `/install/` se deje instalado un SaaS mínimo funcional con acceso para `root` y `admin` y la base para crecer por módulos.

---

## 📦 Requisitos
- **PHP 8.0+** con extensiones: `PDO`, `pdo_mysql`, `openssl`, `mbstring`, `json`.
- **MySQL 5.7+ / MariaDB 10.3+**.
- Servidor web (Apache/Nginx). En Apache, activar `AllowOverride All` si se usa `.htaccess`.

---

## 🚀 Instalación Rápida (hosting)
1. **Sube** el contenido del proyecto al servidor (raíz web o subcarpeta).
2. **Abre** en el navegador: `https://tu-dominio.com/install/`.
3. El asistente te guiará por 6 pasos:
   1) Requisitos → 2) Base de datos → 3) Config APP → 4) Email → 5) Migraciones/Seeds → 6) Finalizar.
4. Al finalizar verás el botón **“Ir al Login”**. Ingresa con:
   - **Root:** `root@dominio.com` / `root123`
   - **Admin demo:** `admin@dominio.com` / `admin123`

> ⚠️ **Seguridad**: Cambia ambas contraseñas inmediatamente después del primer ingreso.

---

## 🧑‍💻 Instalación Local (dev)
```bash
cp .env.example .env
php -S localhost:8000 -t public
# o usa tu stack local (Apache/Nginx)
```
Luego abre `http://localhost:8000/install/`.

---

## 🏗️ Estructura del Proyecto
```
indice-saas/
├─ public/
│  ├─ index.php              # Front-controller
│  └─ install/               # Asistente de instalación (wizard)
│     ├─ index.php           # Paso 1: requisitos + router de pasos
│     ├─ steps/              # Vistas parciales del wizard
│     │  ├─ step1_requirements.php
│     │  ├─ step2_database.php
│     │  ├─ step3_app.php
│     │  ├─ step4_mail.php
│     │  ├─ step5_migrate.php
│     │  └─ step6_finish.php
│     └─ Installer.php       # Clase helper del instalador
├─ config/
│  ├─ config.php             # Carga .env, helpers DB/APP
│  └─ routes.php             # Rutas básicas (si usas micro-MVC)
├─ database/
│  ├─ migrations/            # SQL y/o PHP por orden
│  ├─ seeds/                 # Seeds iniciales (root/admin/planes demo)
│  └─ migrate.php            # Runner de migraciones
├─ src/
│  ├─ auth/                  # login/register/logout
│  ├─ core/                  # helpers: auth(), hasPermission(), getDB()
│  ├─ admin/                 # panel admin de empresa + invitaciones
│  ├─ panel_root/            # panel root (planes SaaS)
│  └─ modules/               # módulos funcionales (expenses, hr, ...)
├─ lang/
│  ├─ es.php
│  └─ en.php
├─ vendor/                   # Composer (si aplica)
├─ .env.example
├─ composer.json
└─ README.md
```

---

## 🔐 Cuentas iniciales y roles
- **Root (SaaS):** `root@dominio.com` → administra planes y empresas globales.
- **Admin (demo empresa):** `admin@dominio.com` → administra una empresa de ejemplo.

**Contraseñas por defecto**:
- `root123` y `admin123` (obligatorio cambiarlas al primer login).

---

## 🧱 Esquema mínimo de base de datos (resumen)
> La migración crea un conjunto mínimo y estandariza nombres en inglés.

- `users` (id, name, email [unique], password_hash, is_active, created_at)
- `companies` (id, name, plan_id, created_by, created_at)
- `units` (id, company_id, name)
- `businesses` (id, unit_id, name)
- `plans` (id, name, description, price_monthly, users_max, businesses_max, units_max, storage_max_mb, modules_included JSON, is_active)
- `user_companies` (id, user_id, company_id, role)
- `permissions` (id, key, description)
- `role_permissions` (id, role, permission_id)
- `invitations` (id, email, company_id, unit_id NULL, business_id NULL, role, token, status ENUM('pending','accepted','expired'), sent_date, expiration_date)
- `notifications` (id, user_id, company_id, message, created_at)

> Los módulos crearán sus propias tablas (`expenses`, `providers`, etc.).

---

## 🧩 Módulos
Cada módulo vive en `src/modules/{slug}/` con esta forma:
```
modules/expenses/
├─ index.php
├─ controller.php
├─ config.php
├─ js/expenses.js
└─ README.md
```
**Permisos**: Los módulos consumen `hasPermission('expenses.view')`, `...edit`, `...kpis`, etc. Define las claves en `permissions` y asígnalas por `role_permissions`.

---

## 🧰 Instalador Web (wizard)
**Flujo de pasos**
1. **Requisitos**: versión PHP/extensiones.
2. **Base de datos**: host, nombre, usuario, password.
3. **App**: `APP_URL`, `APP_ENV`, `APP_DEBUG`.
4. **Email**: host SMTP, puerto, usuario, contraseña, from.
5. **Migraciones**: ejecuta `database/migrate.php` y `database/seeds/*`.
6. **Finalizar**: muestra credenciales iniciales, botón “Ir al login”.

**Salida**: genera `.env` desde `.env.example` con los valores del wizard.

---

## ⚙️ Variables de entorno (.env)
```ini
DB_HOST=localhost
DB_NAME=indice_saas
DB_USER=usuario
DB_PASS=secreto

APP_URL=https://tu-dominio.com
APP_ENV=production
APP_DEBUG=false

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email
MAIL_PASSWORD=tu_password_app
MAIL_ENCRYPTION=tls
MAIL_FROM_EMAIL=no-reply@tu-dominio.com
MAIL_FROM_NAME="Indice SaaS"
```

---

## ✉️ Email & Notificaciones (mínimo viable)
- **Email**: helper `sendMail($to,$subject,$html)` que usa `.env`.
- **Invitaciones**: al crear token se **envía correo** con link `.../admin/accept_invitation.php?token=XYZ`.
- **Notificaciones internas**: función simple `createNotification($user_id,$company_id,$message)` y un badge en la navbar (últimas 20).

> Objetivo: que las invitaciones sean **operativas** desde el día 1; el centro de notificaciones puede crecer después.

---

## 💳 Pagos (stub listo para integrar)
- `src/payments/checkout.php`, `webhook.php`, `plans.php` (UI planes) **incluidos como plantilla**.
- Implementar Stripe/PayPal/Mercado Pago más adelante sin bloquear el uso.

---

## 🔒 Seguridad
- `password_hash()` / `password_verify()`
- PDO + consultas preparadas
- Sanitización `htmlspecialchars()` en vistas
- CSRF tokens en formularios sensibles (pendiente v1.1)
- Regeneración de ID de sesión al login

---

## 🧪 Calidad & Scripts
- **Composer** con `phpcs` (PSR-12) y linter.
```json
{
  "require": {},
  "require-dev": {
    "squizlabs/php_codesniffer": "^3.9"
  },
  "scripts": {
    "lint": "phpcs --standard=PSR12 src admin panel_root",
    "migrate": "php database/migrate.php"
  }
}
```

---

## 🧰 Snippets clave
**Installer.php (esqueleto)**
```php
class Installer {
  public static function writeEnv(array $vars): bool {
    $tpl = file_get_contents(__DIR__ . '/../../.env.example');
    foreach ($vars as $k => $v) { $tpl = preg_replace('/^'.preg_quote($k,'/').'.*$/m', "$k=$v", $tpl); }
    return (bool) file_put_contents(__DIR__ . '/../../.env', $tpl);
  }
  public static function testDb($h,$n,$u,$p): bool {
    try { new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); return true; } catch(Throwable $e){ return false; }
  }
}
```

**Seed inicial (resumen)**
```php
$hash = password_hash('root123', PASSWORD_DEFAULT);
$db->prepare("INSERT INTO users(name,email,password_hash,is_active) VALUES(?,?,?,1)")
   ->execute(['Root','root@dominio.com',$hash]);
$rootId = $db->lastInsertId();

$db->exec("INSERT INTO plans(name,price_monthly,users_max,units_max,businesses_max,modules_included,is_active) VALUES
('Free',0,3,1,1,'["expenses","human-resources"]',1),
('Pro',75,25,10,25,'["*"]',1)");

$db->exec("INSERT INTO companies(name,plan_id,created_by) VALUES('Demo Company',1,$rootId)");
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$db->prepare("INSERT INTO users(name,email,password_hash,is_active) VALUES(?,?,?,1)")
   ->execute(['Admin Demo','admin@dominio.com',$adminHash]);
$adminId = $db->lastInsertId();
$db->prepare("INSERT INTO user_companies(user_id,company_id,role) VALUES(?,?,?)")
   ->execute([$rootId, 1, 'root']);
$db->prepare("INSERT INTO user_companies(user_id,company_id,role) VALUES(?,?,?)")
   ->execute([$adminId, 1, 'admin']);
```

---

## 🧑‍✈️ Primeros pasos tras instalar
1. Entra como **root** → `/panel_root/` → crea/edita **planes**.
2. Ingresa como **admin** → prueba **invitaciones** y **módulos**.
3. Configura **SMTP** en `/admin/settings/email` y envía un **email de prueba**.

---

## 🩹 Troubleshooting
- *No conecta a DB*: verifica host/usuario/pass y privilegios del usuario.
- *No envía correos*: revisa `MAIL_*` y usa **contraseñas de aplicación**.
- *HTTP 500*: activa `APP_DEBUG=true` temporalmente y consulta el log.

---

## 📄 Licencia
MIT. Atribución recomendada a "Indice SaaS".

---

# AGENTS.md — Roles, Permisos y Módulos (v1.0.0)

## 👤 Roles
- **root**: control total del SaaS (planes, empresas, estadísticas globales).
- **support**: lectura/diagnóstico limitado.
- **superadmin**: dueño de empresa (usuarios, unidades, negocios, módulos).
- **admin**: gestión operativa de su empresa.
- **moderator**: supervisión local.
- **user**: uso básico.

> Un usuario puede tener **múltiples roles por empresa**. El *contexto activo* (empresa → unidad → negocio) se almacena en sesión.

### Sesión (estándar)
```php
$_SESSION['user_id'];
$_SESSION['company_id'];
$_SESSION['unit_id'];
$_SESSION['business_id'];
$_SESSION['current_role'];
```

## 🔐 Permisos (claves sugeridas)
- `panel_root.view`, `panel_root.edit`
- `admin.invite_user`, `admin.manage_roles`, `admin.permissions`
- `expenses.view`, `expenses.edit`, `expenses.delete`, `expenses.kpis`, `expenses.export`
- `human-resources.view`, `human-resources.edit`, `employees.attendance`
- `settings.view`, `settings.edit`

**Helpers**
```php
function auth(){ /* verifica login */ }
function checkRole(array $roles){ /* valida rol actual */ }
function hasPermission(string $key){ /* busca en role_permissions */ }
```

## 🧩 Módulos (slugs en inglés)
- `expenses`, `human-resources`, `inventory`, `crm`, `maintenance`, `kpis`, `forms`, `transportation`, `vehicles`, `pos`, `petty-cash`, `processes-tasks`, `properties`, `training`, `minutes`, `chat`, `analytics`.

### Esqueleto de un módulo
```
modules/{slug}/
├─ index.php
├─ controller.php
├─ config.php
├─ js/{slug}.js
└─ README.md
```
**Ejemplo de guard de acceso**
```php
auth();
checkRole(['admin','superadmin','root']);
if(!hasPermission('expenses.view')){ http_response_code(403); exit('Access denied'); }
```

## 📬 Invitaciones
- Endpoint de creación: `POST /admin/controller.php?action=send_invitation` con `email`, `role`, `company_id`.
- **Flujo**: crear token → enviar email → `GET /admin/accept_invitation.php?token=...` → alta de usuario / asociación a empresa → notificación interna.

## 🛎️ Notificaciones (simple)
- Tabla `notifications(user_id, company_id, message, created_at)`.
- Navbar muestra última veintena.

## 💳 Planes SaaS
- Tabla `plans` con límites (`users_max`, `units_max`, `businesses_max`, `modules_included`, `storage_max_mb`).
- Helper `checkPlanLimit($company_id, $type)`.
- Validar en el backend antes de permitir crear usuarios/unidades/negocios.

## 🧪 Calidad
- PSR-12 via PHP_CodeSniffer (`composer lint`).
- Scripts de migración (`composer migrate`).

## 🔜 Roadmap
- CSRF tokens + audit log.
- Centro de notificaciones avanzado + tiempo real.
- Pasarela de pagos (Stripe/PayPal) y facturación.
- PWA + tema oscuro.

