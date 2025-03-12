<?php
require '../config/db.php';

header('Content-Type: application/json');

try {
    // Consulta SQL para obtener los registros de asistencia
    $query = "SELECT 
                e.nombre,
                e.apellido,
                e.cedula,
                e.año,
                e.seccion,
                MAX(CASE WHEN a.tipo = 'entrada' THEN a.fecha_hora END) AS entrada,
                MAX(CASE WHEN a.tipo = 'salida' THEN a.fecha_hora END) AS salida
              FROM registros_asistencia a
              INNER JOIN estudiantes e ON a.estudiante_id = e.id
              GROUP BY e.id
              ORDER BY a.fecha_hora DESC";

    $resultado = $conexion->query($query);
    
    $registros = [];
    while($fila = $resultado->fetch_assoc()) {
        $registros[] = [
            'nombre' => $fila['nombre'],
            'apellido' => $fila['apellido'],
            'cedula' => $fila['cedula'],
            'año' => $fila['año'],
            'seccion' => $fila['seccion'],
            'entrada' => $fila['entrada'] ? date('H:i:s', strtotime($fila['entrada'])) : '--',
            'salida' => $fila['salida'] ? date('H:i:s', strtotime($fila['salida'])) : '--'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $registros
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener registros: ' . $e->getMessage()
    ]);
}