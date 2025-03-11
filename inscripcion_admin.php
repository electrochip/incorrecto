<?php
session_start();
require_once 'header.php';
require_once 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
$alumno = null;
$inscripcion = null;
$grupos = [];
$search_type = 'curp';
$search_value = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_type = $_POST['search_type'];
    $search_value = strtoupper($_POST['search_value']);

    if (isset($_POST['search'])) {
        $sql_alumno = $search_type == 'curp' ? "SELECT * FROM alumnos WHERE curp = :value" : "SELECT a.* FROM alumnos a JOIN inscripciones i ON a.curp = i.curp_alumno WHERE i.folio = :value AND i.ciclo = '2025-2026'";
        $stmt_alumno = $conn->prepare($sql_alumno);
        $stmt_alumno->execute([':value' => $search_value]);
        $alumno = $stmt_alumno->fetch(PDO::FETCH_ASSOC);

        if ($alumno) {
            $sql_inscripcion = "SELECT i.folio, i.id_grupo, g.grado, g.grupo FROM inscripciones i JOIN grupos g ON i.id_grupo = g.id WHERE i.curp_alumno = :curp AND i.ciclo = '2025-2026'";
            $stmt_inscripcion = $conn->prepare($sql_inscripcion);
            $stmt_inscripcion->execute([':curp' => $alumno['curp']]);
            $inscripcion = $stmt_inscripcion->fetch(PDO::FETCH_ASSOC);

            $sql_historico = "SELECT grado FROM historico WHERE curp_alumno = :curp ORDER BY ciclo DESC LIMIT 1";
            $stmt_historico = $conn->prepare($sql_historico);
            $stmt_historico->execute([':curp' => $alumno['curp']]);
            $ultimo_grado = $stmt_historico->fetchColumn();
            $grado_siguiente = ($ultimo_grado && $ultimo_grado < 6) ? $ultimo_grado + 1 : 1;

            $sql_grupos = "SELECT g.id, g.grado, g.grupo, d.nombre, d.primer_apellido FROM grupos g LEFT JOIN docentes d ON g.id_docente = d.id WHERE g.ciclo = '2025-2026' AND g.grado = :grado";
            $stmt_grupos = $conn->prepare($sql_grupos);
            $stmt_grupos->execute([':grado' => $grado_siguiente]);
            $grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);
        }
    } elseif (isset($_POST['inscribir'])) {
        $curp = $alumno['curp'];
        $id_grupo = $_POST['id_grupo'];
        $sql_check = "DELETE FROM inscripciones WHERE curp_alumno = :curp AND ciclo = '2025-2026'";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([':curp' => $curp]);
        $folio = "INS" . date("YmdHis") . rand(1000, 9999);
        $sql_insert = "INSERT INTO inscripciones (curp_alumno, id_grupo, ciclo, folio, fecha_inscripcion) VALUES (:curp, :id_grupo, '2025-2026', :folio, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([':curp' => $curp, ':id_grupo' => $id_grupo, ':folio' => $folio]);
        $success = "Inscripción exitosa. Folio: $folio";
        $inscripcion = ['folio' => $folio, 'id_grupo' => $id_grupo];
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-user-shield"></i> Inscripción por Administrador</h2>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="card">
        <div class="card-header">
            <h3>Buscar Alumno</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label for="search_type" class="form-label">Buscar por:</label>
                    <select name="search_type" id="search_type" class="form-select">
                        <option value="curp" <?php echo $search_type == 'curp' ? 'selected' : ''; ?>>CURP</option>
                        <option value="folio" <?php echo $search_type == 'folio' ? 'selected' : ''; ?>>Folio</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search_value" class="form-label">Valor:</label>
                    <input type="text" name="search_value" id="search_value" class="form-control" value="<?php echo htmlspecialchars($search_value); ?>" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="search" class="btn btn-primary mt-2"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($alumno): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Datos del Alumno</h3>
            </div>
            <div class="card-body">
                <p><strong>CURP:</strong> <?php echo htmlspecialchars($alumno['curp']); ?></p>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($alumno['nombres'] . ' ' . $alumno['primer_apellido'] . ' ' . $alumno['segundo_apellido']); ?></p>
                <p><strong>Tutor:</strong> <?php echo htmlspecialchars($alumno['tutor']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($alumno['telefono']); ?></p>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>Inscribir Alumno</h3>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="search_value" value="<?php echo htmlspecialchars($search_value); ?>">
                    <div class="col-md-6">
                        <label for="id_grupo" class="form-label">Seleccionar Grupo:</label>
                        <select name="id_grupo" id="id_grupo" class="form-select" required>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo $grupo['id']; ?>">
                                    Grado <?php echo htmlspecialchars($grupo['grado']); ?> Grupo <?php echo htmlspecialchars($grupo['grupo']); ?> - 
                                    <?php echo htmlspecialchars($grupo['nombre'] . ' ' . $grupo['primer_apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" name="inscribir" class="btn btn-primary mt-2"><i class="fas fa-check"></i> Inscribir</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($inscripcion): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Inscripción Actual</h3>
                </div>
                <div class="card-body">
                    <p><strong>Grado:</strong> <?php echo htmlspecialchars($inscripcion['grado']); ?></p>
                    <p><strong>Grupo:</strong> <?php echo htmlspecialchars($inscripcion['grupo']); ?></p>
                    <p><strong>Folio:</strong> <?php echo htmlspecialchars($inscripcion['folio']); ?></p>
                    <a href="generate_pdf.php?folio=<?php echo urlencode($inscripcion['folio']); ?>" class="btn btn-primary"><i class="fas fa-print"></i> Imprimir Comprobante</a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>