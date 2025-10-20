<?php
/*
================================================================================
ARCHIVO 1: admin.php
================================================================================
PROPÓSITO: 
    Panel de administración para REGISTRAR eventos en la base de datos.
    
QUÉ HACE:
    1. Muestra un formulario para registrar eventos
    2. Recibe los datos del formulario (nombre, fecha, correo, mensaje)
    3. Valida que todos los campos estén completos
    4. Guarda el evento en la base de datos
    5. NO ENVÍA correos (eso lo hace enviar_correos.php automáticamente)
    
CUÁNDO SE USA:
    - Cuando el administrador quiere programar un evento nuevo
    - Se accede manualmente desde el navegador
    
IMPORTANTE:
    - El campo 'enviado' se guarda en 0 (NO enviado)
    - El correo se enviará automáticamente el día del evento
================================================================================
*/

// Variable para mostrar mensajes al usuario
$mensajeResultado = "";

// VERIFICAR SI EL FORMULARIO FUE ENVIADO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ============================================
    // 1. RECIBIR DATOS DEL FORMULARIO
    // ============================================
    // trim() elimina espacios en blanco al inicio y final
    $nombre = trim($_POST['nombre']);
    $fecha_evento = $_POST['fecha_evento'];
    $correo = trim($_POST['correo']);
    $mensaje = trim($_POST['mensaje']);
    
    // ============================================
    // 2. VALIDAR DATOS
    // ============================================
    // Verificar que ningún campo esté vacío
    if (empty($nombre) || empty($fecha_evento) || empty($correo) || empty($mensaje)) {
        $mensajeResultado = "<p style='color: red;'>❌ Todos los campos son obligatorios</p>";
    } 
    // Validar que el correo sea válido
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensajeResultado = "<p style='color: red;'>❌ El correo no es válido</p>";
    } 
    // Si todo está bien, guardar en base de datos
    else {
        
        // ============================================
        // 3. CONECTAR A LA BASE DE DATOS
        // ============================================
        require_once "php/connect.php";
        
        // ============================================
        // 4. INSERTAR EN BASE DE DATOS (FORMA SEGURA)
        // ============================================
        // Prepared Statement para prevenir SQL Injection
        $sql = "INSERT INTO usuario(nombre, fecha_evento, correo, mensaje, enviado) VALUES (?, ?, ?, ?, 0)";
        
        // Preparar la consulta
        $stmt = $mysqli->prepare($sql);
        
        // Vincular parámetros (s = string)
        // Los 4 "ssss" significan: string, string, string, string
        $stmt->bind_param("ssss", $nombre, $fecha_evento, $correo, $mensaje);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Éxito: Mostrar mensaje de confirmación
            $mensajeResultado = "<p style='color: green; font-weight: bold;'>
                ✅ ¡Evento programado exitosamente!<br>
                Se enviará automáticamente el: " . date('d/m/Y', strtotime($fecha_evento)) . "
            </p>";
        } else {
            // Error: Mostrar mensaje de error
            $mensajeResultado = "<p style='color: red;'>❌ Error al guardar en la base de datos</p>";
        }
        
        // Cerrar statement y conexión
        $stmt->close();
        $mysqli->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Registro de Eventos</title>
    <style>
        /* ============================================
           ESTILOS CSS - Diseño del formulario
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        form {
            display: flex;
            flex-direction: column;
        }
        
        label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 20px;
            font-family: inherit;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea {
            resize: vertical;
        }
        
        input[type="submit"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .mensaje {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .info-box p {
            color: #1565c0;
            font-size: 14px;
            margin: 0;
        }
        
        .btn-ver-eventos {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .btn-ver-eventos:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- TÍTULO Y DESCRIPCIÓN -->
        <h1>🗓️ Panel de Administración</h1>
        <p class="subtitle">Programa eventos - Los correos se envían automáticamente en la fecha indicada</p>
        
        <!-- INFORMACIÓN IMPORTANTE -->
        <div class="info-box">
            <p>ℹ️ El correo se enviará automáticamente el día del evento a las 8:00 AM</p>
        </div>
        
        <!-- MOSTRAR MENSAJE DE ÉXITO O ERROR -->
        <?php if ($mensajeResultado): ?>
            <div class="mensaje"><?php echo $mensajeResultado; ?></div>
        <?php endif; ?>
        
        <!-- FORMULARIO DE REGISTRO -->
        <form action="" method="post">
            <!-- Campo: Nombre del cliente -->
            <label for="nombre">Nombre del Cliente</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
            
            <!-- Campo: Fecha del evento -->
            <label for="fecha_evento">Fecha del Evento</label>
            <input type="date" id="fecha_evento" name="fecha_evento" required min="<?php echo date('Y-m-d'); ?>">
            
            <!-- Campo: Correo electrónico -->
            <label for="correo">Correo Electrónico</label>
            <input type="email" id="correo" name="correo" required placeholder="cliente@correo.com">
            
            <!-- Campo: Tipo de evento o mensaje -->
            <label for="mensaje">Tipo de Evento</label>
            <textarea id="mensaje" name="mensaje" rows="3" required placeholder="Ej: Cumpleaños, Aniversario, Boda, Graduación, etc."></textarea>
            
            <!-- Botón de envío -->
            <input type="submit" value="📅 Programar Evento">
        </form>
        
        <!-- Enlace para ver eventos programados -->
        <a href="ver_eventos.php" class="btn-ver-eventos">📋 Ver eventos programados</a>
    </div>
</body>
</html>