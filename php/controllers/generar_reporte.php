<?php
require '../../config/db.php';
require '../../vendor/autoload.php'; // Incluir Composer

// Obtener datos de la base de datos
$query = "SELECT * FROM vista_reporte_completo"; // Usar misma vista del get_asistencia.php
$resultado = $conexion->query($query);

// Configurar según formato
$formato = $_GET['formato'] ?? 'pdf';

if($formato === 'pdf') {
    // Generar PDF con TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
    $pdf->AddPage();
    
    // Contenido HTML del reporte
    $html = '<h1>Reporte de Asistencia</h1>';
    $html .= '<table border="1"><tr><th>Nombre</th><th>Hora Entrada</th>...</tr>';
    
    while($row = $resultado->fetch_assoc()) {
        $html .= "<tr><td>{$row['nombre']}</td><td>{$row['entrada']}</td>...</tr>";
    }
    
    $pdf->writeHTML($html);
    $pdf->Output('reporte_asistencia.pdf', 'D');

} elseif($formato === 'excel') {
    // Generar Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_asistencia.xlsx"');

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Cabeceras
    $sheet->setCellValue('A1', 'Nombre');
    $sheet->setCellValue('B1', 'Hora Entrada');
    //...
    
    // Datos
    $rowNumber = 2;
    while($registro = $resultado->fetch_assoc()) {
        $sheet->setCellValue('A'.$rowNumber, $registro['nombre']);
        $sheet->setCellValue('B'.$rowNumber, $registro['entrada']);
        //...
        $rowNumber++;
    }

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
}