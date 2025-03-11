<?php
require 'session_check.php'; // Nueva inclusión
// Control de Cambios
// Hash: o7p8q9r0s1t2u3v4w5x6y7z8a9b0 (MD5 del contenido sin este comentario)
// Versión: v2.3
require 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

require 'header.php';
?>

<main>
    <h2>Reportes</h2>
    <p>Seleccione un tipo de reporte:</p>
    <ul>
        <li><a href="reportes_alumnos.php">Reportes de Alumnos</a></li>
        <li><a href="reportes_asignaciones.php">Reportes de Asignaciones</a></li>
        <li><a href="reportes_historicos.php">Reportes de Históricos</a></li>
    </ul>
</main>
</body>
</html>