<?php
require 'php/config/db.php';

$anio = $_GET['anio'] ?? 'all';

try {
    $query = "SELECT 
                SUM(CASE WHEN genero = 'M' THEN 1 ELSE 0 END) as ninos,
                SUM(CASE WHEN genero = 'F' THEN 1 ELSE 0 END) as ninas
              FROM estudiantes
              WHERE (? = 'all' OR año = ?)";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('ss', $anio, $anio);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    echo json_encode($resultado->fetch_assoc());

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}