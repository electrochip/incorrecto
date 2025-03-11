<?php
require 'session_check.php'; // Nueva inclusión
// Control de Cambios
// Hash: p8q9r0s1t2u3v4w5x6y7z8a9b0c1 (MD5 del contenido sin este comentario)
// Versión: v2.3
require 'fpdf/fpdf.php';
require 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

class PDF_Alumnos extends FPDF {
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
    $grado = $_POST['grado'] ?? '';
    $grupo = $_POST['grupo'] ?? '';

    $sql = "SELECT a.curp, a.nombres, a.primer_apellido, a.segundo_apellido, g.grado, g.grupo 
            FROM inscripciones i 
            JOIN alumnos a ON i.curp_alumno = a.curp 
            JOIN grupos g ON i.id_grupo = g.id 
            WHERE i.ciclo = :ciclo";
    $params = [':ciclo' => $ciclo];
    if ($grado) {
        $sql .= " AND g.grado = :grado";
        $params[':grado'] = $grado;
    }
    if ($grupo) {
        $sql .= " AND g.grupo = :grupo";
        $params[':grupo'] = $grupo;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_alumnos = count($alumnos);

    $pdf = new PDF_Alumnos('P', 'mm', 'Letter');
    $pdf->SetFont('Arial', '', 12);
    $pdf->AddPage();
    $pdf->Cell(0, 10, "Ciclo Escolar: $ciclo - Total Alumnos: $total_alumnos", 0, 1);

    $current_group = '';
    foreach ($alumnos as $alumno) {
        if ($alumno['grupo'] != $current_group) {
            if ($current_group) {
                $pdf->AddPage();
            }
            $current_group = $alumno['grupo'];
            $pdf->Cell(0, 10, "Grado: {$alumno['grado']} - Grupo: {$alumno['grupo']}", 0, 1, 'C');
        }
        $pdf->Cell(0, 10, "{$alumno['nombres']} {$alumno['primer_apellido']} {$alumno['segundo_apellido']} (CURP: {$alumno['curp']})", 0, 1);
    }

    $pdf->Output('D', 'reporte_alumnos.pdf');
}

require 'header.php';
?>

<main>
    <h2>Reporte de Alumnos</h2>
    <div class="form-container">
        <form method="POST" action="">
            <label>Ciclo Escolar:</label>
            <input type="text" name="ciclo" value="2025-2026" required><br>
            <label>Grado (opcional):</label>
            <select name="grado">
                <option value="">Todos</option>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select><br>
            <label>Grupo (opcional):</label>
            <input type="text" name="grupo"><br>
            <button type="submit">Generar Reporte PDF</button>
        </form>
    </div>
</main>
</body>
</html>