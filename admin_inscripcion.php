<?php
// Control de Cambios
// Hash: m5n6o7p8q9r0s1t2u3v4w5x6y7z8 (MD5 del contenido sin este comentario)
// Versión: v2.3
require 'session_check.php';
require 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

$alumno = null;
$grupos_disponibles = [];
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $curp = strtoupper($_POST['curp']);
        $sql_alumno = "SELECT * FROM alumnos WHERE curp = :curp";
        $stmt_alumno = $conn->prepare($sql_alumno);
        $stmt_alumno->execute([':curp' => $curp]);
        $alumno = $stmt_alumno->fetch(PDO::FETCH_ASSOC);

        if ($alumno) {
            $sql_historico = "SELECT grado FROM historico WHERE curp_alumno = :curp ORDER BY ciclo DESC LIMIT 1";
            $stmt_historico = $conn->prepare($sql_historico);
            $stmt_historico->execute([':curp' => $curp]);
            $ultimo_grado = $stmt_historico->fetchColumn();
            $grado_siguiente = ($ultimo_grado && $ultimo_grado < 6) || ($ultimo_grado == 6 && $alumno['estatus'] == 'reprobado') ? $ultimo_grado + 1 : null;

            if ($grado_siguiente) {
                $sql_grupos = "SELECT g.id, g.grado, g.grupo, d.nombre, d.primer_apellido, g.capacidad_maxima, COUNT(i.id) as inscritos 
                               FROM grupos g 
                               LEFT JOIN docentes d ON g.id_docente = d.id 
                               LEFT JOIN inscripciones i ON g.id = i.id_grupo 
                               WHERE g.ciclo = '2025-2026' AND g.grado = :grado 
                               GROUP BY g.id";
                $stmt_grupos = $conn->prepare($sql_grupos);
                $stmt_grupos->execute([':grado' => $grado_siguiente]);
                $grupos_disponibles = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $error = "No se encontró un alumno con ese CURP.";
        }
    } elseif (isset($_POST['proceed'])) {
        $curp = $_POST['curp'];
        $id_grupo = $_POST['id_grupo'];
        $sql_check = "SELECT id FROM inscripciones WHERE curp_alumno = :curp AND ciclo = '2025-2026'";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([':curp' => $curp]);
        if ($stmt_check->rowCount() == 0) {
            $folio = "INS" . date("YmdHis") . rand(1000, 9999);
            $sql_insert = "INSERT INTO inscripciones (curp_alumno, id_grupo, ciclo, folio, fecha_inscripcion) 
                           VALUES (:curp, :id_grupo, '2025-2026', :folio, NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->execute([':curp' => $curp, ':id_grupo' => $id_grupo, ':folio' => $folio]);
            $success = "Inscripción exitosa. Folio: $folio";
        } else {
            $error = "El alumno ya está inscrito para el ciclo 2025-2026.";
        }
    }
}

require 'header.php';
?>

<main>
    <h2>Inscribir Alumno (Administrador)</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

    <div class="form-container">
        <h3>Buscar Alumno</h3>
        <form method="POST" action="">
            <label>CURP:</label>
            <input type="text" name="curp" required><br>
            <button type="submit" name="search">Buscar</button>
        </form>

        <?php if ($alumno): ?>
            <h3>Datos del Alumno</h3>
            <p>CURP: <?php echo $alumno['curp']; ?></p>
            <p>Nombre: <?php echo $alumno['nombres'] . ' ' . $alumno['primer_apellido'] . ' ' . $alumno['segundo_apellido']; ?></p>
            <p>Tutor: <?php echo $alumno['tutor']; ?></p>
            <p>Teléfono: <?php echo $alumno['telefono']; ?></p>

            <?php if (count($grupos_disponibles) > 0): ?>
                <h3>Seleccionar Grupo</h3>
                <form method="POST" action="">
                    <input type="hidden" name="curp" value="<?php echo $alumno['curp']; ?>">
                    <label>Grupo:</label>
                    <select name="id_grupo" required>
                        <?php foreach ($grupos_disponibles as $grupo): ?>
                            <option value="<?php echo $grupo['id']; ?>">
                                Grado <?php echo $grupo['grado']; ?> Grupo <?php echo $grupo['grupo']; ?> - 
                                <?php echo $grupo['nombre'] . ' ' . $grupo['primer_apellido']; ?> 
                                (Cupo: <?php echo $grupo['capacidad_maxima'] - $grupo['inscritos']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select><br>
                    <button type="submit" name="proceed">Inscribir</button>
                </form>
            <?php else: ?>
                <p class="error">No hay grupos disponibles para el grado correspondiente.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>