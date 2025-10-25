<?php
// ========================================
// ARCHIVO 3: ver_eventos.php CON CRUD
// Ver, Editar y Eliminar eventos
// ========================================

// Conectar a BD
require_once "php/connect.php";

$mensaje = "";

// ============================================
// ELIMINAR EVENTO
// ============================================
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $mysqli->query("DELETE FROM usuario WHERE id = $id");
    $mensaje = "✅ Evento eliminado correctamente";
    header("Location: ver_eventos.php"); // Recargar página
    exit;
}

// ============================================
// ACTUALIZAR EVENTO
// ============================================
if (isset($_POST['actualizar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha'];
    $correo = $_POST['correo'];
    $tipo = $_POST['tipo'];
    
    $sql = "UPDATE usuario SET 
            nombre = '$nombre', 
            fecha_evento = '$fecha', 
            correo = '$correo', 
            mensaje = '$tipo'
            WHERE id = $id";
    
    if ($mysqli->query($sql)) {
        $mensaje = "✅ Evento actualizado correctamente";
    } else {
        $mensaje = "❌ Error al actualizar";
    }
}

// ============================================
// OBTENER TODOS LOS EVENTOS
// ============================================
$sql = "SELECT * FROM usuario ORDER BY fecha_evento";
$resultado = $mysqli->query($sql);

// Si se está editando un evento
$editando = false;
$evento_editar = null;

if (isset($_GET['editar'])) {
    $editando = true;
    $id_editar = $_GET['editar'];
    $sql_editar = "SELECT * FROM usuario WHERE id = $id_editar";
    $resultado_editar = $mysqli->query($sql_editar);
    $evento_editar = $resultado_editar->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eventos Registrados</title>
    <style>
        body {
            font-family: Arial;
            background: #667eea;
            padding: 30px;
        }
        .container {
            background: white;
            padding: 30px;
            max-width: 1100px;
            margin: 0 auto;
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        .enviado {
            color: green;
            font-weight: bold;
        }
        .pendiente {
            color: orange;
            font-weight: bold;
        }
        .hoy {
            background: #ffe0e0;
        }
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
        .btn-nuevo {
            background: #667eea;
            color: white;
        }
        .btn-ejecutar {
            background: #28a745;
            color: white;
        }
        .btn-editar {
            background: #ffc107;
            color: black;
        }
        .btn-eliminar {
            background: #dc3545;
            color: white;
        }
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
        
        /* Confirmación de eliminación */
        .btn-eliminar:hover {
            background: #a71d2a;
        }
    </style>
    
    <script>
        // Confirmar antes de eliminar
        function confirmarEliminar(nombre) {
            return confirm('¿Estás seguro de eliminar el evento de ' + nombre + '?');
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>📋 Gestión de Eventos</h2>
        
        <div>
            <a href="admin.php" class="btn btn-nuevo">➕ Nuevo Evento</a>
            <a href="enviar_correos.php" target="_blank" class="btn btn-ejecutar">▶️ Enviar Ahora</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <!-- ============================================
             FORMULARIO DE EDICIÓN (aparece cuando se da clic en Editar)
             ============================================ -->
        <?php if ($editando && $evento_editar): ?>
            <div class="form-editar">
                <h3>✏️ Editando evento #<?php echo $evento_editar['id']; ?></h3>
                
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $evento_editar['id']; ?>">
                    
                    <label>Nombre del cliente:</label>
                    <input type="text" name="nombre" value="<?php echo $evento_editar['nombre']; ?>" required>
                    
                    <label>Fecha del evento:</label>
                    <input type="date" name="fecha" value="<?php echo $evento_editar['fecha_evento']; ?>" required>
                    
                    <label>Correo electrónico:</label>
                    <input type="email" name="correo" value="<?php echo $evento_editar['correo']; ?>" required>
                    
                    <label>Tipo de evento:</label>
                    <input type="text" name="tipo" value="<?php echo $evento_editar['mensaje']; ?>" required>
                    
                    <div class="form-actions">
                        <a href="ver_eventos.php" class="btn btn-cancelar">❌ Cancelar</a>
                        <button type="submit" name="actualizar" class="btn btn-guardar">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- ============================================
             TABLA DE EVENTOS
             ============================================ -->
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
                $hoy = date('Y-m-d');
                $resultado = $mysqli->query($sql); // Volver a consultar
                while ($evento = $resultado->fetch_assoc()): 
                    $es_hoy = ($evento['fecha_evento'] == $hoy);
                    $es_editando = ($editando && $evento['id'] == $evento_editar['id']);
                ?>
                    
                    <tr class="<?php echo $es_hoy ? 'hoy' : ''; ?> <?php echo $es_editando ? 'editando' : ''; ?>">
                        <td><?php echo $evento['id']; ?></td>
                        <td><strong><?php echo $evento['nombre']; ?></strong></td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($evento['fecha_evento'])); ?>
                            <?php if ($es_hoy): ?>
                                <strong style="color: red;">⚡ HOY</strong>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $evento['correo']; ?></td>
                        <td><?php echo $evento['mensaje']; ?></td>
                        <td>
                            <?php if ($evento['enviado'] == 1): ?>
                                <span class="enviado">✅ Enviado</span>
                                <br>
                                <small style="color: #666;"><?php echo date('d/m/Y H:i', strtotime($evento['fecha_envio'])); ?></small>
                            <?php else: ?>
                                <span class="pendiente">⏳ Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Botón EDITAR -->
                            <a href="?editar=<?php echo $evento['id']; ?>" class="btn btn-editar">
                                ✏️ Editar
                            </a>
                            
                            <!-- Botón ELIMINAR -->
                            <a href="?eliminar=<?php echo $evento['id']; ?>" 
                               class="btn btn-eliminar"
                               onclick="return confirmarEliminar('<?php echo $evento['nombre']; ?>')">
                                🗑️ Eliminar
                            </a>
                        </td>
                    </tr>
                    
                <?php endwhile; ?>
            </table>
            
            <!-- Estadísticas -->
            <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
                <?php
                // Calcular estadísticas
                $resultado_stats = $mysqli->query("SELECT * FROM usuario");
                $total = $resultado_stats->num_rows;
                $pendientes = 0;
                $enviados = 0;
                
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
            <div style="text-align: center; padding: 40px; color: #999;">
                <h3>📭 No hay eventos registrados</h3>
                <p>
                    <a href="admin.php" class="btn btn-nuevo">Registra tu primer evento</a>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Información del sistema -->
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

<?php $mysqli->close(); ?>