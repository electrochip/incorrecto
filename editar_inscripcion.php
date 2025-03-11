<?php
// Control de Cambios
// Hash: d6e7f8g9h0i1j2k3l4m5n6o7p8q9 (MD5 del contenido sin este comentario)
// Versión: v2.2
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

require 'database.php';

$id = $_GET['id'];
$sql_inscripcion = "SELECT i.*, a.nombres, a.primer_apellido, a.segundo_apellido, g.grado, g.grupo 
                   FROM inscripciones i 
                   JOIN alumnos a ON i.curp_alumno = a.curp 
                   JOIN grupos g ON i.id_grupo = g.id 
                   WHERE i.id = :id";
$stmt_inscripcion = $conn->prepare($sql_inscripcion);
$stmt_inscripcion->execute([':id' => $id]);
$inscripcion = $stmt_inscripcion->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_grupo = $_POST['id_grupo'];
    $sql_update = "UPDATE inscripciones SET id_grupo = :id_grupo WHERE id = :id";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->execute([':id_grupo' => $id_grupo, ':id' => $id]);
    header("Location: inscripcion.php");
    exit();
}

$sql_grupos = "SELECT g.id, g.grado, g.grupo, d.nombre, d.primer_apellido, g.capacidad_maxima, COUNT(i.id) as inscritos 
               FROM grupos g 
               LEFT JOIN docentes d ON g.id_docente = d.id 
               LEFT JOIN inscripciones i ON g.id = i.id_grupo 
               WHERE g.ciclo = '2025-2026' AND g.grado = :grado 
               GROUP BY g.id 
               HAVING inscritos < g.capacidad_maxima";
$stmt_grupos = $conn->prepare($sql_grupos);
$stmt_grupos->execute([':grado' => $inscripcion['grado']]);
$grupos_disponibles = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';
?>

<main>
    <h2>Editar Inscripción</h2>
    <div class="form-container">
        <p>Alumno: <?php echo $inscripcion['nombres'] . ' ' . $inscripcion['primer_apellido'] . ' ' . $inscripcion['segundo_apellido']; ?></p>
        <p>Folio: <?php echo $inscripcion['folio']; ?></p>
        <form method="POST" action="">
            <label>Grupo:</label>
            <select name="id_grupo" required>
                <?php foreach ($grupos_disponibles as $grupo): ?>
                    <option value="<?php echo $grupo['id']; ?>" <?php echo $grupo['id'] == $inscripcion['id_grupo'] ? 'selected' : ''; ?>>
                        Grado <?php echo $grupo['grado']; ?> Grupo <?php echo $grupo['grupo']; ?> - 
                        <?php echo $grupo['nombre'] . ' ' . $grupo['primer_apellido']; ?> 
                        (Cupo: <?php echo $grupo['capacidad_maxima'] - $grupo['inscritos']; ?>)
                    </option>
                <?php endforeach; ?>
            </select><br>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</main>
</body>
</html>