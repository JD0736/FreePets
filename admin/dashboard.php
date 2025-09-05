<?php
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Obtener estadísticas
$stats = [];
$query = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$stats['usuarios'] = $query->fetch_assoc()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'disponible'");
$stats['mascotas_disponibles'] = $query->fetch_assoc()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'adoptado'");
$stats['mascotas_adoptadas'] = $query->fetch_assoc()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM reportes_mascotas WHERE estado = 'activo'");
$stats['reportes_activos'] = $query->fetch_assoc()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM donaciones WHERE estado = 'pendiente'");
$stats['donaciones_pendientes'] = $query->fetch_assoc()['total'];

$page_title = "Dashboard de Administración";
include 'header_admin.php';
?>

<div class="admin-content">
    <div class="breadcrumb">
        <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['usuarios']; ?></div>
            <div class="stat-label">Usuarios Registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['mascotas_disponibles']; ?></div>
            <div class="stat-label">Mascotas Disponibles</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['mascotas_adoptadas']; ?></div>
            <div class="stat-label">Mascotas Adoptadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['reportes_activos']; ?></div>
            <div class="stat-label">Reportes Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['donaciones_pendientes']; ?></div>
            <div class="stat-label">Donaciones Pendientes</div>
        </div>
    </div>

    <div class="admin-grid">
        <div class="admin-card">
            <h3><i class="fas fa-chart-line"></i> Actividad Reciente</h3>
            <div class="activity-content">
                <p>Gráficos y estadísticas de actividad reciente...</p>
                <div class="placeholder-chart">
                    <p>Próximamente: Gráficos de usuarios registrados, adopciones, donaciones, etc.</p>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-bell"></i> Alertas Recientes</h3>
            <ul class="alert-list">
                <li>
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Nuevo reporte de mascota recibido</span>
                    <small>Hace 2 horas</small>
                </li>
                <li>
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Donación pendiente de revisión</span>
                    <small>Hace 5 horas</small>
                </li>
                <li>
                    <i class="fas fa-user-plus"></i>
                    <span>Usuario nuevo registrado</span>
                    <small>Hace 1 día</small>
                </li>
            </ul>
        </div>
    </div>

    <div class="admin-grid">
        <div class="admin-card">
            <h3><i class="fas fa-tasks"></i> Tareas Pendientes</h3>
            <ul class="task-list">
                <li>
                    <input type="checkbox" id="task1">
                    <label for="task1">Revisar 5 solicitudes de adopción</label>
                </li>
                <li>
                    <input type="checkbox" id="task2">
                    <label for="task2">Actualizar información de mascotas</label>
                </li>
                <li>
                    <input type="checkbox" id="task3">
                    <label for="task3">Verificar reportes de situación</label>
                </li>
                <li>
                    <input type="checkbox" id="task4">
                    <label for="task4">Contactar donantes recientes</label>
                </li>
            </ul>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-calendar"></i> Próximos Eventos</h3>
            <div class="events-list">
                <div class="event-item">
                    <div class="event-date">15 Dic</div>
                    <div class="event-info">
                        <h4>Campaña de Vacunación</h4>
                        <p>Parque Central - 9:00 AM</p>
                    </div>
                </div>
                <div class="event-item">
                    <div class="event-date">20 Dic</div>
                    <div class="event-info">
                        <h4>Evento de Adopción</h4>
                        <p>Centro Comunitario - 10:00 AM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer_admin.php';
?>