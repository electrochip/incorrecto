<?php
// Control de Cambios
// Hash: z9a0b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5q6r7 (MD5 del contenido sin este comentario)
// Versión: v2.6
require 'database.php';
$sql = "SELECT * FROM cambios_pendientes";
$stmt = $conn->query($sql);
$changes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($changes, true) . "</pre>";
// Manualmente revisado por admin
?>