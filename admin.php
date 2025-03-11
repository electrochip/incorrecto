<?php
// Control de Cambios
// Hash: l8m9n0o1p2q3r4s5t6u7v8w9x0y1 (MD5 del contenido sin este comentario)
// Versión: v1.0
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'header.php';
?>

<main>
    <h2>Panel de Administración</h2>
    <p>Funcionalidad para administración en desarrollo...</p>
</main>
</body>
</html>