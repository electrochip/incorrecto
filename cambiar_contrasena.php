<?php
require 'session_check.php'; // Nueva inclusión
// Control de Cambios
// Hash: n6o7p8q9r0s1t2u3v4w5x6y7z8a9 (MD5 del contenido sin este comentario)
// Versión: v2.3
require 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);

    $sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena_actual, $user['contrasena'])) {
        $sql_update = "UPDATE usuarios SET contrasena = :contrasena WHERE usuario = :usuario";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':contrasena' => $nueva_contrasena, ':usuario' => $usuario]);
        $success = "Contraseña cambiada exitosamente.";
    } else {
        $error = "Usuario o contraseña actual incorrectos.";
    }
}

require 'header.php';
?>

<main>
    <h2>Cambiar Contraseña</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

    <div class="form-container">
        <form method="POST" action="">
            <label>Usuario:</label>
            <input type="text" name="usuario" required><br>
            <label>Contraseña Actual:</label>
            <input type="password" name="contrasena_actual" required><br>
            <label>Nueva Contraseña:</label>
            <input type="password" name="nueva_contrasena" required><br>
            <button type="submit">Cambiar Contraseña</button>
        </form>
    </div>
</main>
</body>
</html>