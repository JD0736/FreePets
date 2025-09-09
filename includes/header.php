<?php
// Verificar si hay una sesión activa
$usuario_autenticado = isset($_SESSION['usuario_id']);
$es_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="vista/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <?php if (isset($additional_css)): ?>
        <link rel="stylesheet" href="<?php echo $additional_css; ?>">
    <?php endif; ?>
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">
        <img src="multimedia/Imagen1.png" alt="Logo Free Pets">
    </a>

    <nav class="navbar">
        <a href="index.php#home" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Inicio</a>
        <div class="dropdown">
            <a href="index.php#about" class="dropbtn <?php echo (basename($_SERVER['PHP_SELF']) == 'mision_y_vision.php') ? 'active' : ''; ?>">Nosotros</a>
            <div class="dropdown-content">
                <a href="mision_y_vision.php">Misión y Visión</a>
            </div>
        </div>
        <div class="dropdown">
            <a href="index.php#services" class="dropbtn <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['adopcion.php', 'eventos.php', 'reportes_mascotas_situaciones.php', 'donacion.php'])) ? 'active' : ''; ?>">Servicios</a>
            <div class="dropdown-content">
                <a href="adopcion.php">Centro de adopción</a>
                <a href="eventos.php">Eventos solidarios</a>
                <a href="reportes_mascotas_situaciones.php">Reportar mascotas</a>
                <a href="donacion.php">Donar</a>
            </div>
        </div>
        <a href="index.php#contact" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['contact']) ? 'class="active"' : ''; ?>>Contacto</a>
        
        <?php if ($usuario_autenticado): ?>
            <?php if ($es_admin): ?>
                <div class="dropdown">
                    <a href="admin_usuarios.php" class="dropbtn <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_usuarios.php') ? 'active' : ''; ?>">Administrar</a>
                    <div class="dropdown-content">
                        <a href="admin_usuarios.php">Usuarios</a>
                        <a href="adopcion.php">Mascotas</a>
                        <a href="eventos.php">Eventos</a>
                        <a href="reportes_mascotas_situaciones.php">Reportes</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <a href="perfil.php" <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'class="active"' : ''; ?>>Mi Perfil</a>
            <a href="logout.php">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>
        <?php else: ?>
            <a href="login.php" <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'class="active"' : ''; ?>>Iniciar Sesión</a>
        <?php endif; ?>
    </nav>

    <div class="icons">
        <div class="fas fa-bars" id="menu-btn"></div>
    </div>
</header>

<main>