<?php
session_start();
require 'php/config/db.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: index.php');
    exit;
}

// Obtener datos del estudiante CON EL QR
$stmt = $conexion->prepare("SELECT *, qr_token, qr_expira FROM estudiantes WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Actualizar sesión con datos del QR
$_SESSION['qr_data'] = [
    'token' => $usuario['qr_token'],
    'expira' => $usuario['qr_expira']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Mismo head que index.php -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>EduTrack - Login</title>
    
    <!-- Estilos -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin: 2rem auto;
            padding: 2rem;
            max-width: 800px;
        }

        .qr-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 2rem 0;
    }
    
    #qrcode {
        margin: 0 auto;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    </style>
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <!-- Botón cerrar sesión -->
        <div class="text-right pt-3">
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
            </a>
        </div>

        <div class="view-container active">
            <div class="container">
                <div class="dashboard-card">
                    <div class="text-center">
                        <h3 class="mb-4">Bienvenido <?php echo $usuario['nombre']; ?></h3>
                    </div>
                    <div class="text-center">
                        <h3 class="mb-4">Tu código QR de acceso</h3>
                        <div id="qrcode"></div>
                        <div id="qrTimer" class="text-primary font-weight-bold my-3"></div>
                        <button class="btn btn-success download-btn" onclick="descargarQR()">
                            <i class="fas fa-download mr-2"></i> Descargar QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- Mismos scripts que antes -->


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        // Generar y manejar QR
        <?php if (isset($_SESSION['qr_data'])): ?>
        const qrData = {
            token: '<?= $usuario['qr_token'] ?>', // Usar token de la BD
            expira: '<?= $usuario['qr_expira'] ?>'
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
        <?php endif; ?> 
    </script>
</body>
</html>