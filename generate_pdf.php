<?php
session_start();
require_once 'fpdf/fpdf.php';
require_once 'database.php';

if (!isset($_GET['folio']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$folio = $_GET['folio'];

try {
    $sql = "SELECT a.curp, a.nombres, a.primer_apellido, a.segundo_apellido, g.grado, g.grupo, d.nombre AS docente_nombre, d.primer_apellido AS docente_apellido, i.folio 
            FROM inscripciones i 
            JOIN alumnos a ON i.curp_alumno = a.curp 
            JOIN grupos g ON i.id_grupo = g.id 
            JOIN docentes d ON g.id_docente = d.id 
            WHERE i.folio = :folio AND i.ciclo = '2025-2026'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':folio' => $folio]);
    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscripcion) {
        die("Error: Folio no encontrado.");
    }

    class PDF_Comprobante extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 10, 'Comprobante de Inscripción - Modelo Enrique Laubscher', 0, 1, 'C');
            $this->Ln(5);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF_Comprobante('P', 'mm', 'Letter');
    $pdf->SetFont('Arial', '', 12);
    $pdf->AddPage();
    $pdf->Cell(0, 10, "Folio: " . $inscripcion['folio'], 0, 1);
    $pdf->Cell(0, 10, "Alumno: " . $inscripcion['nombres'] . " " . $inscripcion['primer_apellido'] . " " . $inscripcion['segundo_apellido'], 0, 1);
    $pdf->Cell(0, 10, "CURP: " . $inscripcion['curp'], 0, 1);
    $pdf->Cell(0, 10, "Grado: " . $inscripcion['grado'], 0, 1);
    $pdf->Cell(0, 10, "Grupo: " . $inscripcion['grupo'], 0, 1);
    $pdf->Cell(0, 10, "Docente: " . $inscripcion['docente_nombre'] . " " . $inscripcion['docente_apellido'], 0, 1);
    $pdf->Cell(0, 10, "Fecha: " . date('d/m/Y H:i:s'), 0, 1);

    $pdf->Output('D', 'comprobante_' . $inscripcion['folio'] . '.pdf');
    exit();
} catch (Exception $e) {
    die("Error al generar el PDF: " . $e->getMessage());
}