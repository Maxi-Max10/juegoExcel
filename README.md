# Excel Snake

Aplicación web educativa para aprender Excel con 100 niveles progresivos, autenticación simple, progreso persistente, ranking y validación automática de fórmulas.

## Requisitos

- PHP 8.1 o superior
- MySQL 8 o MariaDB compatible
- Servidor local tipo Apache o el servidor embebido de PHP

## Instalación rápida

1. En Hostinger crea o usa la base de datos `u404968876_gameExcel` con el usuario `u404968876_gameExcel`.
2. Importa el archivo `database/schema.sql` desde phpMyAdmin o el panel de Hostinger dentro de esa base de datos.
3. El archivo `config/config.php` ya quedó preparado con estas credenciales por defecto:

```php
DB_HOST=localhost
DB_NAME=u404968876_gameExcel
DB_USER=u404968876_gameExcel
DB_PASS=gameExcel12
```

4. Si Hostinger te muestra otro host MySQL distinto de `localhost`, reemplázalo en `config/config.php`.
5. Inicia el proyecto con PHP en local o súbelo al hosting:

```bash
php -S localhost:8000
```

6. En local abre `http://localhost:8000`.

## Qué incluye

- Registro e inicio de sesión con sesiones PHP
- 100 niveles precargados en MySQL
- Validación de fórmulas con variantes aceptadas
- Desbloqueo progresivo y puntaje acumulado
- Sistema de vidas y racha
- Ranking de jugadores
- Interfaz responsive con animaciones, microinteracciones y sonidos generados por JavaScript

## Librerías frontend integradas

- GSAP por CDN para animaciones de entrada y movimiento sutil
- ScrollTrigger por CDN para revelar bloques al hacer scroll
- Font Awesome por CDN para iconografía

Este enfoque evita depender de Node o de un build step, lo que simplifica la publicación en Hostinger.