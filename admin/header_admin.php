<?php
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo isset($page_title) ? $page_title : 'FreePets'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<div class="admin-header">
    <div class="admin-header-content">
        <h2><i class="fas fa-cog"></i> Panel de Administración - FreePets</h2>
        <div class="admin-user-info">
            <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>
</div>

<nav class="admin-nav">
    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="admin_usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_usuarios.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Usuarios
    </a>
    <a href="admin_mascotas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_mascotas.php' ? 'active' : ''; ?>">
        <i class="fas fa-paw"></i> Mascotas
    </a>
    <a href="admin_reportes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_reportes.php' ? 'active' : ''; ?>">
        <i class="fas fa-flag"></i> Reportes
    </a>
    <a href="admin_donaciones.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_donaciones.php' ? 'active' : ''; ?>">
        <i class="fas fa-hand-holding-heart"></i> Donaciones
    </a>
    <a href="admin_eventos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_eventos.php' ? 'active' : ''; ?>">
        <i class="fas fa-calendar"></i> Eventos
    </a>
    <a href="../index.php" class="back-to-site">
        <i class="fas fa-home"></i> Volver al Sitio
    </a>
</nav>

<main class="admin-main">