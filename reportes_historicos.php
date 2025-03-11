<?php
require 'session_check.php'; // Nueva inclusión
// Control de Cambios
// Hash: r0s1t2u3v4w5x6y7z8a9b0c1d2e3 (MD5 del contenido sin este comentario)
// Versión: v2.3
require 'fpdf/fpdf.php';
require 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

class PDF_Historicos extends FPDF {
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
    $tipo_reporte = $_POST['tipo_reporte'];
    $ciclo = $_POST['ciclo'] ?? '2025-2026';
    $grado = $_POST['grado'] ?? '';
    $grupo = $_POST['grupo'] ?? '';
    $curp = $_POST['curp'] ?? '';

    $pdf = new PDF_Historicos('P', 'mm', 'Letter');
    $pdf->SetFont('Arial', '', 12);
    $pdf->AddPage();

    if ($tipo_reporte == 'alumno') {
        $sql = "SELECT a.nombres, a.primer_apellido, a.segundo_apellido, h.grado, h.calificacion 
                FROM historico h 
                JOIN alumnos a ON h.curp_alumno = a.curp 
                WHERE h.curp_alumno = :curp 
                ORDER BY h.grado";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':curp' => $curp]);
        $historicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdf->Cell(0, 10, "Reporte de Histórico - Alumno: $curp", 0, 1);
        $pdf->Cell(0, 10, "{$historicos[0]['nombres']} {$historicos[0]['primer_apellido']} {$historicos[0]['segundo_apellido']}", 0, 1);
        $pdf->Ln(5);
        $pdf->Cell(40, 10, 'Grado', 1);
        $pdf->Cell(40, 10, 'Calificación', 1);
        $pdf->Ln();
        foreach ($historicos as $historico) {
            $pdf->Cell(40, 10, $historico['grado'], 1);
            $pdf->Cell(40, 10, $historico['calificacion'], 1);
            $pdf->Ln();
        }
    } elseif ($tipo_reporte == 'grado') {
        $sql = "SELECT a.curp, a.nombres, a.primer_apellido, a.segundo_apellido, h.grado, h.calificacion 
                FROM historico h 
                JOIN alumnos a ON h.curp_alumno = a.curp 
                JOIN inscripciones i ON a.curp = i.curp_alumno 
                JOIN grupos g ON i.id_grupo = g.id 
                WHERE g.grado = :grado AND i.ciclo = :ciclo";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':grado' => $grado, ':ciclo' => $ciclo]);
        $historicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdf->Cell(0, 10, "Reporte de Histórico - Grado: $grado - Ciclo: $ciclo", 0, 1);
        $pdf->Ln(5);
        $pdf->Cell(30, 10, 'CURP', 1);
        $pdf->Cell(40, 10, 'Nombre', 1);
        $pdf->Cell(40, 10, 'Primer Apellido', 1);
        $pdf->Cell(40, 10, 'Segundo Apellido', 1);
        $pdf->Cell(40, 10, 'Calificación', 1);
        $pdf->Ln();
        foreach ($historicos as $historico) {
            $pdf->Cell(30, 10, $historico['curp'], 1);
            $pdf->Cell(40, 10, $historico['nombres'], 1);
            $pdf->Cell(40, 10, $historico['primer_apellido'], 1);
            $pdf->Cell(40, 10, $historico['segundo_apellido'], 1);
            $pdf->Cell(40, 10, $historico['calificacion'], 1);
            $pdf->Ln();
        }
    } elseif ($tipo_reporte == 'grupo') {
        $sql = "SELECT d.nombre, d.primer_apellido, g.grado, g.grupo, a.curp, a.nombres, a.primer_apellido AS a_paterno, a.segundo_apellido, h.calificacion 
                FROM inscripciones i 
                JOIN grupos g ON i.id_grupo = g.id 
                JOIN docentes d ON g.id_docente = d.id 
                JOIN alumnos a ON i.curp_alumno = a.curp 
                LEFT JOIN historico h ON a.curp = h.curp_alumno AND h.grado = g.grado 
                WHERE g.grupo = :grupo AND g.ciclo = :ciclo";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':grupo' => $grupo, ':ciclo' => $ciclo]);
        $historicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdf->Cell(0, 10, "Reporte de Histórico - Grupo: $grupo - Ciclo: $ciclo", 0, 1);
        $pdf->Cell(0, 10, "Docente: {$historicos[0]['nombre']} {$historicos[0]['primer_apellido']}", 0, 1);
        $pdf->Ln(5);
        $pdf->Cell(30, 10, 'CURP', 1);
        $pdf->Cell(40, 10, 'Nombre', 1);
        $pdf->Cell(40, 10, 'Primer Apellido', 1);
        $pdf->Cell(40, 10, 'Segundo Apellido', 1);
        $pdf->Cell(40, 10, 'Calificación', 1);
        $pdf->Ln();
        foreach ($historicos as $historico) {
            $pdf->Cell(30, 10, $historico['curp'], 1);
            $pdf->Cell(40, 10, $historico['nombres'], 1);
            $pdf->Cell(40, 10, $historico['a_paterno'], 1);
            $pdf->Cell(40, 10, $historico['segundo_apellido'], 1);
            $pdf->Cell(40, 10, $historico['calificacion'] ?? 'N/A', 1);
            $pdf->Ln();
        }
    }

    $pdf->Output('D', 'reporte_historicos.pdf');
}

require 'header.php';
?>

<main>
    <h2>Reporte de Históricos</h2>
    <div class="form-container">
        <form method="POST" action="">
            <label>Tipo de Reporte:</label>
            <select name="tipo_reporte" required>
                <option value="alumno">Por Alumno</option>
                <option value="grado">Por Grado</option>
                <option value="grupo">Por Grupo</option>
            </select><br>
            <label>Ciclo Escolar:</label>
            <input type="text" name="ciclo" value="2025-2026" required><br>
            <?php if (isset($_POST['tipo_reporte']) && $_POST['tipo_reporte'] == 'alumno'): ?>
                <label>CURP:</label>
                <input type="text" name="curp" required><br>
            <?php elseif (isset($_POST['tipo_reporte']) && $_POST['tipo_reporte'] == 'grado'): ?>
                <label>Grado:</label>
                <select name="grado">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select><br>
            <?php elseif (isset($_POST['tipo_reporte']) && $_POST['tipo_reporte'] == 'grupo'): ?>
                <label>Grupo:</label>
                <input type="text" name="grupo" required><br>
            <?php endif; ?>
            <button type="submit">Generar Reporte PDF</button>
        </form>
    </div>
</main>
</body>
</html>