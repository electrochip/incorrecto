<?php
// Control de Cambios
// Hash: h0i1j2k3l4m5n6o7p8q9r0s1t2u3 (MD5 del contenido sin este comentario)
// Versión: v2.2
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_asignacion'])) {
        $id_docente = $_POST['id_docente'];
        $grado = $_POST['grado'];
        $grupo = $_POST['grupo'];
        $ciclo = $_POST['ciclo'];
        $capacidad_maxima = $_POST['capacidad_maxima'];

        $sql_insert = "INSERT INTO grupos (id_docente, grado, grupo, ciclo, capacidad_maxima) 
                       VALUES (:id_docente, :grado, :grupo, :ciclo, :capacidad_maxima)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([
            ':id_docente' => $id_docente,
            ':grado' => $grado,
            ':grupo' => $grupo,
            ':ciclo' => $ciclo,
            ':capacidad_maxima' => $capacidad_maxima
        ]);
    } elseif (isset($_POST['edit_asignacion'])) {
        $id = $_POST['id'];
        $id_docente = $_POST['id_docente'];
        $grado = $_POST['grado'];
        $grupo = $_POST['grupo'];
        $ciclo = $_POST['ciclo'];
        $capacidad_maxima = $_POST['capacidad_maxima'];

        $sql_update = "UPDATE grupos SET id_docente = :id_docente, grado = :grado, grupo = :grupo, 
                       ciclo = :ciclo, capacidad_maxima = :capacidad_maxima WHERE id = :id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([
            ':id_docente' => $id_docente,
            ':grado' => $grado,
            ':grupo' => $grupo,
            ':ciclo' => $ciclo,
            ':capacidad_maxima' => $capacidad_maxima,
            ':id' => $id
        ]);
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql_delete = "DELETE FROM grupos WHERE id = :id";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->execute([':id' => $id]);
    header("Location: asignaciones.php");
    exit();
}

$edit_asignacion = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql_edit = "SELECT * FROM grupos WHERE id = :id";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->execute([':id' => $id]);
    $edit_asignacion = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

$sql_docentes = "SELECT * FROM docentes";
$stmt_docentes = $conn->prepare($sql_docentes);
$stmt_docentes->execute();
$docentes = $stmt_docentes->fetchAll(PDO::FETCH_ASSOC);

$sql_grupos = "SELECT g.*, d.nombre, d.primer_apellido 
               FROM grupos g 
               JOIN docentes d ON g.id_docente = d.id";
$stmt_grupos = $conn->prepare($sql_grupos);
$stmt_grupos->execute();
$grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';
?>

<main>
    <h2>Asignaciones de Docentes</h2>
    <div class="form-container">
        <h3><?php echo $edit_asignacion ? 'Editar Asignación' : 'Agregar Asignación'; ?></h3>
        <form method="POST" action="">
            <?php if ($edit_asignacion): ?>
                <input type="hidden" name="id" value="<?php echo $edit_asignacion['id']; ?>">
            <?php endif; ?>
            <label>Docente:</label>
            <select name="id_docente" required>
                <?php foreach ($docentes as $docente): ?>
                    <option value="<?php echo $docente['id']; ?>" <?php echo ($edit_asignacion && $edit_asignacion['id_docente'] == $docente['id']) ? 'selected' : ''; ?>>
                        <?php echo $docente['nombre'] . ' ' . $docente['primer_apellido'] . ' ' . $docente['segundo_apellido']; ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <label>Grado:</label>
            <select name="grado" required>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($edit_asignacion && $edit_asignacion['grado'] == $i) ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select><br>
            <label>Grupo:</label>
            <input type="text" name="grupo" value="<?php echo $edit_asignacion['grupo'] ?? ''; ?>" required><br>
            <label>Ciclo Escolar:</label>
            <input type="text" name="ciclo" value="<?php echo $edit_asignacion['ciclo'] ?? '2025-2026'; ?>" required><br>
            <label>Capacidad Máxima:</label>
            <input type="number" name="capacidad_maxima" value="<?php echo $edit_asignacion['capacidad_maxima'] ?? 30; ?>" required><br>
            <button type="submit" name="<?php echo $edit_asignacion ? 'edit_asignacion' : 'add_asignacion'; ?>">
                <?php echo $edit_asignacion ? 'Guardar Cambios' : 'Agregar Asignación'; ?>
            </button>
        </form>
    </div>

    <h3>Lista de Asignaciones</h3>
    <table>
        <thead>
            <tr>
                <th>Docente</th>
                <th>Grado</th>
                <th>Grupo</th>
                <th>Ciclo</th>
                <th>Capacidad Máxima</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grupos as $grupo): ?>
                <tr>
                    <td><?php echo $grupo['nombre'] . ' ' . $grupo['primer_apellido']; ?></td>
                    <td><?php echo $grupo['grado']; ?></td>
                    <td><?php echo $grupo['grupo']; ?></td>
                    <td><?php echo $grupo['ciclo']; ?></td>
                    <td><?php echo $grupo['capacidad_maxima']; ?></td>
                    <td>
                        <a href="asignaciones.php?edit=<?php echo $grupo['id']; ?>"><i class="fas fa-edit"></i> Editar</a>
                        <a href="asignaciones.php?delete=<?php echo $grupo['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta asignación?');"><i class="fas fa-trash"></i> Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>