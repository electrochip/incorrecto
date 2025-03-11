<?php
require 'session_check.php'; // Nueva inclusión
// Control de Cambios
// Hash: q9r0s1t2u3v4w5x6y7z8a9b0c1d2 (MD5 del contenido sin este comentario)
// Versión: v2.3
require 'fpdf/fpdf.php';
require 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}


class PDF_Asignaciones extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Modelo Enrique Laubscher - CCT: 30EPR1536G', 0, 1, 'C');
        $this->Ln(5);
    }

    // Método para convertir UTF-8 a Latin1 (ISO-8859-1) para FPDF
    function convertToLatin1($text) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }

    // Método para manejar texto con acentos
    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $txt = $this->convertToLatin1($txt);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ciclo = $_POST['ciclo'] ?? '2025-2026';

    $sql = "SELECT d.nombre, d.primer_apellido, g.grado, g.grupo 
            FROM grupos g 
            JOIN docentes d ON g.id_docente = d.id 
            WHERE g.ciclo = :ciclo";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':ciclo' => $ciclo]);
    $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf = new PDF_Asignaciones('P', 'mm', 'Letter');
    $pdf->SetFont('Arial', '', 12);
    $pdf->AddPage();
    $pdf->Cell(0, 10, "Ciclo Escolar: $ciclo", 0, 1);

    foreach ($asignaciones as $asignacion) {
        $pdf->Cell(0, 10, "{$asignacion['nombre']} {$asignacion['primer_apellido']} - Grado: {$asignacion['grado']} Grupo: {$asignacion['grupo']}", 0, 1);
    }

    $pdf->Output('D', 'reporte_asignaciones.pdf');
}

require 'header.php';
?>

<main>
    <h2>Reporte de Asignaciones</h2>
    <div class="form-container">
        <form method="POST" action="">
            <label>Ciclo Escolar:</label>
            <input type="text" name="ciclo" value="2025-2026" required><br>
            <button type="submit">Generar Reporte PDF</button>
        </form>
    </div>
</main>
</body>
</html>