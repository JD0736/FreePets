<?php
session_start();
include 'conexion.php';

// Verificar si el usuario es administrador
$es_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';

// Procesar donaciones
$mensaje = "";
$tipo_mensaje = "";

// Procesar administración de donaciones
if ($es_admin) {
    // Eliminar donación
    if (isset($_GET['eliminar_donacion'])) {
        $id_donacion = $_GET['eliminar_donacion'];
        
        $sql = "DELETE FROM donaciones WHERE id_donacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_donacion);
        
        if ($stmt->execute()) {
            $mensaje = "Donación eliminada correctamente.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al eliminar la donación: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
    
    // Cambiar estado de donación
    if (isset($_GET['cambiar_estado'])) {
        $id_donacion = $_GET['cambiar_estado'];
        $nuevo_estado = $_GET['estado'];
        
        $sql = "UPDATE donaciones SET estado = ? WHERE id_donacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id_donacion);
        
        if ($stmt->execute()) {
            $mensaje = "Estado de donación actualizado correctamente.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al actualizar el estado: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['realizar_donacion'])) {
    $tipo_donacion = $_POST['tipo_donacion'];
    $cantidad = $_POST['cantidad'];
    $tipo_articulo = isset($_POST['tipo_articulo']) ? filter_input(INPUT_POST, 'tipo_articulo', FILTER_SANITIZE_STRING) : '';
    $monto = isset($_POST['monto']) ? $_POST['monto'] : 0;
    $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    
    // Validar campos según el tipo de donación
    if ($tipo_donacion === 'juguetes' || $tipo_donacion === 'comida') {
        if (empty($tipo_articulo) || empty($cantidad)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        }
    } elseif ($tipo_donacion === 'dinero') {
        if (empty($monto) || $monto <= 0) {
            $mensaje = "Por favor, ingrese un monto válido.";
            $tipo_mensaje = "error";
        }
    }
    
    if (empty($mensaje)) {
        // Guardar donación
        $sql = "INSERT INTO donaciones (tipo_donacion, tipo_articulo, cantidad, monto, id_usuario) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        // Ajustar valores según el tipo de donación
        if ($tipo_donacion === 'dinero') {
            $cantidad = 1; // Para donaciones de dinero, cantidad es 1
            $tipo_articulo = 'Donación monetaria';
        } else {
            $monto = 0; // Para donaciones en especie, monto es 0
        }
        
        $stmt->bind_param("ssidi", $tipo_donacion, $tipo_articulo, $cantidad, $monto, $id_usuario);
        
        if ($stmt->execute()) {
            $mensaje = "¡Gracias por tu donación! Tu apoyo hace la diferencia.";
            $tipo_mensaje = "exito";
            $_POST = array();
        } else {
            $mensaje = "Error al procesar la donación: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
}

// Obtener donaciones para administración
$donaciones = [];
if ($es_admin) {
    $sql_donaciones = "SELECT d.*, u.nombre as donante 
                      FROM donaciones d 
                      LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario 
                      ORDER BY d.fecha_donacion DESC";
    $resultado_donaciones = $conn->query($sql_donaciones);
    if ($resultado_donaciones->num_rows > 0) {
        while($fila = $resultado_donaciones->fetch_assoc()) {
            $donaciones[] = $fila;
        }
    }
}

$page_title = "FreePets - Donaciones";
include 'includes/header.php';
?>

<section class="content donaciones">
    <h2 class="title">Hacer una Donación</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="donacion-intro">
        <p>Tu donación ayuda a mantener nuestro refugio y a proporcionar cuidado a los animales necesitados. 
           ¡Cada contribución cuenta!</p>
    </div>
    
    <!-- Panel de administración de donaciones -->
    <?php if ($es_admin): ?>
    <div class="admin-panel">
        <h3>Administrar Donaciones</h3>
        
        <?php if (count($donaciones) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donante</th>
                            <th>Tipo</th>
                            <th>Artículo/Cantidad</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donaciones as $donacion): ?>
                            <tr>
                                <td><?php echo $donacion['id_donacion']; ?></td>
                                <td><?php echo $donacion['donante'] ? htmlspecialchars($donacion['donante']) : 'Anónimo'; ?></td>
                                <td><?php echo ucfirst($donacion['tipo_donacion']); ?></td>
                                <td>
                                    <?php if ($donacion['tipo_donacion'] === 'dinero'): ?>
                                        Donación monetaria
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($donacion['tipo_articulo']); ?> (<?php echo $donacion['cantidad']; ?>)
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($donacion['monto'] > 0): ?>
                                        $<?php echo number_format($donacion['monto'], 2); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($donacion['fecha_donacion'])); ?></td>
                                <td>
                                    <span class="estado-badge estado-<?php echo $donacion['estado']; ?>">
                                        <?php echo ucfirst($donacion['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <?php if ($donacion['estado'] == 'pendiente'): ?>
                                            <button class="admin-btn" onclick="cambiarEstado(<?php echo $donacion['id_donacion']; ?>, 'aprobado')">Aprobar</button>
                                        <?php endif; ?>
                                        <button class="admin-btn delete" onclick="eliminarDonacion(<?php echo $donacion['id_donacion']; ?>)">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay donaciones registradas.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="tipos-donacion">
        <!-- Donación de juguetes -->
        <div class="donacion-card">
            <h3><i class="fas fa-baseball-ball"></i> Donar Juguetes</h3>
            <form method="POST">
                <input type="hidden" name="tipo_donacion" value="juguetes">
                <input type="hidden" name="realizar_donacion" value="1">
                
                <div class="form-group">
                    <label for="tipo_juguete">Tipo de juguete:*</label>
                    <select id="tipo_juguete" name="tipo_articulo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="Pelotas">Pelotas</option>
                        <option value="Mordedores">Mordedores</option>
                        <option value="Peluches">Peluches</option>
                        <option value="Rascadores">Rascadores (para gatos)</option>
                        <option value="Otro">Otro tipo</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cantidad_juguetes">Cantidad:*</label>
                    <input type="number" id="cantidad_juguetes" name="cantidad" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="condicion_juguetes">Condición:</label>
                    <select id="condicion_juguetes" name="condicion">
                        <option value="nuevo">Nuevo</option>
                        <option value="usado_buen_estado">Usado - Buen estado</option>
                        <option value="usado_regular">Usado - Estado regular</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-donacion">Donar Juguetes</button>
            </form>
        </div>

        <!-- Donación de comida -->
        <div class="donacion-card">
            <h3><i class="fas fa-utensils"></i> Donar Comida</h3>
            <form method="POST">
                <input type="hidden" name="tipo_donacion" value="comida">
                <input type="hidden" name="realizar_donacion" value="1">
                
                <div class="form-group">
                    <label for="tipo_comida">Tipo de alimento:*</label>
                    <select id="tipo_comida" name="tipo_articulo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="Concentrado para perro adulto">Concentrado para perro adulto</option>
                        <option value="Concentrado para cachorro">Concentrado para cachorro</option>
                        <option value="Concentrado para gato adulto">Concentrado para gato adulto</option>
                        <option value="Concentrado para gatito">Concentrado para gatito</option>
                        <option value="Comida húmeda">Comida húmeda</option>
                        <option value="Premios y golosinas">Premios y golosinas</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cantidad_comida">Cantidad (kg o unidades):*</label>
                    <input type="number" id="cantidad_comida" name="cantidad" min="1" step="0.5" required>
                </div>
                
                <div class="form-group">
                    <label for="fecha_vencimiento">Fecha de vencimiento (si aplica):</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <button type="submit" class="btn-donacion">Donar Comida</button>
            </form>
        </div>

        <!-- Donación de dinero -->
        <div class="donacion-card">
            <h3><i class="fas fa-hand-holding-heart"></i> Donación Monetaria</h3>
            <form method="POST">
                <input type="hidden" name="tipo_donacion" value="dinero">
                <input type="hidden" name="realizar_donacion" value="1">
                
                <div class="form-group">
                    <label for="monto_donacion">Monto a donar (COP):*</label>
                    <input type="number" id="monto_donacion" name="monto" min="1000" step="1000" required 
                           placeholder="Ej: 50000">
                </div>
                
                <div class="form-group">
                    <label for="frecuencia_donacion">Frecuencia:</label>
                    <select id="frecuencia_donacion" name="frecuencia">
                        <option value="unica">Donación única</option>
                        <option value="mensual">Donación mensual</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="metodo_pago">Método de pago:*</label>
                    <select id="metodo_pago" name="metodo_pago" required>
                        <option value="">Seleccionar método</option>
                        <option value="tarjeta">Tarjeta crédito/débito</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="efectivo">Efectivo</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-donacion">Donar Ahora</button>
            </form>
        </div>
    </div>
    
    <div class="info-donaciones">
        <h3><i class="fas fa-info-circle"></i> Información sobre Donaciones</h3>
        <div class="info-content">
            <p><strong>Puntos de entrega:</strong> Puedes traer tus donaciones directamente a nuestro refugio en 
            [Dirección del refugio] de lunes a viernes de 9:00 am a 5:00 pm.</p>
            
            <p><strong>Donaciones monetarias:</strong> Las donaciones en efectivo se utilizan para:
                <ul>
                    <li>Alimento de calidad para los animales</li>
                    <li>Gastos veterinarios y medicinas</li>
                    <li>Mantenimiento de instalaciones</li>
                    <li>Programas de esterilización</li>
                </ul>
            </p>
            
            <p><strong>Contacto:</strong> Para donaciones grandes o coordinación especial, contáctanos al 
            <strong>300 762 09 48</strong> o al email <strong>donaciones@freepets.com</strong></p>
        </div>
    </div>
</section>

<script>
function eliminarDonacion(id) {
    if (confirm('¿Está seguro de que desea eliminar esta donación? Esta acción no se puede deshacer.')) {
        window.location.href = 'donacion.php?eliminar_donacion=' + id;
    }
}

function cambiarEstado(id, estado) {
    if (confirm('¿Está seguro de que desea cambiar el estado de esta donación?')) {
        window.location.href = 'donacion.php?cambiar_estado=' + id + '&estado=' + estado;
    }
}
</script>

<?php
include 'includes/footer.php';
?>