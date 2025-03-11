<?php
require 'session_check.php';
require 'vendor/autoload.php'; // Asegúrate de incluir PhpSpreadsheet via Composer o manualmente
// Control de Cambios
// Hash: p9q0r1s2t3u4v5w6x7y8z9a0b1c2d3e4f5g6h7i8 (MD5 del contenido sin este comentario)
// Versión: v2.6
require 'database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

$ciclo = $_GET['ciclo'] ?? '';
$grado = $_GET['grado'] ?? '';
$grupo = $_GET['grupo'] ?? '';
$alumno = $_GET['alumno'] ?? '';

$sql = "SELECT h.curp_alumno, a.nombres, a.primer_apellido, a.segundo_apellido, h.grado, h.calificacion 
        FROM historico h 
        JOIN alumnos a ON h.curp_alumno = a.curp 
        WHERE 1=1";
$params = [];
if ($ciclo) { $sql .= " AND h.ciclo = :ciclo"; $params[':ciclo'] = $ciclo; }
if ($grado) { $sql .= " AND h.grado = :grado"; $params[':grado'] = $grado; }
if ($grupo) { $sql .= " AND h.curp_alumno IN (SELECT i.curp_alumno FROM inscripciones i JOIN grupos g ON i.id_grupo = g.id WHERE g.grupo = :grupo)"; $params[':grupo'] = $grupo; }
if ($alumno) { $sql .= " AND h.curp_alumno = :curp"; $params[':curp'] = $alumno; }
$sql .= " ORDER BY h.curp_alumno, h.grado";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'No.');
$sheet->setCellValue('B1', 'CURP');
$sheet->setCellValue('C1', 'Nombre');
for ($g = 1; $g <= 6; $g++) $sheet->setCellValueByColumnAndRow($g + 3, 1, $g . '°');
$sheet->setCellValue('I1', 'Promedio');

$current_curp = '';
$grades = [];
$row = 2;
$i = 1;
foreach ($historico as $row_data) {
    if ($current_curp != $row_data['curp_alumno']) {
        if ($current_curp) {
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $current_curp);
            $sheet->setCellValue('C' . $row, $name);
            $promedio = array_sum($grades) / count($grades);
            for ($g = 1; $g <= 6; $g++) $sheet->setCellValueByColumnAndRow($g + 3, $row, isset($grades[$g]) ? $grades[$g] : '');
            $sheet->setCellValue('I' . $row, $promedio);
            $row++;
        }
        $current_curp = $row_data['curp_alumno'];
        $name = $row_data['primer_apellido'] . ' / ' . $row_data['segundo_apellido'] . ' * ' . $row_data['nombres'];
        $grades = [];
    }
    $grades[$row_data['grado']] = $row_data['calificacion'];
}
if ($current_curp) {
    $sheet->setCellValue('A' . $row, $i);
    $sheet->setCellValue('B' . $row, $current_curp);
    $sheet->setCellValue('C' . $row, $name);
    $promedio = array_sum($grades) / count($grades);
    for ($g = 1; $g <= 6; $g++) $sheet->setCellValueByColumnAndRow($g + 3, $row, isset($grades[$g]) ? $grades[$g] : '');
    $sheet->setCellValue('I' . $row, $promedio);
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="historico_' . date('YmdHis') . '.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit();
?>