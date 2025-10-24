<?php
/*
================================================================================
ARCHIVO: ver_eventos.php (CON CRUD INTEGRADO)
================================================================================
*/

require_once "php/connect.php";

// ============================================
// PROCESAR EDICIÓN (AJAX)
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'editar') {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $fecha_evento = trim($_POST['fecha_evento']);
    $mensaje = trim($_POST['mensaje']);
    
    $stmt = $mysqli->prepare("UPDATE usuario SET nombre=?, correo=?, fecha_evento=?, mensaje=? WHERE id=?");
    $stmt->bind_param("ssssi", $nombre, $correo, $fecha_evento, $mensaje, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Evento actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}

// ============================================
// PROCESAR ELIMINACIÓN (AJAX)
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    $id = intval($_POST['id']);
    
    $stmt = $mysqli->prepare("DELETE FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Evento eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}

// ============================================
// CONSULTAR TODOS LOS EVENTOS
// ============================================
$sql = "SELECT * FROM usuario ORDER BY fecha_evento ASC, enviado ASC";
$resultado = $mysqli->query($sql);

// Calcular estadísticas
$pendientes = 0;
$enviados = 0;
$eventos = [];
$hoy = date('Y-m-d');

while ($evento = $resultado->fetch_assoc()) {
    $eventos[] = $evento;
    if ($evento['enviado'] == 0) {
        $pendientes++;
    } else {
        $enviados++;
    }
}
$total = count($eventos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos Programados</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        /* Estadísticas */
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card.total { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .stat-card.pendientes { background: #fff3cd; border-left: 4px solid #ffc107; }
        .stat-card.enviados { background: #d4edda; border-left: 4px solid #28a745; }
        
        .stat-card h2 {
            font-size: 36px;
            margin-bottom: 5px;
        }
        
        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        /* Botones de acción */
        .btn-accion {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            margin: 2px;
            transition: all 0.3s;
        }
        
        .btn-editar {
            background: #28a745;
            color: white;
        }
        
        .btn-editar:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-eliminar {
            background: #dc3545;
            color: white;
        }
        
        .btn-eliminar:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }
        
        .close:hover {
            color: #000;
        }
        
        /* Formulario en modal */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        /* Mensajes */
        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
            font-size: 14px;
            animation: slideIn 0.5s;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .mensaje.exito {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        
        /* Badge */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge.enviado {
            background: #d4edda;
            color: #155724;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }
            
            .btn-accion {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📅 Eventos Programados</h1>
        
        <!-- Botones principales -->
        <div style="margin-bottom: 20px;">
            <a href="admin.php" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 10px;">
                ➕ Registrar Nuevo Evento
            </a>
            <a href="enviar_correos.php" target="_blank" style="display: inline-block; background: #28a745; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                ▶️ Ejecutar Envío Manual
            </a>
        </div>
        
        <!-- Mensaje de respuesta -->
        <div id="mensajeRespuesta"></div>
        
        <!-- Estadísticas -->
        <div class="stats">
            <div class="stat-card total">
                <h2><?php echo $total; ?></h2>
                <p>📊 Total Eventos</p>
            </div>
            <div class="stat-card pendientes">
                <h2><?php echo $pendientes; ?></h2>
                <p>⏳ Pendientes</p>
            </div>
            <div class="stat-card enviados">
                <h2><?php echo $enviados; ?></h2>
                <p>✅ Enviados</p>
            </div>
        </div>
        
        <!-- Tabla de eventos -->
        <?php if ($total > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha Evento</th>
                        <th>Correo</th>
                        <th>Tipo de Evento</th>
                        <th>Estado</th>
                        <th style="text-align: center; width: 160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): 
                        $es_hoy = ($evento['fecha_evento'] == $hoy);
                    ?>
                        <tr>
                            <td><?php echo $evento['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($evento['nombre']); ?></strong></td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($evento['fecha_evento'])); ?>
                                <?php if ($es_hoy): ?>
                                    <span style="color: red; font-weight: bold;">⚡ HOY</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($evento['correo']); ?></td>
                            <td><?php echo htmlspecialchars($evento['mensaje']); ?></td>
                            <td>
                                <?php if ($evento['enviado'] == 1): ?>
                                    <span class="badge enviado">✅ Enviado</span>
                                    <br><small style="color: #999; font-size: 11px;">
                                        <?php echo date('d/m/Y H:i', strtotime($evento['fecha_envio'])); ?>
                                    </small>
                                <?php else: ?>
                                    <span class="badge pendiente">⏳ Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn-accion btn-editar" 
                                        onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($evento)); ?>)">
                                    ✏️ Editar
                                </button>
                                <button class="btn-accion btn-eliminar" 
                                        onclick="eliminarEvento(<?php echo $evento['id']; ?>, '<?php echo htmlspecialchars($evento['nombre']); ?>')">
                                    🗑️ Eliminar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <h2>📭</h2>
                <p>No hay eventos registrados</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- MODAL DE EDICIÓN -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 style="margin-bottom: 20px; color: #333;">✏️ Editar Evento</h2>
            
            <form id="formEditar" onsubmit="guardarEdicion(event)">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label>👤 Nombre del Cliente:</label>
                    <input type="text" id="edit_nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label>📧 Correo Electrónico:</label>
                    <input type="email" id="edit_correo" name="correo" required>
                </div>
                
                <div class="form-group">
                    <label>📅 Fecha del Evento:</label>
                    <input type="date" id="edit_fecha" name="fecha_evento" required>
                </div>
                
                <div class="form-group">
                    <label>📝 Tipo de Evento:</label>
                    <textarea id="edit_mensaje" name="mensaje" required></textarea>
                </div>
                
                <button type="submit" class="btn-submit">💾 Guardar Cambios</button>
            </form>
        </div>
    </div>
    
    <script>
        // ============================================
        // ABRIR MODAL DE EDICIÓN
        // ============================================
        function abrirModalEditar(evento) {
            document.getElementById('edit_id').value = evento.id;
            document.getElementById('edit_nombre').value = evento.nombre;
            document.getElementById('edit_correo').value = evento.correo;
            document.getElementById('edit_fecha').value = evento.fecha_evento;
            document.getElementById('edit_mensaje').value = evento.mensaje;
            
            document.getElementById('modalEditar').style.display = 'block';
        }
        
        // ============================================
        // CERRAR MODAL
        // ============================================
        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target == modal) {
                cerrarModal();
            }
        }
        
        // ============================================
        // GUARDAR EDICIÓN (AJAX)
        // ============================================
        function guardarEdicion(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('formEditar'));
            formData.append('action', 'editar');
            
            fetch('ver_eventos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje(data.message, 'exito');
                    cerrarModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarMensaje(data.message, 'error');
                }
            })
            .catch(error => {
                mostrarMensaje('Error de conexión', 'error');
            });
        }
        
        // ============================================
        // ELIMINAR EVENTO
        // ============================================
        function eliminarEvento(id, nombre) {
            if (!confirm(`⚠️ ¿Estás seguro de eliminar este evento?\n\nCliente: ${nombre}\n\n⚠️ Esta acción NO se puede deshacer.`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id', id);
            
            fetch('ver_eventos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje(data.message, 'exito');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarMensaje(data.message, 'error');
                }
            })
            .catch(error => {
                mostrarMensaje('Error de conexión', 'error');
            });
        }
        
        // ============================================
        // MOSTRAR MENSAJES
        // ============================================
        function mostrarMensaje(texto, tipo) {
            const div = document.getElementById('mensajeRespuesta');
            div.innerHTML = `<div class="mensaje ${tipo}">${tipo === 'exito' ? '✅' : '⚠️'} ${texto}</div>`;
            
            setTimeout(() => {
                div.innerHTML = '';
            }, 5000);
        }
    </script>
</body>
</html>

<?php $mysqli->close(); ?>