# Índice SaaS — Plan maestro y flujo v2 (reorganizado)

> Documento de alineación para continuar el desarrollo desde cero (conservando lo útil) y lograr un **producto instalable**, **suscribible por planes** y **operable por módulos** con navegación simple centrada en el usuario.

---

## 1) Visión y objetivo
- **Visión**: Un SaaS modular (PHP + MySQL) donde una empresa contrata un **plan** y con base en ese plan **activa módulos**. El dueño (superadmin) invita usuarios y les asigna permisos por rol/módulo.
- **Objetivo de esta versión**:
  1. **Instalador web** + seeds (root/admin)
  2. **Planes habilitan módulos**
  3. **Navegación centrada en módulos** (no jerarquía larga) con “Mi Alcance” por rol/permisos
  4. **Invitaciones por email** + notificaciones mínimas
  5. **Panel Root** útil (planes/empresas) y **preparado** para pagos

---

## 2) Flujo de negocio (simple y vertical)

### 2.1 Adquisición
1. **Marketing** (`indiceapp.com/planes.php`) → el usuario elige un plan.
2. **Checkout** (ahora stub / a integrar) → crea **empresa** + **superadmin** en `app.indiceapp.com`.
3. Redirección al **Onboarding** en `app.indiceapp.com`.

> Mientras se integra el pago, el superadmin puede **seleccionar plan** durante el registro y se marca como `pending` (o `active` si es demo).

### 2.2 Activación de módulos
- El plan define `modules_included` (JSON).
- Al confirmar el plan, se activa un **catálogo** (grid) con los módulos disponibles para esa empresa.

### 2.3 Operación (navegación centrada en módulos)
- **Landing post-login**: **Grid de módulos** permitidos para el usuario.
- En cada módulo, el usuario ve **solo su alcance** (empresa/unidad/negocio) según rol/permisos.
- Un **menú hamburguesa** abre el panel lateral con:
  - **Mi Alcance**: selector rápido de (empresa/unidad/negocio) **solo cuando aplica**.
  - **Filtro de visibilidad**: *“Ver solo lo que me corresponde”* (por default ON).
  - Accesos: Notificaciones, Perfil, Configuración.

> La jerarquía (Empresa→Unidad→Negocio) **no bloquea** el flujo. Si el usuario solo tiene 1 empresa/unidad/negocio, se preselecciona y **no se obliga a navegar** por niveles.

### 2.4 Administración
- **Root**: gestiona planes, ve métricas globales y administra empresas.
- **Superadmin**: gestiona su empresa, activa módulos del plan, invita usuarios, asigna roles.
- **Admin/Moderator/User**: operan en los módulos según permisos.

---

## 3) Roles y permisos (resumen operativo)

| Rol | Entra | Capacidades clave |
|---|---|---|
| root | `/panel_root/` | CRUD planes, ver empresas, métricas; soporte y auditoría. |
| superadmin | `/` (grid módulos) + `/admin/` | Activa módulos del plan, invita usuarios, asigna roles/permisos. |
| admin | `/` (grid módulos) | Operación de módulos; puede invitar si el superadmin lo permite. |
| moderator | Módulos asignados | Supervisión y reportes. |
| user | Módulos asignados | Operación básica. |

**Claves de permisos** (ejemplos):
- `panel_root.view`, `panel_root.edit`
- `admin.invite_user`, `admin.manage_roles`, `admin.permissions`
- `expenses.view`, `expenses.edit`, `expenses.delete`, `expenses.kpis`, `expenses.export`
- `human-resources.view`, `employees.attendance`

---

## 4) Navegación y UX (v2)

### 4.1 Landing → Catálogo de módulos (grid)
- Tarjetas por módulo (icono, nombre, breve descripción, estado: habilitado/pendiente).
- Badge de “Incluido por tu plan” o “Requiere upgrade”.
- **Búsqueda** y **favoritos**.

### 4.2 Menú hamburguesa (panel lateral)
- **Mi Alcance** (selector, solo visible si hay más de 1 opción):
  - Empresa (si >1)
  - Unidad (si >1 y permiso)
  - Negocio (si >1 y permiso)
- **Filtro**: "Mostrar solo mis permisos" (ON por defecto)
- **Accesos**: Notificaciones, Perfil, Idioma, Cerrar sesión.

### 4.3 Dentro de un módulo
- Encabezado con breadcrumb acotado (solo si hay variedad de contexto).
- Filtros rápidos (propios del módulo) y botones core.
- Respetar `hasPermission()` para acciones.

---

## 5) Arquitectura técnica

### 5.1 Instalador web
- Ruta: `/install/` (pasos: requisitos → DB → APP → Mail → Migrar/Seed → Final).
- Genera `.env` y ejecuta migraciones + seeds (root/admin + planes demo + empresa demo).
- Credenciales iniciales:
  - `root@dominio.com / root123`
  - `admin@dominio.com / admin123`

### 5.2 Esquema mínimo (tablas núcleo)
- `users(id,name,email,password_hash,is_active,created_at)`
- `companies(id,name,plan_id,created_by,created_at)`
- `units(id,company_id,name)`
- `businesses(id,unit_id,name)`
- `plans(id,name,description,price_monthly,users_max,units_max,businesses_max,storage_max_mb,modules_included JSON,is_active)`
- `user_companies(id,user_id,company_id,role)`
- `permissions(id,key,description)`
- `role_permissions(id,role,permission_id)`
- `invitations(id,email,company_id,unit_id,business_id,role,token,status,sent_date,expiration_date)`
- `notifications(id,user_id,company_id,message,created_at)`

> Los módulos crean sus propias tablas (p. ej. `expenses`, `providers`, ...).

### 5.3 Helpers y middlewares
- **Auth**: `auth()`, `currentUser()`, `checkRole([...])`
- **Permisos**: `hasPermission(key)`
- **Scope**: `resolveScope()` devuelve `(company_id, unit_id, business_id)` decidido por "Mi Alcance" y permisos del usuario.
- **PlanLimiter**: `checkPlanLimit(company_id, type)` antes de crear usuarios/unidades/negocios; `planAllowsModule(company_id, module_slug)`.
- **Mailer**: `sendMail($to,$subj,$html)` con `.env`.
- **Notify**: `createNotification(user_id, company_id, message)` y `getNotifications(user_id)`.

### 5.4 Activación de módulos por plan
- `plans.modules_included` guarda un JSON (p. ej. `["expenses","human-resources"]` o `"*"`).
- En login se calcula `availableModules = intersect(modules_included, user_permissions)` y se pintan en el grid.

### 5.5 Rutas clave (ejemplo)
- `GET /` → grid de módulos
- `GET /module/{slug}` → index del módulo (verifica permiso y plan)
- `GET /admin/` → herramientas de empresa (solo superadmin)
- `GET /panel_root/` → administración global (root)
- `POST /admin/invitations` → crear invitación (envía email)
- `GET  /admin/accept_invitation?token=...` → alta/aceptación

---

## 6) Integraciones pendientes (MVP listo para crecer)
- **Pagos**: mantener stub (`/payments/checkout.php`, `webhook.php`) y UI de planes; al integrar, el checkout creará empresa + superadmin y marcará plan `active`.
- **SMTP desde panel**: pantalla para configurar y botón "Enviar prueba".
- **Notificaciones**: centro básico (badge + dropdown) con últimas 20, ya funcional.

---

## 7) Módulos

### 7.1 Estado actual
- **Expenses**: listo (CRUD, KPIs, export, permisos) → migrar a estructura `modules/expenses/`.
- **Human Resources**: pase de lista implementado → migrar a estructura estándar.

### 7.2 Backlog de módulos
| Módulo | Estado | Nota |
|---|---|---|
| analytics | En desarrollo | KPIs globales |
| chat | En desarrollo | Mensajería interna |
| cleaning | En desarrollo | Tareas de limpieza |
| crm | En desarrollo | Relaciones con clientes |
| expenses | Activo | Completo |
| forms | En desarrollo | Constructor |
| human-resources | Activo | Pase de lista |
| inventory | En desarrollo | Stock |
| invoicing | En desarrollo | Facturación |
| kpis | En desarrollo | Tablero |
| laundry | En desarrollo | Lavandería |
| maintenance | En desarrollo | Órdenes/reporte |
| minutes | En desarrollo | Actas |
| petty-cash | En desarrollo | Caja chica |
| pos | En desarrollo | Punto de venta |
| processes-tasks | En desarrollo | Flujos |
| properties | En desarrollo | Propiedades |
| sales-agent | En desarrollo | Agentes |
| settings | En desarrollo | Config negocio |
| template-module | Experimental | Boilerplate |
| training | En desarrollo | Capacitaciones |
| transportation | En desarrollo | Transporte |
| vehicles | En desarrollo | Vehículos |

---

## 8) Roadmap de entrega (por fases)

### Fase 0 — Base
- [ ] **Instalador `/install/`** (wizard + .env + migraciones + seeds root/admin/planes/empresa demo)
- [ ] **Helpers núcleo** (Auth, Permisos, Scope, PlanLimiter, Mailer, Notify)
- [ ] **Grid de módulos** post-login + menú hamburguesa (Mi Alcance + filtro)

### Fase 1 — Operable
- [ ] **Invitaciones por email** (crear token + enviar + aceptar)
- [ ] **Notificaciones mínimas** (badge + dropdown)
- [ ] **Panel Root v1** (CRUD de planes / ver empresas)

### Fase 2 — Valor
- [ ] **Migración módulo Expenses** a estructura estándar + permisos
- [ ] **Migración HR (pase de lista)** a estructura estándar
- [ ] **SMTP en panel** (config + enviar prueba)

### Fase 3 — Comercial
- [ ] **UI de planes y upgrade** dentro del app
- [ ] **Stub de pagos** (Stripe/PayPal/Mercado Pago)
- [ ] **Webhooks** → activar plan y empresa

---

## 9) Criterios de aceptación (MVP)
- Al subir y abrir `/install/` se instala, crea `.env` y puedo **loguearme** como `root` o `admin`.
- Como **root**, puedo crear/editar **planes** y ver empresas.
- Como **superadmin/admin**, veo el **grid de módulos** que mi plan permite.
- Puedo **invitar** a un usuario, este recibe un **email**, acepta con link y **aparece** en la empresa con su rol.
- En un módulo, solo veo **mi alcance** (empresa/unidad/negocio) y acciones según **permisos**.

---

## 10) Tareas técnicas inmediatas
1. **Crear estructura de proyecto** (public/config/database/src/lang/vendor).
2. **Wizard `/install/`** + `Installer.php` (test DB, write .env, run migraciones/seeds).
3. **Migraciones núcleo** + **Seeds** (root/admin/planes/empresa).
4. **Helpers** (Auth, Permisos, Scope, PlanLimiter, Mailer, Notify).
5. **Grid de módulos** + **menú hamburguesa**.
6. **Invitaciones + email** (flujo aceptado).
7. **Panel Root v1** (CRUD planes, lista empresas).
8. **Migrar Expenses/HR** a `modules/*`.
9. **Pantalla SMTP** (config + test).
10. **Stub pagos** + rutas básicas.

---

## 11) Notas de implementación
- Si el usuario solo tiene 1 empresa/unidad/negocio, **auto-seleccionar** y ocultar selectores.
- Mantener nombres de tablas **en inglés**.
- Código **PSR-12** + `phpcs` en `composer scripts`.
- Evitar dependencias complejas; PHP nativo con Bootstrap 5.

---

## 12) Riesgos y mitigaciones
- **Email no configurado** → mostrar alerta y permitir reenviar invitaciones al configurar SMTP.
- **Pagos aún no integrados** → permitir plan demo con expiración.
- **Permisos mal asignados** → agregar `debug_permissions.php` por módulo.

---

## 13) Entregables
- Código fuente instalable con `/install/`.
- README de despliegue y uso.
- Scripts de migración y seeds.
- Módulos Expenses/HR migrados.
- Panel Root v1.

