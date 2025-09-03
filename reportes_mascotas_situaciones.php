<?php
session_start();
include 'conexion.php';

// Procesar reportes
$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['reportar_mascota'])) {
        $tipo_reporte = $_POST['tipo_reporte'];
        $nombre_mascota = filter_input(INPUT_POST, 'nombre_mascota', FILTER_SANITIZE_STRING);
        $especie_raza = filter_input(INPUT_POST, 'especie_raza', FILTER_SANITIZE_STRING);
        $lugar = filter_input(INPUT_POST, 'lugar', FILTER_SANITIZE_STRING);
        $fecha_avistamiento = $_POST['fecha_avistamiento'];
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        
        // Validar campos
        if (empty($especie_raza) || empty($lugar) || empty($fecha_avistamiento) || empty($descripcion)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        } else {
            // Guardar reporte
            $sql = "INSERT INTO reportes_mascotas (tipo_reporte, nombre_mascota, especie_raza_color, lugar, fecha_avistamiento, descripcion, id_usuario) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $tipo_reporte, $nombre_mascota, $especie_raza, $lugar, $fecha_avistamiento, $descripcion, $id_usuario);
            
            if ($stmt->execute()) {
                $mensaje = "Reporte enviado correctamente. Gracias por tu ayuda.";
                $tipo_mensaje = "exito";
                $_POST = array();
            } else {
                $mensaje = "Error al enviar el reporte: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
    
    if (isset($_POST['reportar_situacion'])) {
        $tipo_situacion = $_POST['tipo_situacion'];
        $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
        $fecha_situacion = $_POST['fecha_situacion'];
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        
        // Validar campos
        if (empty($tipo_situacion) || empty($direccion) || empty($fecha_situacion) || empty($descripcion)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        } else {
            // Guardar situación
            $sql = "INSERT INTO situaciones_precarias (tipo_situacion, direccion, fecha_situacion, descripcion, id_usuario) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $tipo_situacion, $direccion, $fecha_situacion, $descripcion, $id_usuario);
            
            if ($stmt->execute()) {
                $mensaje = "Situación reportada correctamente. Tomaremos medidas.";
                $tipo_mensaje = "exito";
                $_POST = array();
            } else {
                $mensaje = "Error al reportar la situación: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
}

$page_title = "FreePets - Reportar Situaciones";
include 'includes/header.php';
?>

<section class="content reportes">
    <h2 class="title">Reportar Situaciones</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="reportes-grid">
        <!-- Reporte de mascota -->
        <div class="reporte-card">
            <h3><i class="fas fa-paw"></i> Reportar Mascota</h3>
            <form method="POST">
                <input type="hidden" name="reportar_mascota" value="1">
                
                <div class="form-group">
                    <label for="tipo_reporte">Tipo de reporte:*</label>
                    <select id="tipo_reporte" name="tipo_reporte" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="Perdida">Mascota Perdida</option>
                        <option value="Encontrada">Mascota Encontrada</option>
                        <option value="En adopcion">En Adopción</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nombre_mascota">Nombre de la mascota (opcional):</label>
                    <input type="text" id="nombre_mascota" name="nombre_mascota">
                </div>
                
                <div class="form-group">
                    <label for="especie_raza">Especie/Raza/Color:*</label>
                    <input type="text" id="especie_raza" name="especie_raza" required 
                           placeholder="Ej: Perro labrador amarillo, Gato siamés, etc.">
                </div>
                
                <div class="form-group">
                    <label for="lugar">Lugar del reporte:*</label>
                    <input type="text" id="lugar" name="lugar" required 
                           placeholder="Dirección exacta o punto de referencia">
                </div>
                
                <div class="form-group">
                    <label for="fecha_avistamiento">Fecha del avistamiento:*</label>
                    <input type="date" id="fecha_avistamiento" name="fecha_avistamiento" required 
                           max="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción detallada:*</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required 
                              placeholder="Describa la situación, condición de la mascota, comportamiento, etc."></textarea>
                </div>
                
                <button type="submit" class="btn">Enviar Reporte</button>
            </form>
        </div>

        <!-- Reporte de situación precaria -->
        <div class="reporte-card">
            <h3><i class="fas fa-exclamation-triangle"></i> Reportar Situación Precaria</h3>
            <form method="POST">
                <input type="hidden" name="reportar_situacion" value="1">
                
                <div class="form-group">
                    <label for="tipo_situacion">Tipo de situación:*</label>
                    <select id="tipo_situacion" name="tipo_situacion" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="Maltrato">Maltrato animal</option>
                        <option value="Abandono">Abandono</option>
                        <option value="Falta de alimento">Falta de alimento/agua</option>
                        <option value="Condiciones precarias">Condiciones precarias</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección exacta:*</label>
                    <input type="text" id="direccion" name="direccion" required 
                           placeholder="Dirección donde ocurre la situación">
                </div>
                
                <div class="form-group">
                    <label for="fecha_situacion">Fecha de la situación:*</label>
                    <input type="date" id="fecha_situacion" name="fecha_situacion" required 
                           max="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="descripcion_situacion">Descripción detallada:*</label>
                    <textarea id="descripcion_situacion" name="descripcion" rows="4" required 
                              placeholder="Describa la situación en detalle, número de animales afectados, condiciones, etc."></textarea>
                </div>
                
                <button type="submit" class="btn">Reportar Situación</button>
            </form>
        </div>
    </div>
    
    <div class="info-importante">
        <h4><i class="fas fa-info-circle"></i> Información Importante</h4>
        <p>Todos los reportes son verificados por nuestro equipo. En caso de emergencia, contacta directamente a las autoridades locales.</p>
        <p>Tu reporte puede salvar vidas animales. ¡Gracias por tu colaboración!</p>
    </div>
</section>

<?php
include 'includes/footer.php';
?>