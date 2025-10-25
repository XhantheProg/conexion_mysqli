<?php
// ============================================
// SCRIPT DE ENVÍO AUTOMÁTICO DE CORREOS
// Se ejecuta diariamente con CRON JOB
// ============================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './php_mailer/Exception.php';
require './php_mailer/PHPMailer.php';
require './php_mailer/SMTP.php';
require_once "php/connect.php";

// Registrar inicio de ejecución
$log = "=== Ejecución: " . date('Y-m-d H:i:s') . " ===\n";
echo $log;

// Obtener fecha de hoy
$hoy = date('Y-m-d');
echo "Buscando eventos para hoy: $hoy\n";

// Buscar eventos de HOY que NO se han enviado
$sql = "SELECT * FROM usuario WHERE fecha_evento = ? AND enviado = 0"; // El campo 'enviado' se guarda en 0 (NO enviado)
$stmt = $mysqli->prepare($sql); // Preparar la consulta
$stmt->bind_param("s", $hoy); // Vincular el parámetro
$stmt->execute(); // Ejecutar la consulta
$resultado = $stmt->get_result(); // Obtener resultados

$enviados = 0; // Contador de correos enviados
$errores = 0; // Contador de errores

// Recorrer cada evento de hoy
while ($evento = $resultado->fetch_assoc()) { //fetch_assoc obtiene una fila como un array asociativo
    
    echo "\n--- Procesando evento ID: {$evento['id']} ---\n";
    echo "Cliente: {$evento['nombre']}\n";
    echo "Correo: {$evento['correo']}\n";
    
    // Configurar PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'curlydoge107@gmail.com';     // ⚠️ CAMBIAR
        $mail->Password   = 'vclf nkts ziso ztst';         // ⚠️ CAMBIAR
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Remitente y destinatario
        $mail->setFrom('curlydoge107@gmail.com', 'Sistema de Eventos');
        $mail->addAddress($evento['correo'], $evento['nombre']);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = '¡Felicitaciones en tu día especial! 🎉';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                            color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0; font-size: 32px;'>🎉</h1>
                    <h2 style='margin: 10px 0 0 0;'>¡Feliz {$evento['mensaje']}!</h2>
                </div>
                <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #333; margin-top: 0;'>Hola, {$evento['nombre']}</h2>
                    <p style='font-size: 16px; color: #666; line-height: 1.6;'>
                        En este día tan especial queremos enviarte nuestros mejores deseos. 
                        Esperamos que tengas un día maravilloso lleno de alegría y momentos inolvidables.
                    </p>
                    <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;'>
                        <p style='color: #667eea; font-size: 18px; font-weight: bold; margin: 0;'>
                            📅 " . date('d/m/Y', strtotime($evento['fecha_evento'])) . "
                        </p>
                    </div>
                    <p style='color: #666; font-size: 15px; font-style: italic;'>
                        \"{$evento['mensaje']}\"
                    </p>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #999; font-size: 13px;'>
                    <p>Con cariño,<br><strong>El equipo de Sistema de Eventos</strong></p>
                </div>
            </div>
        ";
        
        // Enviar
        $mail->send();
        
        // Marcar como enviado en la BD
        $sqlUpdate = "UPDATE usuario SET enviado = 1, fecha_envio = NOW() WHERE id = ?"; // Actualizar campo 'enviado' a 1 (ENVIADO)
        $stmtUpdate = $mysqli->prepare($sqlUpdate); // Preparar la consulta
        $stmtUpdate->bind_param("i", $evento['id']); // Vincular el ID del evento
        $stmtUpdate->execute(); // Ejecutar la actualización
        $stmtUpdate->close(); // Cerrar statement de actualización
        
        echo "✅ Correo enviado exitosamente\n";
        $enviados++;
        
    } catch (Exception $e) { // Manejo de errores
        echo "❌ Error al enviar: {$mail->ErrorInfo}\n";
        $errores++;
    }
}

$stmt->close();
$mysqli->close();

// Resumen final
echo "\n=== RESUMEN ===\n";
echo "Total enviados: $enviados\n";
echo "Total errores: $errores\n";
echo "Finalizado: " . date('Y-m-d H:i:s') . "\n";
?>