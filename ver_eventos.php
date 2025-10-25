<?php
// Archivo: ver_eventos.php
// Propósito: Mostrar, editar y eliminar eventos; interfaz administrativa

// ========================================
// Conexión a la base de datos
// ========================================
// 'require_once' incluye el archivo 'connect.php' que contiene la conexión $mysqli
require_once "php/connect.php";

// Variable para mostrar mensajes en la interfaz (ej. éxito/error)
$mensaje = "";

// ============================================
// ELIMINAR EVENTO
// ============================================
// Si se recibe el parámetro GET 'eliminar', se borra el registro con ese id
if (isset($_GET['eliminar'])) {
    // Obtener el id pasado por GET
    $id = $_GET['eliminar'];
    // Ejecutar la consulta DELETE sobre la tabla 'usuario'
    $mysqli->query("DELETE FROM usuario WHERE id = $id");
    // Preparar mensaje de éxito para mostrar en pantalla
    $mensaje = "✅ Evento eliminado correctamente";
    // Redirigir a la misma página para refrescar la lista y evitar resubmisiones
    header("Location: ver_eventos.php");
    // Terminar la ejecución del script después de la redirección
    exit;
}

// ============================================
// ACTUALIZAR EVENTO
// ============================================
// Si se envió el formulario de actualización (botón 'actualizar')
if (isset($_POST['actualizar'])) {
    // Recuperar campos enviados por POST
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha'];
    $correo = $_POST['correo'];
    $tipo = $_POST['tipo'];
    
    // Construir la consulta UPDATE para modificar el registro con el id dado
    $sql = "UPDATE usuario SET 
            nombre = '$nombre', 
            fecha_evento = '$fecha', 
            correo = '$correo', 
            mensaje = '$tipo'
            WHERE id = $id";
    
    // Ejecutar la consulta y asignar mensaje según resultado
    if ($mysqli->query($sql)) {
        $mensaje = "✅ Evento actualizado correctamente";
    } else {
        $mensaje = "❌ Error al actualizar";
    }
}

// ============================================
// OBTENER TODOS LOS EVENTOS
// ============================================
// Consulta principal para listar eventos ordenados por fecha
$sql = "SELECT * FROM usuario ORDER BY fecha_evento";
$resultado = $mysqli->query($sql);

// Variables para control del estado de edición
$editando = false;         // Indica si estamos en modo edición
$evento_editar = null;     // Contendrá los datos del evento a editar

// Si se recibe el parámetro GET 'editar', cargar el evento para editar
if (isset($_GET['editar'])) {
    $editando = true;                      // Activar modo edición
    $id_editar = $_GET['editar'];          // Obtener id a editar
    $sql_editar = "SELECT * FROM usuario WHERE id = $id_editar"; // Consulta específica
    $resultado_editar = $mysqli->query($sql_editar);              // Ejecutar consulta
    $evento_editar = $resultado_editar->fetch_assoc();            // Obtener fila como array asociativo
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos básicos -->
    <meta charset="UTF-8">
    <title>Eventos Registrados</title>
    
    <!-- Estilos internos para la página -->
    <style>
        /* Estilo del cuerpo: fuente, color de fondo y padding */
        body {
            font-family: Arial;
            background: #667eea;
            padding: 30px;
        }
        /* Contenedor central que emula una tarjeta */
        .container {
            background: white;
            padding: 30px;
            max-width: 1100px;
            margin: 0 auto;
            border-radius: 10px;
        }
        /* Estilos básicos de la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        /* Encabezados de la tabla: fondo y color */
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        /* Celdas de la tabla: padding y separador inferior */
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        /* Clase para marcar como enviado */
        .enviado {
            color: green;
            font-weight: bold;
        }
        /* Clase para marcar como pendiente */
        .pendiente {
            color: orange;
            font-weight: bold;
        }
        /* Resaltar fila si el evento es hoy */
        .hoy {
            background: #ffe0e0;
        }
        /* Estilos comunes a botones (enlaces estilizados) */
        .btn {
            display: inline-block;
            margin: 5px 2px;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            border: none;
        }
        /* Botón para crear nuevo evento */
        .btn-nuevo {
            background: #667eea;
            color: white;
        }
        /* Botón para ejecutar envío manual */
        .btn-ejecutar {
            background: #28a745;
            color: white;
        }
        /* Botón de editar (amarillo) */
        .btn-editar {
            background: #ffc107;
            color: black;
        }
        /* Botón de eliminar (rojo) */
        .btn-eliminar {
            background: #dc3545;
            color: white;
        }
        /* Botones de guardar y cancelar dentro del formulario */
        .btn-guardar {
            background: #28a745;
            color: white;
            padding: 10px 20px;
        }
        .btn-cancelar {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
        }
        /* Estilo del contenedor de mensajes */
        .mensaje {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            text-align: center;
            background: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        
        /* FORMULARIO DE EDICIÓN */
        .form-editar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 3px solid #ffc107;
        }
        .form-editar h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .form-editar label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #555;
        }
        .form-editar input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-editar input:focus {
            border-color: #ffc107;
            outline: none;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        /* Confirmación de eliminación: cambio de color al pasar el mouse */
        .btn-eliminar:hover {
            background: #a71d2a;
        }
    </style>
    
    <!-- Pequeño script para confirmar eliminación en cliente -->
    <script>
        // Mostrar un confirm() antes de eliminar y devolver true/false según elección
        function confirmarEliminar(nombre) {
            return confirm('¿Estás seguro de eliminar el evento de ' + nombre + '?');
        }
    </script>
</head>
<body>
    <!-- Contenedor principal -->
    <div class="container">
        <!-- Título de la página -->
        <h2>📋 Gestión de Eventos</h2>
        
        <!-- Acciones rápidas: crear nuevo y enviar ahora -->
        <div>
            <a href="admin.php" class="btn btn-nuevo">➕ Nuevo Evento</a>
            <a href="enviar_correos.php" target="_blank" class="btn btn-ejecutar">▶️ Enviar Ahora</a>
        </div>
        
        <!-- Si hay un mensaje (operación previa), mostrarlo -->
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <!-- FORMULARIO DE EDICIÓN: sólo se muestra si estamos en modo edición -->
        <?php if ($editando && $evento_editar): ?>
            <div class="form-editar">
                <!-- Mostrar el id que se está editando -->
                <h3>✏️ Editando evento #<?php echo $evento_editar['id']; ?></h3>
                
                <!-- Formulario que envía por POST los cambios -->
                <form method="post">
                    <!-- Campo oculto con el id del registro -->
                    <input type="hidden" name="id" value="<?php echo $evento_editar['id']; ?>">
                    
                    <label>Nombre del cliente:</label>
                    <input type="text" name="nombre" value="<?php echo $evento_editar['nombre']; ?>" required>
                    
                    <label>Fecha del evento:</label>
                    <input type="date" name="fecha" value="<?php echo $evento_editar['fecha_evento']; ?>" required>
                    
                    <label>Correo electrónico:</label>
                    <input type="email" name="correo" value="<?php echo $evento_editar['correo']; ?>" required>
                    
                    <label>Tipo de evento:</label>
                    <input type="text" name="tipo" value="<?php echo $evento_editar['mensaje']; ?>" required>
                    
                    <!-- Acciones: cancelar (vuelve a la lista) o guardar (envía POST) -->
                    <div class="form-actions">
                        <a href="ver_eventos.php" class="btn btn-cancelar">❌ Cancelar</a>
                        <button type="submit" name="actualizar" class="btn btn-guardar">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- TABLA DE EVENTOS: si la consulta devolvió filas -->
        <?php if ($resultado->num_rows > 0): ?>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Correo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                
                <?php 
                // Fecha actual en formato Y-m-d para comparar
                $hoy = date('Y-m-d');
                // Volver a ejecutar la consulta para asegurarse de tener el recurso actualizado
                $resultado = $mysqli->query($sql);
                // Iterar cada fila (evento) obtenida
                while ($evento = $resultado->fetch_assoc()): 
                    // Marcar si el evento ocurre hoy
                    $es_hoy = ($evento['fecha_evento'] == $hoy);
                    // Marcar si la fila actual es la que se está editando
                    $es_editando = ($editando && $evento['id'] == $evento_editar['id']);
                ?>
                    
                    <!-- Fila del evento; agregar clases condicionales -->
                    <tr class="<?php echo $es_hoy ? 'hoy' : ''; ?> <?php echo $es_editando ? 'editando' : ''; ?>">
                        <!-- ID -->
                        <td><?php echo $evento['id']; ?></td>
                        <!-- Nombre del cliente en negrita -->
                        <td><strong><?php echo $evento['nombre']; ?></strong></td>
                        <!-- Fecha formateada a d/m/Y y etiqueta 'HOY' si aplica -->
                        <td>
                            <?php echo date('d/m/Y', strtotime($evento['fecha_evento'])); ?>
                            <?php if ($es_hoy): ?>
                                <strong style="color: red;">⚡ HOY</strong>
                            <?php endif; ?>
                        </td>
                        <!-- Correo -->
                        <td><?php echo $evento['correo']; ?></td>
                        <!-- Tipo / mensaje -->
                        <td><?php echo $evento['mensaje']; ?></td>
                        <!-- Estado: enviado o pendiente -->
                        <td>
                            <?php if ($evento['enviado'] == 1): ?>
                                <span class="enviado">✅ Enviado</span>
                                <br>
                                <!-- Mostrar fecha/hora de envío si existe -->
                                <small style="color: #666;"><?php echo date('d/m/Y H:i', strtotime($evento['fecha_envio'])); ?></small>
                            <?php else: ?>
                                <span class="pendiente">⏳ Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <!-- Acciones: editar y eliminar -->
                        <td>
                            <!-- Enlace para editar pasando ?editar=id -->
                            <a href="?editar=<?php echo $evento['id']; ?>" class="btn btn-editar">
                                ✏️ Editar
                            </a>
                            
                            <!-- Enlace para eliminar pasando ?eliminar=id; onclick pide confirmación -->
                            <a href="?eliminar=<?php echo $evento['id']; ?>" 
                               class="btn btn-eliminar"
                               onclick="return confirmarEliminar('<?php echo $evento['nombre']; ?>')">
                                🗑️ Eliminar
                            </a>
                        </td>
                    </tr>
                    
                <?php endwhile; ?>
            </table>
            
            <!-- Estadísticas: total, pendientes y enviados -->
            <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
                <?php
                // Calcular estadísticas básicas consultando todos los registros
                $resultado_stats = $mysqli->query("SELECT * FROM usuario");
                $total = $resultado_stats->num_rows; // total de filas
                $pendientes = 0; // contador pendientes
                $enviados = 0;   // contador enviados
                
                // Recorrer cada fila para sumar los contadores
                while ($e = $resultado_stats->fetch_assoc()) {
                    if ($e['enviado'] == 1) {
                        $enviados++;
                    } else {
                        $pendientes++;
                    }
                }
                ?>
                <strong>📊 Estadísticas:</strong> 
                Total: <?php echo $total; ?> | 
                ⏳ Pendientes: <?php echo $pendientes; ?> | 
                ✅ Enviados: <?php echo $enviados; ?>
            </div>
            
        <?php else: ?>
            <!-- Mensaje cuando no hay eventos -->
            <div style="text-align: center; padding: 40px; color: #999;">
                <h3>📭 No hay eventos registrados</h3>
                <p>
                    <a href="admin.php" class="btn btn-nuevo">Registra tu primer evento</a>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Información de ayuda sobre el sistema -->
        <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 5px; border-left: 4px solid #2196f3;">
            <h4 style="margin-top: 0;">ℹ️ Cómo funciona el sistema:</h4>
            <ol style="line-height: 1.8;">
                <li><strong>Registrar:</strong> Crea eventos desde admin.php</li>
                <li><strong>Editar:</strong> Haz clic en "✏️ Editar" para modificar un evento</li>
                <li><strong>Eliminar:</strong> Haz clic en "🗑️ Eliminar" para borrar un evento</li>
                <li><strong>Automático:</strong> El sistema envía correos automáticamente el día del evento a las 8 AM</li>
                <li><strong>Manual:</strong> Puedes enviar correos manualmente con el botón "▶️ Enviar Ahora"</li>
            </ol>
            <p style="margin: 10px 0 0 0; color: #666;">
                <strong>Nota:</strong> Los eventos marcados como "✅ Enviado" ya no se pueden editar automáticamente, 
                pero puedes modificarlos y el correo se enviará nuevamente si cambias el estado.
            </p>
        </div>
    </div>
</body>
</html>

<?php
// Cerrar la conexión mysqli al final del script
$mysqli->close();
?>