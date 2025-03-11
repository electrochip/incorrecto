<?php
// Control de Cambios
// Hash: w9x0y1z2a3b4c5d6e7f8g9h0i1j2 (MD5 del contenido sin este comentario)
// Versión: v2.1
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    try {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($contrasena, $user['contrasena'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['es_admin'] = $user['es_admin'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Administrador - Control Escolar</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
</head>
<body>
    <?php require 'header.php'; ?>
    <main>
        <div class="login-container">
            <h2>Acceso Administrador</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="">
                <label>Usuario:</label><br>
                <input type="text" name="usuario" required><br>
                <label>Contraseña:</label><br>
                <input type="password" name="contrasena" required><br>
                <button type="submit">Iniciar sesión</button>
            </form>
        </div>
    </main>
</body>
</html>