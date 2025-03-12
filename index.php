<?php
session_start();
require 'php/config/db.php';

// Procesar login si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $contrasena = $_POST['contrasena'];

        // 1. Primero verificar en la tabla de usuarios (admin/director)
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ? AND activo = 1");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            $hash = '$2y$10$tuHashDeLaBD'; 
$password = 'adminif';

echo password_verify($password, $hash) ? "Válido" : "Inválido";
            
            if (password_verify($contrasena, $usuario['contraseña'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['nombre'] = $usuario['nombre'];
                header('Location: admin.php');
                exit;
            }
            throw new Exception("Contraseña incorrecta");
        }

        // 2. Si no es admin, verificar en estudiantes
        $stmt = $conexion->prepare("SELECT * FROM estudiantes WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            throw new Exception("Credenciales inválidas");
        }

        $usuario = $resultado->fetch_assoc();

        if (!password_verify($contrasena, $usuario['contraseña'])) {
            throw new Exception("Credenciales inválidas");
        }

        // Configurar sesión para estudiante
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['nombre'] = $usuario['nombre'];

        // Generar QR para estudiantes
        if ($_SESSION['rol'] === 'estudiante') {
            $qr_token = bin2hex(random_bytes(16));
            $expira = new DateTime('now', new DateTimeZone('America/Caracas'));
            $expira->setTime(17, 30, 0);

            $update_stmt = $conexion->prepare("UPDATE estudiantes SET 
                qr_token = ?, 
                qr_expira = ?
                WHERE id = ?");
            $update_stmt->bind_param("ssi", 
                $qr_token, 
                $expira->format('Y-m-d H:i:s'),
                $usuario['id']
            );
            $update_stmt->execute();

            $_SESSION['qr_data'] = [
                'token' => $qr_token,
                'expira' => $expira->format('Y-m-d H:i:s')
            ];
        }

        header('Location: dashboard.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Redirigir usuarios autenticados
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . ($_SESSION['rol'] === 'estudiante' ? 'dashboard.php' : 'admin.php'));
    exit;
}

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;

 } 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>EduTrack - Login</title>
    
    <!-- Estilos -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
    .dashboard {
        background: rgba(255, 255, 255, 0.95); /* Fondo semi-transparente */
        backdrop-filter: blur(5px); /* Efecto vidrio esmerilado */
        border-radius: 15px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        margin: 5% auto;
        padding: 40px;
    }

    /* Hereda el gradiente del login */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
        min-height: 100vh;
    }
         /* Dentro del <style> existente */
    .logout-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .logout-btn {
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            font-size: 14px;
        }
    }

    /* Transición suave entre vistas */
    .view-container {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }
        .view-container.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

        
<body class="bg-gradient-primary">
    <!-- Formulario de Login -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-5">
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form id="loginForm" method="POST">
                            <div class="form-group">
                                <input type="email" name="correo" class="form-control form-control-user"
                                    placeholder="Correo electrónico" required>
                            </div>
                            <div class="form-group">
                                <input type="password" name="contrasena" class="form-control form-control-user"
                                    placeholder="Contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Iniciar Sesión
                            </button>

                            <div class="text-center mt-3">¿Nuevo usuario? <a href="register.html">Regístrate aquí</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        // Generar y manejar QR
        <?php if (isset($_SESSION['qr_data'])): ?>
        const qrData = {
            token: '<?php echo $_SESSION['qr_data']['token']; ?>',
            expira: '<?php echo $_SESSION['qr_data']['expira']; ?>'
        };
        
        // Generar QR
        new QRCode(document.getElementById('qrcode'), {
            text: qrData.token,
            width: 200,
            height: 200,
            correctLevel: QRCode.CorrectLevel.H
        });

        // Temporizador
        function actualizarTemporizador() {
            const ahora = new Date();
            const expiracion = new Date(qrData.expira);
            const diferencia = Math.floor((expiracion - ahora) / 1000);
            
            if (diferencia <= 0) {
                document.getElementById('qrTimer').textContent = 'QR Expirado';
                return;
            }
            
            const horas = Math.floor(diferencia / 3600);
            const minutos = Math.floor((diferencia % 3600) / 60);
            document.getElementById('qrTimer').textContent = `Válido por: ${horas}h ${minutos}m`;
            
            setTimeout(actualizarTemporizador, 1000);
        }
        actualizarTemporizador();

        // Descargar QR y redirigir
        function descargarQR() {
            const canvas = document.querySelector('#qrcode canvas');
            const enlace = document.createElement('a');
            enlace.download = 'mi-qr.png';
            enlace.href = canvas.toDataURL();
            enlace.click();
            
            // Redirigir después de descargar
            setTimeout(() => {
                window.location.href = 'lectura.html';
            }, 1000);
        }
        <?php unset($_SESSION['qr_data']); endif; ?>
    </script>
</body>
</html>