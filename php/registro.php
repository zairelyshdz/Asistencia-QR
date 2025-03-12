<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registro_estudiantes";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Recoger y sanitizar datos del formulario
$nombre = mysqli_real_escape_string($conn, $_POST['Nombre']);
$apellido = mysqli_real_escape_string($conn, $_POST['Apellido']);
$fecha_nacimiento = $_POST['Fecha_de_Nacimiento'];
$cedula = mysqli_real_escape_string($conn, $_POST['Cédula_de_Identidad']);
$correo = mysqli_real_escape_string($conn, $_POST['Correo_Electrónico']);
$contraseña = password_hash($_POST['Contraseña'], PASSWORD_DEFAULT);
$año = intval($_POST['Año']);
$seccion = mysqli_real_escape_string($conn, $_POST['Sección']);
$genero = mysqli_real_escape_string($conn, $_POST['Género']);

// Validar contraseñas coincidan
if ($_POST['Contraseña'] !== $_POST['Repetir_Contraseña']) {
    die("Error: Las contraseñas no coinciden");
}

// Validar formato de cédula
if (!preg_match('/^[VE]?-?\d{6,8}$/i', $cedula) && !preg_match('/^[A-Z0-9-]{7,12}$/i', $cedula)) {
    die("Error: Formato de cédula inválido");
}

// Verificar cédula única
$check_cedula = $conn->prepare("SELECT cedula FROM estudiantes WHERE cedula = ?");
$check_cedula->bind_param("s", $cedula);
$check_cedula->execute();
$check_cedula->store_result();

if ($check_cedula->num_rows > 0) {
    die("Error: La cédula ya está registrada");
}

// Verificar correo único
$check_email = $conn->prepare("SELECT correo FROM estudiantes WHERE correo = ?");
$check_email->bind_param("s", $correo);
$check_email->execute();
$check_email->store_result();

if ($check_email->num_rows > 0) {
    die("Error: El correo electrónico ya está registrado");
}

// Insertar datos en la base de datos
$stmt = $conn->prepare("INSERT INTO estudiantes 
    (nombre, apellido, fecha_nacimiento, cedula, correo, contraseña, año, seccion, genero)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssiss", 
    $nombre,
    $apellido,
    $fecha_nacimiento,
    $cedula,
    $correo,
    $contraseña,
    $año,
    $seccion,
    $genero
);

if ($stmt->execute()) {
    header("Location: ../login.html?success=1");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>