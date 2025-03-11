<?php
// Control de Cambios
// Hash: y8z9a0b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5q6 (MD5 del contenido sin este comentario)
// Versión: v2.6
require 'database.php';
$backup_file = 'backup_' . date('YmdHis') . '.sql';
exec("mysqldump -u $username -p$password $dbname > $backup_file 2>/dev/null");
if (file_exists($backup_file)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_file));
    readfile($backup_file);
    unlink($backup_file);
} else {
    die("Error al generar backup.");
}
?>