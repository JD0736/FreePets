<?php
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo isset($page_title) ? $page_title : 'FreePets'; ?></title>
    <link rel="stylesheet" href="../vista/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<div class="admin-header">
    <h2><i class="fas fa-cog"></i> Panel de Administración - FreePets</h2>
    <div>
        <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
        <a href="../logout.php" style="color: white; margin-left: 1rem;">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</div>

<nav class="admin-nav">
    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Usuarios
    </a>
    <a href="mascotas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'mascotas.php' ? 'active' : ''; ?>">
        <i class="fas fa-paw"></i> Mascotas
    </a>
    <a href="reportes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : ''; ?>">
        <i class="fas fa-flag"></i> Reportes
    </a>
    <a href="donaciones.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'donaciones.php' ? 'active' : ''; ?>">
        <i class="fas fa-hand-holding-heart"></i> Donaciones
    </a>
    <a href="eventos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'eventos.php' ? 'active' : ''; ?>">
        <i class="fas fa-calendar"></i> Eventos
    </a>
    <a href="../index.php" style="background: #df9d40;">
        <i class="fas fa-home"></i> Volver al Sitio
    </a>
</nav>

<div class="admin-content">
    <?php if (isset($page_title)): ?>
    <div class="breadcrumb">
        <h3><?php echo $page_title; ?></h3>
    </div>
    <?php endif; ?>