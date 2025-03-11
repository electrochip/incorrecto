<?php
// Control de Cambios
// Hash: i5j6k7l8m9n0o1p2q3r4s5t6u7v8 (MD5 del contenido sin este comentario)
// Versión: v1.0
include 'database.php';

$ciclo = $_GET['ciclo'] ?? '';
$grado = $_GET['grado'] ?? '';

$stmt = $conn->prepare("SELECT DISTINCT grupo FROM grupos WHERE ciclo = :ciclo AND grado = :grado");
$stmt->execute(['ciclo' => $ciclo, 'grado' => $grado]);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($grupos);
?>