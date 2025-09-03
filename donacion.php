<?php
session_start();
include 'conexion.php';

// Procesar donaciones
$mensaje = "";
$tipo_mensaje = "";

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

<?php
include 'includes/footer.php';
?>