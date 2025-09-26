<?php
session_start();
require_once '../admin/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
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

// Obtener actividad reciente
$actividad_reciente = [];
$query_actividad = $conn->query("
    (SELECT 'usuario' as tipo, nombre, fecha_registro as fecha FROM usuarios ORDER BY fecha_registro DESC LIMIT 3)
    UNION
    (SELECT 'adopcion' as tipo, CONCAT('Adopción de ', m.nombre) as nombre, a.fecha_solicitud as fecha 
     FROM adopciones a JOIN mascotas m ON a.id_mascota = m.id_mascota ORDER BY a.fecha_solicitud DESC LIMIT 3)
    UNION
    (SELECT 'donacion' as tipo, CONCAT('Donación de $', monto) as nombre, fecha_donacion as fecha 
     FROM donaciones ORDER BY fecha_donacion DESC LIMIT 3)
    ORDER BY fecha DESC LIMIT 5
");

if ($query_actividad->num_rows > 0) {
    while($fila = $query_actividad->fetch_assoc()) {
        $actividad_reciente[] = $fila;
    }
}

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
                <?php if (count($actividad_reciente) > 0): ?>
                    <ul class="activity-list">
                        <?php foreach ($actividad_reciente as $actividad): ?>
                            <li>
                                <i class="fas fa-<?php echo $actividad['tipo'] == 'usuario' ? 'user-plus' : ($actividad['tipo'] == 'adopcion' ? 'heart' : 'donate'); ?>"></i>
                                <span><?php echo htmlspecialchars($actividad['nombre']); ?></span>
                                <small><?php echo date('d/m/Y H:i', strtotime($actividad['fecha'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No hay actividad reciente.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-bell"></i> Alertas Recientes</h3>
            <ul class="alert-list">
                <?php
                // Alertas dinámicas
                $alertas = [];
                
                // Reportes pendientes
                if ($stats['reportes_activos'] > 0) {
                    $alertas[] = [
                        'icono' => 'fa-exclamation-circle',
                        'texto' => $stats['reportes_activos'] . ' reporte(s) pendiente(s) de revisión',
                        'tiempo' => 'Reciente',
                        'prioridad' => 'alta'
                    ];
                }
                
                // Donaciones pendientes
                if ($stats['donaciones_pendientes'] > 0) {
                    $alertas[] = [
                        'icono' => 'fa-hand-holding-heart',
                        'texto' => $stats['donaciones_pendientes'] . ' donación(es) pendiente(s)',
                        'tiempo' => 'Reciente',
                        'prioridad' => 'media'
                    ];
                }
                
                // Solicitudes de adopción pendientes
                $query_adopciones = $conn->query("SELECT COUNT(*) as total FROM adopciones WHERE estado = 'pendiente'");
                $adopciones_pendientes = $query_adopciones->fetch_assoc()['total'];
                
                if ($adopciones_pendientes > 0) {
                    $alertas[] = [
                        'icono' => 'fa-heart',
                        'texto' => $adopciones_pendientes . ' solicitud(es) de adopción pendiente(s)',
                        'tiempo' => 'Reciente',
                        'prioridad' => 'alta'
                    ];
                }
                
                // Si no hay alertas, mostrar mensaje
                if (empty($alertas)) {
                    echo '<li><i class="fas fa-check-circle"></i><span>No hay alertas pendientes</span></li>';
                } else {
                    foreach ($alertas as $alerta) {
                        echo '<li class="' . $alerta['prioridad'] . '">
                                <i class="fas ' . $alerta['icono'] . '"></i>
                                <span>' . $alerta['texto'] . '</span>
                                <small>' . $alerta['tiempo'] . '</small>
                              </li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="admin-grid">
        <div class="admin-card">
            <h3><i class="fas fa-tasks"></i> Tareas Pendientes</h3>
            <ul class="task-list">
                <li>
                    <input type="checkbox" id="task1" onchange="marcarTarea(1)">
                    <label for="task1">Revisar solicitudes de adopción</label>
                </li>
                <li>
                    <input type="checkbox" id="task2" onchange="marcarTarea(2)">
                    <label for="task2">Actualizar información de mascotas</label>
                </li>
                <li>
                    <input type="checkbox" id="task3" onchange="marcarTarea(3)">
                    <label for="task3">Verificar reportes de situación</label>
                </li>
                <li>
                    <input type="checkbox" id="task4" onchange="marcarTarea(4)">
                    <label for="task4">Contactar donantes recientes</label>
                </li>
            </ul>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-calendar"></i> Próximos Eventos</h3>
            <div class="events-list">
                <?php
                $query_eventos = $conn->query("SELECT * FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 2");
                if ($query_eventos->num_rows > 0) {
                    while($evento = $query_eventos->fetch_assoc()) {
                        echo '<div class="event-item">
                                <div class="event-date">' . date('d M', strtotime($evento['fecha'])) . '</div>
                                <div class="event-info">
                                    <h4>' . htmlspecialchars($evento['titulo']) . '</h4>
                                    <p>' . htmlspecialchars($evento['lugar']) . ' - ' . date('H:i', strtotime($evento['hora_inicio'])) . '</p>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<p>No hay eventos próximos programados.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Gráficos estadísticos -->
    <div class="admin-card">
        <h3><i class="fas fa-chart-bar"></i> Estadísticas Mensuales</h3>
        <div class="charts-container">
            <div class="chart">
                <canvas id="usuariosChart" width="400" height="200"></canvas>
            </div>
            <div class="chart">
                <canvas id="adopcionesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function marcarTarea(id) {
    const checkbox = document.getElementById('task' + id);
    const label = checkbox.nextElementSibling;
    
    if (checkbox.checked) {
        label.style.textDecoration = 'line-through';
        label.style.color = '#888';
    } else {
        label.style.textDecoration = 'none';
        label.style.color = '#333';
    }
}

// Gráfico de usuarios registrados por mes
const usuariosCtx = document.getElementById('usuariosChart').getContext('2d');
const usuariosChart = new Chart(usuariosCtx, {
    type: 'line',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [{
            label: 'Usuarios Registrados',
            data: [12, 19, 15, 25, 22, 30],
            borderColor: '#4aa6b5',
            backgroundColor: 'rgba(74, 166, 181, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Registro de Usuarios por Mes'
            }
        }
    }
});

// Gráfico de adopciones por mes
const adopcionesCtx = document.getElementById('adopcionesChart').getContext('2d');
const adopcionesChart = new Chart(adopcionesCtx, {
    type: 'bar',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [{
            label: 'Adopciones Realizadas',
            data: [5, 8, 6, 12, 10, 15],
            backgroundColor: '#df9d40',
            borderColor: '#aa7a46',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Adopciones por Mes'
            }
        }
    }
});
</script>

<?php
include 'footer_admin.php';
?>