<?php
/*
================================================================================
ARCHIVO 3: ver_eventos.php
================================================================================
PROPÓSITO:
    Panel para visualizar todos los eventos registrados en el sistema.
    
QUÉ HACE:
    1. Consulta TODOS los eventos de la base de datos
    2. Muestra estadísticas (cuántos pendientes, cuántos enviados)
    3. Muestra una tabla con todos los eventos ordenados por fecha
    4. Indica visualmente cuáles están pendientes y cuáles ya se enviaron
    5. Resalta los eventos que son para HOY
    
CUÁNDO SE USA:
    - Para revisar qué eventos están programados
    - Para verificar que un evento se registró correctamente
    - Para ver cuáles correos ya se enviaron
    - Para hacer seguimiento del sistema
    
IMPORTANTE:
    - Este archivo es solo de CONSULTA (no modifica nada)
    - Se accede manualmente desde el navegador
    - Es útil para el administrador
================================================================================
*/

// ============================================
// CONECTAR A LA BASE DE DATOS
// ============================================
require_once "php/connect.php";

// ============================================
// OBTENER TODOS LOS EVENTOS
// ============================================
// Ordenar por: fecha de evento (próximos primero) y estado de envío
$sql = "SELECT * FROM usuario ORDER BY fecha_evento ASC, enviado ASC";
$resultado = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos básicos -->
    <meta charset="UTF-8">
    <title>Eventos Registrados</title>
    
    <!-- Estilos internos para la página -->
    <style>
        /* ============================================
           ESTILOS CSS - Diseño de la tablaa
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial;
            background: #667eea;
            padding: 30px;
        }
        /* Contenedor central que emula una tarjeta */
        .container {
            max-width: 1200px;
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
        
        /* Tarjetas de estadísticas */
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
        
        .stat-card.pendientes {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .stat-card.enviados {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .stat-card.total {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        
        .stat-card h2 {
            font-size: 36px;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 14px;
        }
        
        /* Tabla de eventos */
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
        
        /* Badges de estado */
        .badge {
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
        }
        
        /* Resaltar eventos próximos */
        .fecha-proxima {
            background: #e3f2fd !important;
        }
        
        .fecha-hoy {
            background: #ffebee !important;
            font-weight: bold;
        }
        
        .fecha-pasada {
            color: #999;
        }
        
        /* Sin eventos */
        .no-eventos {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        /* Botones */
        .btn-admin {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-ejecutar {
            background: #28a745;
            margin-left: 10px;
        }
        
        .btn-ejecutar:hover {
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
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
        
        <!-- BOTONES DE ACCIÓN -->
        <div style="margin-bottom: 20px;">
            <a href="admin.php" class="btn-admin">➕ Registrar Nuevo Evento</a>
            <a href="enviar_correos.php" class="btn-admin btn-ejecutar" target="_blank">▶️ Ejecutar Envío Manual</a>
        </div>
        
        <?php
        // ============================================
        // CALCULAR ESTADÍSTICAS
        // ============================================
        $pendientes = 0;
        $enviados = 0;
        $eventos = [];
        $hoy = date('Y-m-d');
        
        // Recorrer todos los eventos y contar
        while ($evento = $resultado->fetch_assoc()) {
            $eventos[] = $evento;
            
            // Contar enviados y pendientes
            if ($evento['enviado'] == 0) {
                $pendientes++;
            } else {
                $enviados++;
            }
        }
        
        $total = count($eventos);
        ?>
        
        <!-- TARJETAS DE ESTADÍSTICAS -->
        <div class="stats">
            <div class="stat-card total">
                <h2><?php echo $total; ?></h2>
                <p>📊 Total Eventos</p>
            </div>
            <div class="stat-card pendientes">
                <h2><?php echo $pendientes; ?></h2>
                <p>⏳ Pendientes de Enviar</p>
            </div>
            <div class="stat-card enviados">
                <h2><?php echo $enviados; ?></h2>
                <p>✅ Ya Enviados</p>
            </div>
        </div>
        
        <!-- TABLA DE EVENTOS -->
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): 
                        // ============================================
                        // DETERMINAR CLASE CSS SEGÚN LA FECHA
                        // ============================================
                        $fecha_evento = $evento['fecha_evento'];
                        $es_hoy = ($fecha_evento == $hoy);
                        $es_proximo = ($fecha_evento > $hoy && $evento['enviado'] == 0);
                        $es_pasado = ($fecha_evento < $hoy);
                        
                        // Asignar clase CSS
                        $clase_fila = '';
                        if ($es_hoy) {
                            $clase_fila = 'fecha-hoy';
                        } elseif ($es_proximo) {
                            $clase_fila = 'fecha-proxima';
                        }
                    ?>
                        <tr class="<?php echo $clase_fila; ?>">
                            <!-- ID del evento -->
                            <td><?php echo $evento['id']; ?></td>
                            
                            <!-- Nombre del cliente -->
                            <td><strong><?php echo htmlspecialchars($evento['nombre']); ?></strong></td>
                            
                            <!-- Fecha del evento -->
                            <td class="<?php echo $es_pasado ? 'fecha-pasada' : ''; ?>">
                                <?php echo date('d/m/Y', strtotime($evento['fecha_evento'])); ?>
                                
                                <!-- Indicador si es HOY -->
                                <?php if ($es_hoy): ?>
                                    <span style="color: red; font-weight: bold; margin-left: 5px;">⚡ HOY</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Correo electrónico -->
                            <td><?php echo htmlspecialchars($evento['correo']); ?></td>
                            
                            <!-- Tipo de evento -->
                            <td><?php echo htmlspecialchars($evento['mensaje']); ?></td>
                            
                            <!-- Estado de envío -->
                            <td>
                                <?php if ($evento['enviado'] == 1): ?>
                                    <!-- YA SE ENVIÓ -->
                                    <span class="badge enviado">✅ Enviado</span>
                                    <br>
                                    <small style="color: #999; font-size: 11px;">
                                        Enviado el: <?php echo date('d/m/Y H:i', strtotime($evento['fecha_envio'])); ?>
                                    </small>
                                <?php else: ?>
                                    <!-- AÚN NO SE HA ENVIADO -->
                                    <span class="badge pendiente">⏳ Pendiente</span>
                                    <?php if ($es_hoy): ?>
                                        <br>
                                        <small style="color: red; font-size: 11px;">
                                            Se enviará hoy
                                        </small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- LEYENDA -->
            <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                <strong>Leyenda:</strong>
                <ul style="margin: 10px 0 0 20px; color: #666; font-size: 14px;">
                    <li><span style="color: red;">⚡ HOY</span> - Eventos programados para hoy (se enviarán automáticamente)</li>
                    <li style="background: #e3f2fd; display: inline-block; padding: 2px 8px; margin-top: 5px;">Fondo azul</span> - Eventos próximos pendientes de enviar</li>
                    <li style="background: #ffebee; display: inline-block; padding: 2px 8px; margin-top: 5px;">Fondo rojo</span> - Eventos para HOY</li>
                </ul>
            </div>
        
        <?php else: ?>
            <!-- NO HAY EVENTOS REGISTRADOS -->
            <div class="no-eventos">
                <h2 style="color: #999; margin-bottom: 10px;">📭</h2>
                <p style="font-size: 18px; margin-bottom: 10px;">No hay eventos registrados</p>
                <p style="margin-top: 10px;">
                    <a href="admin.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                        ➕ Registra tu primer evento
                    </a>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- INFORMACIÓN ADICIONAL -->
        <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #667eea;">
            <h3 style="color: #333; margin-bottom: 10px;">ℹ️ Información del Sistema</h3>
            <ul style="color: #666; line-height: 1.8; font-size: 14px;">
                <li><strong>Fecha actual del sistema:</strong> <?php echo date('d/m/Y H:i:s'); ?></li>
                <li><strong>Eventos pendientes:</strong> <?php echo $pendientes; ?> correos por enviar</li>
                <li><strong>Eventos para hoy:</strong> 
                    <?php 
                    $hoy_count = 0;
                    foreach ($eventos as $e) {
                        if ($e['fecha_evento'] == $hoy && $e['enviado'] == 0) {
                            $hoy_count++;
                        }
                    }
                    echo $hoy_count;
                    ?>
                </li>
                <li><strong>Hora de ejecución automática:</strong> 8:00 AM (configurado en CRON)</li>
            </ul>
        </div>
        
        <!-- INSTRUCCIONES -->
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
            <h4 style="color: #856404; margin-bottom: 10px;">⚙️ ¿Cómo funciona el sistema?</h4>
            <ol style="color: #856404; line-height: 1.8; font-size: 14px; margin-left: 20px;">
                <li>El administrador registra eventos en <strong>admin.php</strong></li>
                <li>Los eventos se guardan con estado "Pendiente" (enviado = 0)</li>
                <li>El script <strong>enviar_correos.php</strong> se ejecuta automáticamente todos los días a las 8:00 AM</li>
                <li>El script busca eventos cuya fecha sea HOY y que NO se hayan enviado</li>
                <li>Envía correos a cada uno y los marca como "Enviado" (enviado = 1)</li>
            </ol>
            <p style="color: #856404; margin-top: 10px; font-size: 14px;">
                <strong>Nota:</strong> Para probar manualmente, haz clic en "▶️ Ejecutar Envío Manual"
            </p>
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

<?php 
// ============================================
// CERRAR CONEXIÓN A LA BASE DE DATOS
// ============================================
$mysqli->close(); 
?>

<!--
================================================================================
ARCHIVO 3: ver_eventos.php - RESUMEN
================================================================================

QUÉ MUESTRA ESTA PÁGINA:
    ✅ Estadísticas generales (total, pendientes, enviados)
    ✅ Tabla con todos los eventos
    ✅ Estado de cada evento (pendiente o enviado)
    ✅ Resalta visualmente los eventos importantes
    ✅ Información del sistema
    ✅ Instrucciones de uso

COLORES Y INDICADORES:
    🔵 Fondo azul = Evento próximo pendiente
    🔴 Fondo rojo = Evento para HOY
    ⚡ HOY = Se enviará hoy automáticamente
    ✅ Enviado = Ya se envió el correo
    ⏳ Pendiente = Esperando la fecha

ACCIONES DISPONIBLES:
    1. Ver todos los eventos registrados
    2. Ir a admin.php para registrar nuevos
    3. Ejecutar enviar_correos.php manualmente para probar

USO RECOMENDADO:
    - Revisa esta página después de registrar eventos
    - Verifica que los correos se estén enviando
    - Monitorea el estado del sistema
================================================================================
-->