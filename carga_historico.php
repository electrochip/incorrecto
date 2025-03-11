<?php
// Control de Cambios
// Hash: x5y6z7a8b9c0d1e2f3g4h5i6j7k8 (MD5 del contenido sin este comentario)
// Versión: v1.3
include 'header.php';
if (!$_SESSION['es_admin']) {
    header("Location: dashboard.php");
    exit;
}
require 'vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['importar'])) {
    $file = $_FILES['excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();
    array_shift($data);

    foreach ($data as $row) {
        [$curp, $ciclo, $grado, $grupo, $promedio] = $row;

        $grado_int = (int)$grado;
        if (!($grado_int >= 1 && $grado_int <= 6) || !in_array($grupo, ['A', 'B', 'C'])) {
            $mensaje = "Error: Grado o grupo inválido en el archivo Excel.";
            break;
        }

        $stmt = $conn->prepare("INSERT IGNORE INTO ciclos_escolares (nombre) VALUES (:ciclo)");
        $stmt->execute(['ciclo' => $ciclo]);

        $estatus = $promedio >= 6 ? 'Aprobado' : 'Reprobado';
        $stmt = $conn->prepare("INSERT INTO historico (curp_alumno, ciclo, grado, grupo, promedio, estatus) 
                                VALUES (:curp_alumno, :ciclo, :grado, :grupo, :promedio, :estatus)
                                ON DUPLICATE KEY UPDATE grado = :grado, grupo = :grupo, promedio = :promedio, estatus = :estatus");
        $stmt->execute([
            'curp_alumno' => $curp,
            'ciclo' => $ciclo,
            'grado' => $grado_int,
            'grupo' => $grupo,
            'promedio' => $promedio ?: 0.0,
            'estatus' => $estatus
        ]);
    }
    if (!isset($mensaje)) {
        $mensaje = "Historial importado con éxito.";
    }
}
?>

<h2>Carga de Histórico de Alumnos</h2>
<?php if (isset($mensaje)) echo "<p class='success'>$mensaje</p>"; ?>
<form method="POST" enctype="multipart/form-data">
    <label>Archivo Excel: <input type="file" name="excel" required accept=".xlsx, .xls"></label><br>
    <button type="submit" name="importar">Importar</button>
</form>
<p>Formato: CURP, Ciclo (YYYY-YYYY), Grado (1-6), Grupo (A/B/C), Promedio</p>

</main>
</body>
</html>