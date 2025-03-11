<?php
require 'database.php'; // Incluye la conexión a la base de datos

$sql = "SELECT id, password FROM usuarios";
$stmt = $conn->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $hashed = password_hash($row['password'], PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE usuarios SET password = :hash WHERE id = :id");
    $update->execute([':hash' => $hashed, ':id' => $row['id']]);
}
echo "Contraseñas actualizadas a hashes correctamente";
?>