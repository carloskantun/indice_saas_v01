# Indice SaaS

Plataforma SaaS modular en PHP que permite habilitar módulos según el plan de cada empresa.

## Requisitos
- PHP 8.0+ con extensiones `PDO`, `pdo_mysql`, `openssl`, `mbstring`, `json`
- MySQL 5.7+ o MariaDB 10.3+

## Instalación local rápida
```bash
cp .env.example .env
php -S localhost:8000 -t public
```
Luego abre [http://localhost:8000/install/](http://localhost:8000/install/) en el navegador.

## Documentación adicional
- Consulta [readme1.md](readme1.md) para el **roadmap** y planeación del proyecto.
- Revisa [indice_saa_s_starter_readme_agents_from_scratch.md](indice_saa_s_starter_readme_agents_from_scratch.md) como guía de **módulos y roles**.

## Scripts útiles
- `composer lint` — Ejecuta PHP_CodeSniffer con estándar PSR-12.
- `composer migrate` — Corre las migraciones de base de datos definidas en `database/migrations`.

