<?php
session_start();
require '../config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitizar entradas
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $contrasena = $_POST['contrasena'];

        // Consulta preparada
        $stmt = $conexion->prepare("SELECT * FROM estudiantes WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            throw new Exception("Credenciales inválidas");
        }

        $usuario = $resultado->fetch_assoc();
        
        if (!password_verify($contrasena, $usuario['contraseña'])) {
            // Dentro del bloque if ($usuario && password_verify(...)):
            if (in_array($usuario['rol'], ['admin', 'director'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];
            header("Location: admin.php"); // Solo redirección, sin respuesta JSON
            exit();
        } else {
        // Generar QR y responder con JSON
        header('Content-Type: application/json');
        echo json_encode([...]);
        exit();
        }
            throw new Exception("Credenciales inválidas");

        }

        // Configurar sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['nombre'] = $usuario['nombre'];

        // Redirección para roles administrativos
        if (in_array($usuario['rol'], ['admin', 'director'])) {
            header("Location: admin.php");
            exit();
        }

        // Generar token QR para estudiantes
        $qr_token = bin2hex(random_bytes(16));
        $expira = new DateTime('now', new DateTimeZone('America/Caracas'));
        $expira->setTime(16, 30, 0);

        // Actualizar base de datos
        $update_stmt = $conexion->prepare("UPDATE estudiantes SET 
            qr_token = ?,
            qr_expira = ?
            WHERE id = ?");
        
        $update_stmt->bind_param("ssi",
            $qr_token,
            $expira->format('Y-m-d H:i:s'),
            $usuario['id']
        );
        
        if (!$update_stmt->execute()) {
            throw new Exception("Error al actualizar datos del QR");
        }

        // Respuesta JSON para estudiantes
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'qr_data' => [
                'token' => $qr_token,
                'expira' => $expira->format('Y-m-d H:i:s')
            ]
        ]);
        exit();

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit();
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

// Cerrar conexiones
if (isset($stmt)) $stmt->close();
if (isset($update_stmt)) $update_stmt->close();
$conexion->close();
?>