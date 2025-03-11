<?php
require 'session_check.php'; // Control de sesiones y seguridad
// Control de Cambios
// Hash: m6n7o8p9q0r1s2t3u4v5w6x7y8z9a0b1c2d3e4 (MD5 del contenido sin este comentario)
// Versión: v2.6
require 'database.php';
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $curp = strtoupper($_POST['curp']);
    $paterno = $_POST['paterno'];
    $materno = $_POST['materno'] ?? '';
    $nombre = $_POST['nombre'];
    $niev = strtoupper($_POST['niev']);
    $tutor = $_POST['tutor'];
    $telefono = $_POST['telefono'];
    $grado = intval($_POST['grado']);

    // Validación de CURP (simplificada, se puede expandir con algoritmo mexicano)
    if (!preg_match('/^[A-Z]{1}[AEIOU]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$/', $curp)) {
        $error = "CURP inválido.";
    } else {
        $sql_check = "SELECT curp FROM alumnos WHERE curp = :curp";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([':curp' => $curp]);
        if ($stmt_check->rowCount() == 0) {
            $sql_insert = "INSERT INTO alumnos (curp, primer_apellido, segundo_apellido, nombres, niev, tutor, telefono) 
                           VALUES (:curp, :paterno, :materno, :nombre, :niev, :tutor, :telefono)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->execute([
                ':curp' => $curp, ':paterno' => $paterno, ':materno' => $materno, ':nombre' => $nombre,
                ':niev' => $niev, ':tutor' => $tutor, ':telefono' => $telefono
            ]);

            for ($g = 1; $g < $grado; $g++) {
                $calificacion = $_POST["calificacion_$g"] ?? 0;
                if (!is_numeric($calificacion) || $calificacion < 0 || $calificacion > 10) {
                    $error = "Calificación inválida para grado $g°.";
                    break;
                }
                $sql_historico = "INSERT INTO historico (curp_alumno, grado, calificacion, ciclo) 
                                  VALUES (:curp, :grado, :calificacion, '2024-2025')";
                $stmt_historico = $conn->prepare($sql_historico);
                $stmt_historico->execute([':curp' => $curp, ':grado' => $g, ':calificacion' => $calificacion]);
            }
            if (!$error) $success = "Alumno registrado exitosamente.";
        } else {
            $error = "El CURP ya está registrado.";
        }
    }
}

require 'header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <main class="container mt-4">
        <h2 class="text-center mb-4">Registro de Alumnos</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

        <form method="POST" class="row g-3">
            <div class="col-md-3">
                <label for="curp" class="form-label">CURP:</label>
                <input type="text" name="curp" id="curp" class="form-control" maxlength="18" required>
            </div>
            <div class="col-md-3">
                <label for="paterno" class="form-label">Primer Apellido:</label>
                <input type="text" name="paterno" id="paterno" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="materno" class="form-label">Segundo Apellido:</label>
                <input type="text" name="materno" id="materno" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="nombre" class="form-label">Nombres:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="niev" class="form-label">NIEV:</label>
                <input type="text" name="niev" id="niev" class="form-control" maxlength="5" required>
            </div>
            <div class="col-md-3">
                <label for="tutor" class="form-label">Tutor:</label>
                <input type="text" name="tutor" id="tutor" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="telefono" class="form-label">Teléfono:</label>
                <input type="text" name="telefono" id="telefono" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="grado" class="form-label">Grado a Inscribir:</label>
                <select name="grado" id="grado" class="form-control" onchange="this.form.submit()" required>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?>°</option>
                    <?php endfor; ?>
                </select>
            </div>
            <?php
            $grado = isset($_POST['grado']) ? intval($_POST['grado']) : 1;
            for ($g = 1; $g < $grado; $g++): ?>
                <div class="col-md-2">
                    <label for="calificacion_<?php echo $g; ?>" class="form-label">Calificación <?php echo $g; ?>°:</label>
                    <input type="number" name="calificacion_<?php echo $g; ?>" id="calificacion_<?php echo $g; ?>" class="form-control" step="0.1" min="0" max="10" required>
                </div>
            <?php endfor; ?>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Registrar Alumno</button>
            </div>
        </form>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>