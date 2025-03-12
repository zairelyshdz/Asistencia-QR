<?php
require '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar token QR
    $stmt = $conexion->prepare("SELECT id, qr_token, qr_expira FROM usuarios WHERE qr_token = ?");
    $stmt->bind_param("s", $data['token']);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    if (!$usuario || new DateTime() > new DateTime($usuario['qr_expira'])) {
        echo json_encode(['status' => 'error', 'message' => 'QR inválido o expirado']);
        exit;
    }

    // Determinar tipo de registro
    $ultimo_registro = $conexion->query("SELECT tipo FROM registros_asistencia 
                                       WHERE usuario_id = {$usuario['id']} 
                                       ORDER BY fecha_hora DESC LIMIT 1");

    $tipo = ($ultimo_registro->num_rows > 0 && $ultimo_registro->fetch_assoc()['tipo'] === 'entrada') 
            ? 'salida' 
            : 'entrada';

    // Insertar nuevo registro
    $insert = $conexion->prepare("INSERT INTO registros_asistencia 
                                (usuario_id, tipo, dispositivo) 
                                VALUES (?, ?, ?)");
    $insert->bind_param("iss", $usuario['id'], $tipo, $_SERVER['HTTP_USER_AGENT']);
    
    if ($insert->execute()) {
        echo json_encode(['status' => 'success', 'tipo' => $tipo]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar']);
    }
    
    $insert->close();
    $stmt->close();
    $conexion->close();
}
?>