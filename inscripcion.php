<?php
session_start();
require_once 'header.php';
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$curp = $_SESSION['user_id'];
$is_admin = $_SESSION['es_admin'] ?? false;
$error = '';
$success = '';
$inscribed = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_admin && !$inscribed) {
    if (isset($_POST['proceed'])) {
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
            $inscribed = true;
        } else {
            $success = "Ya estás inscrito. Revisa tu folio.";
            $inscribed = true;
        }
    }
}

$sql_alumno = "SELECT * FROM alumnos WHERE curp = :curp";
$stmt_alumno = $conn->prepare($sql_alumno);
$stmt_alumno->execute([':curp' => $curp]);
$alumno = $stmt_alumno->fetch(PDO::FETCH_ASSOC);

$sql_inscripcion = "SELECT i.folio, g.grado, g.grupo, d.nombre, d.primer_apellido 
                   FROM inscripciones i 
                   JOIN grupos g ON i.id_grupo = g.id 
                   JOIN docentes d ON g.id_docente = d.id 
                   WHERE i.curp_alumno = :curp AND i.ciclo = '2025-2026'";
$stmt_inscripcion = $conn->prepare($sql_inscripcion);
$stmt_inscripcion->execute([':curp' => $curp]);
$inscripcion = $stmt_inscripcion->fetch(PDO::FETCH_ASSOC);
if ($inscripcion) $inscribed = true;

$sql_historico = "SELECT grado FROM historico WHERE curp_alumno = :curp ORDER BY ciclo DESC LIMIT 1";
$stmt_historico = $conn->prepare($sql_historico);
$stmt_historico->execute([':curp' => $curp]);
$ultimo_grado = $stmt_historico->fetchColumn();
$grado_siguiente = ($ultimo_grado && $ultimo_grado < 6) || ($ultimo_grado == 6 && $alumno['estatus'] == 'reprobado') ? $ultimo_grado + 1 : 1;

$grupos_disponibles = [];
if ($grado_siguiente && !$inscribed) {
    $sql_grupos = "SELECT g.id, g.grado, g.grupo, d.nombre, d.primer_apellido 
                   FROM grupos g 
                   LEFT JOIN docentes d ON g.id_docente = d.id 
                   WHERE g.ciclo = '2025-2026' AND g.grado = :grado";
    $stmt_grupos = $conn->prepare($sql_grupos);
    $stmt_grupos->execute([':grado' => $grado_siguiente]);
    $grupos_disponibles = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-user-plus"></i> Inscripción de Alumnos</h2>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <?php if ($alumno): ?>
        <div class="card <?php echo $inscribed ? 'disabled-form' : ''; ?>">
            <div class="card-header">
                <h3>Datos del Alumno</h3>
            </div>
            <div class="card-body">
                <?php if ($inscribed): ?>
                    <div class="card-text">
                        <p><strong>CURP:</strong> <?php echo htmlspecialchars($alumno['curp']); ?></p>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($alumno['nombres'] . ' ' . $alumno['primer_apellido'] . ' ' . $alumno['segundo_apellido']); ?></p>
                        <p><strong>Tutor:</strong> <?php echo htmlspecialchars($alumno['tutor']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($alumno['telefono']); ?></p>
                    </div>
                    <div class="card-header mt-3">
                        <h4>Inscripción Actual</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Grado:</strong> <?php echo htmlspecialchars($inscripcion['grado']); ?></p>
                        <p><strong>Grupo:</strong> <?php echo htmlspecialchars($inscripcion['grupo']); ?></p>
                        <p><strong>Docente:</strong> <?php echo htmlspecialchars($inscripcion['nombre'] . ' ' . $inscripcion['primer_apellido']); ?></p>
                        <p><strong>Folio:</strong> <?php echo htmlspecialchars($inscripcion['folio']); ?></p>
                        <a href="generate_pdf.php?folio=<?php echo urlencode($inscripcion['folio']); ?>" class="btn btn-primary"><i class="fas fa-print"></i> Imprimir Comprobante</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-6">
                            <label for="id_grupo" class="form-label">Grupo Disponible:</label>
                            <select name="id_grupo" id="id_grupo" class="form-select" required>
                                <?php foreach ($grupos_disponibles as $grupo): ?>
                                    <option value="<?php echo $grupo['id']; ?>">
                                        Grado <?php echo htmlspecialchars($grupo['grado']); ?> Grupo <?php echo htmlspecialchars($grupo['grupo']); ?> - 
                                        <?php echo htmlspecialchars($grupo['nombre'] . ' ' . $grupo['primer_apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" name="proceed" class="btn btn-primary mt-2"><i class="fas fa-check"></i> Proceder a Inscripción</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">No se encontraron datos para este alumno.</div>
    <?php endif; ?>
</div>