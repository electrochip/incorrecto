<?php
// Control de Cambios
// Hash: h4i5j6k7l8m9n0o1p2q3r4s5t6u7 (MD5 del contenido sin este comentario)
// Versión: v1.11
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

require 'database.php';
require 'SimpleExcel/SimpleExcel.php';

use SimpleExcel\SimpleExcel;

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['excel_file'])) {
    $file_tmp = $_FILES['excel_file']['tmp_name'];
    $file_path = '/home/vol9_6/infinityfree.com/if0_38403974/htdocs/uploads/temp_alumnos.csv';

    if (!file_exists(dirname($file_path))) {
        mkdir(dirname($file_path), 0755, true);
    }
    if (move_uploaded_file($file_tmp, $file_path)) {
        try {
            $excel = new SimpleExcel('csv');
            $excel->parser->loadFile($file_path);
            $data = $excel->parser->getField();
            array_shift($data); // Ignora encabezado

            if (!empty($data)) {
                $conn->beginTransaction();
                $inserted = 0;
                foreach ($data as $row) {
                    if (count($row) >= 7) {
                        $curp = strtoupper($row[0]);
                        $paterno = $row[1];
                        $materno = $row[2] ?? '';
                        $nombre = $row[3];
                        $niev = strtoupper($row[4]);
                        $grado = (int)$row[5];
                        $grupo = $row[6];

                        $sql_check = "SELECT curp FROM alumnos WHERE curp = :curp OR niev = :niev";
                        $stmt_check = $conn->prepare($sql_check);
                        $stmt_check->execute([':curp' => $curp, ':niev' => $niev]);
                        if ($stmt_check->rowCount() == 0) {
                            $sql_insert = "INSERT INTO alumnos (curp, primer_apellido, segundo_apellido, nombres, niev) 
                                           VALUES (:curp, :paterno, :materno, :nombre, :niev)";
                            $stmt_insert = $conn->prepare($sql_insert);
                            $stmt_insert->execute([
                                ':curp' => $curp,
                                ':paterno' => $paterno,
                                ':materno' => $materno,
                                ':nombre' => $nombre,
                                ':niev' => $niev
                            ]);

                            $ciclo = '2024-2025';
                            $sql_historico = "INSERT INTO historico (curp_alumno, ciclo, grado, grupo, promedio, estatus) 
                                              VALUES (:curp, :ciclo, :grado, :grupo, 0.0, 'Aprobado')";
                            $stmt_historico = $conn->prepare($sql_historico);
                            $stmt_historico->execute([
                                ':curp' => $curp,
                                ':ciclo' => $ciclo,
                                ':grado' => $grado,
                                ':grupo' => $grupo
                            ]);
                            $inserted++;
                        }
                    }
                }
                $conn->commit();
                $success = "Se importaron $inserted alumnos exitosamente.";
            } else {
                $error = "No se encontraron datos válidos en el archivo CSV.";
            }
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = "Error al procesar el archivo: " . $e->getMessage();
        }
    } else {
        $error = "Error al mover el archivo subido.";
    }

    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

require 'header.php';
?>

<main>
    <h2>Importar Alumnos desde Excel</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Archivo Excel (CSV: CURP, PATERNO, MATERNO, NOMBRE, NIEV, GRADO, GRUPO):</label>
        <input type="file" name="excel_file" accept=".csv" required>
        <button type="submit">Importar</button>
    </form>
</main>
</body>
</html>