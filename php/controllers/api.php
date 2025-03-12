<?php
require '../config/db.php'; // Asegúrate que esta ruta sea correcta
header('Content-Type: application/json');

try {
    // Verificar método de solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Método no permitido", 405);
    }

    // Validar parámetro de acción
    if (!isset($_GET['action'])) {
        throw new Exception("Parámetro 'action' requerido", 400);
    }

    // Sanitizar y obtener acción
    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

    // Manejar diferentes acciones
    switch ($action) {
        case 'getAttendanceData':
            handleGetAttendanceData($conexion);
            break;
            
        // Agregar más casos para otras acciones aquí
            
        default:
            throw new Exception("Acción no válida", 400);
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Función para obtener datos de asistencia
function handleGetAttendanceData($conexion) {
    $rol = $_GET['rol'] ?? '';
    $query = "SELECT 
                e.nombre, 
                e.apellido, 
                e.cedula, 
                e.año, 
                e.seccion,
                MAX(CASE WHEN r.tipo = 'entrada' THEN r.fecha_hora END) as entrada,
                MAX(CASE WHEN r.tipo = 'salida' THEN r.fecha_hora END) as salida
              FROM estudiantes e
              LEFT JOIN registros_asistencia r ON e.id = r.estudiante_id
              GROUP BY e.id
              ORDER BY entrada DESC";
    if($rol === 'director') {
        $query .= " WHERE fecha_hora >= CURDATE()"; // Solo datos del día
    }
    $result = $conexion->query($query);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . $conexion->error, 500);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

    if ($_SESSION['rol'] !== 'admin') {
    die(json_encode(['error' => 'Acceso no autorizado']));
}
}

// Cerrar conexión si es necesario
$conexion->close();
?>