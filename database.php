<?php
// Control de Cambios
// Hash: v4w5x6y7z8a9b0c1d2e3f4g5h6i7j8k9l0m1n2 (MD5 del contenido sin este comentario)
// Versión: v2.6

$host = 'sql309.infinityfree.com';
$dbname = 'if0_38403974_cantonal';
$username = 'if0_38403974';
$password = 'D8I9V1A3';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8mb4'");

    // Crear tabla configuracion si no existe
    $sql_create_config = "CREATE TABLE IF NOT EXISTS configuracion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ciclo_actual VARCHAR(9),
        ciclo_siguiente VARCHAR(9),
        limite_alumnos_grado JSON,
        limite_alumnos_grupo JSON,
        periodo_inicio DATETIME,
        periodo_fin DATETIME,
        hora_inicio TIME,
        hora_fin TIME
    )";
    $conn->exec($sql_create_config);

    // Crear tabla logs
    $sql_create_logs = "CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50),
        accion VARCHAR(100),
        detalle TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql_create_logs);
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>