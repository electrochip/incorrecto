<?php
session_start();
require_once 'header.php';
require_once 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

$historico = [];
$ciclos = [];
$grados = [];
$grupos = [];
$alumnos = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ciclo = $_POST['ciclo'] ?? '';
    $grado = $_POST['grado'] ?? '';
    $grupo = $_POST['grupo'] ?? '';
    $alumno_curp = $_POST['alumno'] ?? '';

    if (isset($_POST['filter'])) {
        $sql = "SELECT h.curp_alumno, a.nombres, a.primer_apellido, a.segundo_apellido, h.grado, h.calificacion 
                FROM historico h 
                JOIN alumnos a ON h.curp_alumno = a.curp 
                WHERE 1=1";
        $params = [];
        if ($ciclo) { $sql .= " AND h.ciclo = :ciclo"; $params[':ciclo'] = $ciclo; }
        if ($grado) { $sql .= " AND h.grado = :grado"; $params[':grado'] = $grado; }
        if ($grupo) { $sql .= " AND h.curp_alumno IN (SELECT i.curp_alumno FROM inscripciones i JOIN grupos g ON i.id_grupo = g.id WHERE g.grupo = :grupo)"; $params[':grupo'] = $grupo; }
        if ($alumno_curp) { $sql .= " AND h.curp_alumno = :curp"; $params[':curp'] = $alumno_curp; }
        $sql .= " ORDER BY h.curp_alumno, h.grado";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif (isset($_POST['export_pdf'])) {
        require 'fpdf/fpdf.php';
        class PDF_Historico extends FPDF {
            function Header() {
                $this->SetFont('Arial', 'B', 14);
                $this->Cell(0, 10, 'Histórico de Calificaciones - Modelo Enrique Laubscher', 0, 1, 'C');
                $this->Ln(5);
            }
            function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C'); }
        }
        $pdf = new PDF_Historico('L', 'mm', 'A4');
        $pdf->SetFont('Arial', '', 10);
        $pdf->AddPage();
        $pdf->Cell(10, 10, 'No.', 1);
        $pdf->Cell(30, 10, 'CURP', 1);
        $pdf->Cell(50, 10, 'Nombre', 1);
        for ($g = 1; $g <= 6; $g++) $pdf->Cell(10, 10, $g . '°', 1);
        $pdf->Cell(20, 10, 'Promedio', 1);
        $pdf->Ln();

        $current_curp = '';
        $grades = [];
        $i = 1;
        foreach ($historico as $row) {
            if ($current_curp != $row['curp_alumno']) {
                if ($current_curp) {
                    $pdf->Cell(10, 10, $i++, 1);
                    $pdf->Cell(30, 10, $current_curp, 1);
                    $pdf->Cell(50, 10, $name, 1);
                    for ($g = 1; $g <= 6; $g++) $pdf->Cell(10, 10, isset($grades[$g]) ? number_format($grades[$g], 1) : '', 1);
                    $pdf->Cell(20, 10, $promedio ? number_format($promedio, 1) : '', 1);
                    $pdf->Ln();
                }
                $current_curp = $row['curp_alumno'];
                $name = $row['primer_apellido'] . ' / ' . $row['segundo_apellido'] . ' * ' . $row['nombres'];
                $grades = [];
                $promedio = 0;
                $count = 0;
            }
            $grades[$row['grado']] = $row['calificacion'];
            $promedio += $row['calificacion'];
            $count++;
        }
        if ($current_curp) {
            $promedio = $count ? $promedio / $count : 0;
            $pdf->Cell(10, 10, $i, 1);
            $pdf->Cell(30, 10, $current_curp, 1);
            $pdf->Cell(50, 10, $name, 1);
            for ($g = 1; $g <= 6; $g++) $pdf->Cell(10, 10, isset($grades[$g]) ? number_format($grades[$g], 1) : '', 1);
            $pdf->Cell(20, 10, number_format($promedio, 1), 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'historico_' . date('YmdHis') . '.pdf');
        exit();
    }
}

$sql_ciclos = "SELECT DISTINCT ciclo FROM historico ORDER BY ciclo DESC";
$stmt_ciclos = $conn->query($sql_ciclos);
$ciclos = $stmt_ciclos->fetchAll(PDO::FETCH_COLUMN);

if (isset($_POST['ciclo']) && $_POST['ciclo']) {
    $sql_grados = "SELECT DISTINCT grado FROM historico WHERE ciclo = :ciclo ORDER BY grado";
    $stmt_grados = $conn->prepare($sql_grados);
    $stmt_grados->execute([':ciclo' => $_POST['ciclo']]);
    $grados = $stmt_grados->fetchAll(PDO::FETCH_COLUMN);
}

if (isset($_POST['ciclo']) && $_POST['ciclo'] && isset($_POST['grado']) && $_POST['grado']) {
    $sql_grupos = "SELECT DISTINCT g.grupo FROM grupos g JOIN inscripciones i ON g.id = i.id_grupo WHERE g.ciclo = :ciclo AND g.grado = :grado";
    $stmt_grupos = $conn->prepare($sql_grupos);
    $stmt_grupos->execute([':ciclo' => $_POST['ciclo'], ':grado' => $_POST['grado']]);
    $grupos = $stmt_grupos->fetchAll(PDO::FETCH_COLUMN);
}

if (isset($_POST['ciclo']) && $_POST['ciclo'] && isset($_POST['grado']) && $_POST['grado'] && isset($_POST['grupo']) && $_POST['grupo']) {
    $sql_alumnos = "SELECT a.curp, a.nombres, a.primer_apellido, a.segundo_apellido FROM alumnos a JOIN inscripciones i ON a.curp = i.curp_alumno JOIN grupos g ON i.id_grupo = g.id WHERE g.ciclo = :ciclo AND g.grado = :grado AND g.grupo = :grupo";
    $stmt_alumnos = $conn->prepare($sql_alumnos);
    $stmt_alumnos->execute([':ciclo' => $_POST['ciclo'], ':grado' => $_POST['grado'], ':grupo' => $_POST['grupo']]);
    $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-history"></i> Imprimir Histórico</h2>
    <div class="card">
        <div class="card-header">
            <h3>Filtros</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label for="ciclo" class="form-label">Ciclo Escolar:</label>
                    <select name="ciclo" id="ciclo" class="form-select" onchange="this.form.submit()" required>
                        <option value="">Seleccione un ciclo</option>
                        <?php foreach ($ciclos as $ciclo): ?>
                            <option value="<?php echo htmlspecialchars($ciclo); ?>" <?php echo isset($_POST['ciclo']) && $_POST['ciclo'] == $ciclo ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ciclo); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="grado" class="form-label">Grado:</label>
                    <select name="grado" id="grado" class="form-select" onchange="this.form.submit()" <?php echo !$ciclos || !$_POST['ciclo'] ? 'disabled' : ''; ?>>
                        <option value="">Seleccione un grado</option>
                        <?php foreach ($grados as $grado): ?>
                            <option value="<?php echo htmlspecialchars($grado); ?>" <?php echo isset($_POST['grado']) && $_POST['grado'] == $grado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($grado); ?>°
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="grupo" class="form-label">Grupo:</label>
                    <select name="grupo" id="grupo" class="form-select" onchange="this.form.submit()" <?php echo !$grados || !$_POST['grado'] ? 'disabled' : ''; ?>>
                        <option value="">Seleccione un grupo</option>
                        <?php foreach ($grupos as $grp): ?>
                            <option value="<?php echo htmlspecialchars($grp); ?>" <?php echo isset($_POST['grupo']) && $_POST['grupo'] == $grp ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($grp); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="alumno" class="form-label">Alumno:</label>
                    <select name="alumno" id="alumno" class="form-select" onchange="this.form.submit()" <?php echo !$grupos || !$_POST['grupo'] ? 'disabled' : ''; ?>>
                        <option value="">Seleccione un alumno</option>
                        <?php foreach ($alumnos as $alumno): ?>
                            <option value="<?php echo htmlspecialchars($alumno['curp']); ?>" <?php echo isset($_POST['alumno']) && $_POST['alumno'] == $alumno['curp'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($alumno['primer_apellido'] . ' / ' . $alumno['segundo_apellido'] . ' * ' . $alumno['nombres']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" name="filter" class="btn btn-primary">Filtrar</button>
                    <?php if ($historico): ?>
                        <button type="submit" name="export_pdf" class="btn btn-primary">Exportar a PDF</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if ($historico): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Resultados del Histórico</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>CURP</th>
                            <th>Nombre</th>
                            <?php for ($g = 1; $g <= 6; $g++): ?>
                                <th><?php echo $g; ?>°</th>
                            <?php endfor; ?>
                            <th>Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $current_curp = '';
                        $grades = [];
                        $i = 1;
                        foreach ($historico as $row) {
                            if ($current_curp != $row['curp_alumno']) {
                                if ($current_curp) {
                                    echo "<tr>";
                                    echo "<td>" . $i++ . "</td>";
                                    echo "<td>" . htmlspecialchars($current_curp) . "</td>";
                                    echo "<td>" . htmlspecialchars($name) . "</td>";
                                    $promedio = array_sum($grades) / count($grades);
                                    for ($g = 1; $g <= 6; $g++) echo "<td>" . (isset($grades[$g]) ? number_format($grades[$g], 1) : '') . "</td>";
                                    echo "<td>" . number_format($promedio, 1) . "</td>";
                                    echo "</tr>";
                                }
                                $current_curp = $row['curp_alumno'];
                                $name = $row['primer_apellido'] . ' / ' . $row['segundo_apellido'] . ' * ' . $row['nombres'];
                                $grades = [];
                            }
                            $grades[$row['grado']] = $row['calificacion'];
                        }
                        if ($current_curp) {
                            echo "<tr>";
                            echo "<td>" . $i . "</td>";
                            echo "<td>" . htmlspecialchars($current_curp) . "</td>";
                            echo "<td>" . htmlspecialchars($name) . "</td>";
                            $promedio = array_sum($grades) / count($grades);
                            for ($g = 1; $g <= 6; $g++) echo "<td>" . (isset($grades[$g]) ? number_format($grades[$g], 1) : '') . "</td>";
                            echo "<td>" . number_format($promedio, 1) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>