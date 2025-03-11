<?php
session_start();
require_once 'header.php';
require_once 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $curp = strtoupper($_POST['curp']);
        $primer_apellido = $_POST['primer_apellido'];
        $segundo_apellido = $_POST['segundo_apellido'];
        $nombres = $_POST['nombres'];
        $niev = $_POST['niev'];
        $tutor = $_POST['tutor'];
        $telefono = $_POST['telefono'];

        $sql = "INSERT INTO alumnos (curp, primer_apellido, segundo_apellido, nombres, niev, tutor, telefono, es_admin) 
                VALUES (:curp, :primer_apellido, :segundo_apellido, :nombres, :niev, :tutor, :telefono, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':curp' => $curp,
            ':primer_apellido' => $primer_apellido,
            ':segundo_apellido' => $segundo_apellido,
            ':nombres' => $nombres,
            ':niev' => $niev,
            ':tutor' => $tutor,
            ':telefono' => $telefono
        ]);
    }
}

$sql = "SELECT curp, primer_apellido, segundo_apellido, nombres, niev, tutor, telefono FROM alumnos";
$stmt = $conn->query($sql);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-users"></i> Gestión de Alumnos</h2>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-plus"></i> Agregar Alumno</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="curp" class="form-label">CURP:</label>
                    <input type="text" name="curp" id="curp" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="primer_apellido" class="form-label">Primer Apellido:</label>
                    <input type="text" name="primer_apellido" id="primer_apellido" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="segundo_apellido" class="form-label">Segundo Apellido:</label>
                    <input type="text" name="segundo_apellido" id="segundo_apellido" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="nombres" class="form-label">Nombres:</label>
                    <input type="text" name="nombres" id="nombres" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="niev" class="form-label">NIEV:</label>
                    <input type="text" name="niev" id="niev" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="tutor" class="form-label">Tutor:</label>
                    <input type="text" name="tutor" id="tutor" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" name="telefono" id="telefono" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" name="add" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Alumno</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Lista de Alumnos</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>CURP</th>
                        <th>Nombre</th>
                        <th>Primer Apellido</th>
                        <th>Segundo Apellido</th>
                        <th>NIEV</th>
                        <th>Tutor</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alumno['curp']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['primer_apellido']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['segundo_apellido']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['niev']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['tutor']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['telefono']); ?></td>
                            <td>
                                <a href="edit_alumno.php?curp=<?php echo urlencode($alumno['curp']); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
                                <a href="delete_alumno.php?curp=<?php echo urlencode($alumno['curp']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')"><i class="fas fa-trash"></i> Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>