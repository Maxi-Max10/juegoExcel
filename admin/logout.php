<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

unset($_SESSION['admin_id'], $_SESSION['admin_username']);
set_flash('success', 'Sesión de administrador cerrada.');
redirect('login.php');
