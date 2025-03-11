<?php
// Control de Cambios
// Hash: s1t2u3v4w5x6y7z8a9b0c1d2e3f4 (MD5 del contenido sin este comentario)
// Versión: v2.3
session_start();
require 'database.php';

if (isset($_SESSION['user_id']) && !isset($_SESSION['es_admin'])) {
    $curp = $_SESSION['user_id'];
    $sql_delete = "DELETE FROM sesiones_activas WHERE curp_alumno = :curp";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->execute([':curp' => $curp]);
}

session_destroy();
header("Location: index.php");
exit();