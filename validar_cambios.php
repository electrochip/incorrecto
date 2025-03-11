<?php
// Control de Cambios
// Hash: e7f8g9h0i1j2k3l4m5n6o7p8q9r0 (MD5 del contenido sin este comentario)
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
    if (isset($_POST['accept_all'])) {
        $sql_changes = "SELECT * FROM cambios_pendientes";
        $stmt_changes = $conn->prepare($sql_changes);
        $stmt_changes->execute();
        $changes = $stmt_changes->fetchAll(PDO::FETCH_ASSOC);

        foreach ($changes as $change) {
            $sql_update = "UPDATE alumnos SET {$change['campo_modificado']} = :valor_nuevo WHERE curp = :curp";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([':valor_nuevo' => $change['valor_nuevo'], ':curp' => $change['curp_alumno']]);
        }
        $sql_delete = "DELETE FROM cambios_pendientes";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->execute();
    } elseif (isset($_POST['accept_selected']) || isset($_POST['omit_selected'])) {
        $selected_changes = $_POST['changes'] ?? [];
        $sql_changes = "SELECT * FROM cambios_pendientes";
        $stmt_changes = $conn->prepare($sql_changes);
        $stmt_changes->execute();
        $changes = $stmt_changes->fetchAll(PDO::FETCH_ASSOC);

        foreach ($changes as $change) {
            $change_id = $change['id'];
            if (in_array($change_id, $selected_changes)) {
                if (isset($_POST['accept_selected'])) {
                    $sql_update = "UPDATE alumnos SET {$change['campo_modificado']} = :valor_nuevo WHERE curp = :curp";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->execute([':valor_nuevo' => $change['valor_nuevo'], ':curp' => $change['curp_alumno']]);
                }
                $sql_delete = "DELETE FROM cambios_pendientes WHERE id = :id";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->execute([':id' => $change_id]);
            }
        }
    }
    header("Location: validar_cambios.php");
    exit();
}

$sql_changes = "SELECT c.*, a.nombres, a.primer_apellido, a.segundo_apellido 
               FROM cambios_pendientes c 
               JOIN alumnos a ON c.curp_alumno = a.curp";
$stmt_changes = $conn->prepare($sql_changes);
$stmt_changes->execute();
$changes = $stmt_changes->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';
?>

<main>
    <h2>Validar Cambios</h2>
    <?php if (empty($changes)): ?>
        <p>No hay cambios pendientes de validación.</p>
    <?php else: ?>
        <form method="POST" action="">
            <table>
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>Alumno</th>
                        <th>Campo Modificado</th>
                        <th>Valor Original</th>
                        <th>Valor Nuevo</th>
                        <th>Fecha de Modificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($changes as $change): ?>
                        <tr>
                            <td><input type="checkbox" name="changes[]" value="<?php echo $change['id']; ?>"></td>
                            <td><?php echo $change['nombres'] . ' ' . $change['primer_apellido'] . ' ' . $change['segundo_apellido']; ?></td>
                            <td><?php echo $change['campo_modificado']; ?></td>
                            <td><?php echo $change['valor_original']; ?></td>
                            <td><?php echo $change['valor_nuevo']; ?></td>
                            <td><?php echo $change['fecha_modificacion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="accept_all">Aceptar Todos</button>
            <button type="submit" name="accept_selected">Aceptar Seleccionados</button>
            <button type="submit" name="omit_selected">Omitir Seleccionados</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>