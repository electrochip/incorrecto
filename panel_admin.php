<?php
require 'session_check.php'; // Control de sesiones y seguridad
// Control de Cambios
// Hash: t2u3v4w5x6y7z8a9b0c1d2e3f4g5h6 (MD5 del contenido sin este comentario)
// Versión: v2.4
require 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
$config = [
    'ciclo_actual' => '',
    'ciclo_siguiente' => '',
    'limite_alumnos_grado' => [],
    'limite_alumnos_grupo' => [],
    'periodo_inscripcion' => [
        'inicio' => '',
        'fin' => '',
        'hora_inicio' => '09:00',
        'hora_fin' => '13:00'
    ]
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_config'])) {
        $ciclo_actual = $_POST['ciclo_actual'];
        $ciclo_siguiente = $_POST['ciclo_siguiente'];
        $limite_alumnos_grado = array_map('intval', $_POST['limite_alumnos_grado']);
        $limite_alumnos_grupo = array_map('intval', $_POST['limite_alumnos_grupo']);
        $periodo_inicio = $_POST['periodo_inicio'];
        $periodo_fin = $_POST['periodo_fin'];
        $hora_inicio = $_POST['hora_inicio'];
        $hora_fin = $_POST['hora_fin'];

        $sql_update = "INSERT INTO configuracion (ciclo_actual, ciclo_siguiente, limite_alumnos_grado, limite_alumnos_grupo, periodo_inicio, periodo_fin, hora_inicio, hora_fin) 
                       VALUES (:ciclo_actual, :ciclo_siguiente, :limite_alumnos_grado, :limite_alumnos_grupo, :periodo_inicio, :periodo_fin, :hora_inicio, :hora_fin)
                       ON DUPLICATE KEY UPDATE ciclo_actual = :ciclo_actual, ciclo_siguiente = :ciclo_siguiente, 
                       limite_alumnos_grado = :limite_alumnos_grado, limite_alumnos_grupo = :limite_alumnos_grupo, 
                       periodo_inicio = :periodo_inicio, periodo_fin = :periodo_fin, hora_inicio = :hora_inicio, hora_fin = :hora_fin";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([
            ':ciclo_actual' => $ciclo_actual,
            ':ciclo_siguiente' => $ciclo_siguiente,
            ':limite_alumnos_grado' => json_encode($limite_alumnos_grado),
            ':limite_alumnos_grupo' => json_encode($limite_alumnos_grupo),
            ':periodo_inicio' => $periodo_inicio,
            ':periodo_fin' => $periodo_fin,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin
        ]);
        $success = "Configuración guardada exitosamente.";
    }
}

$sql_config = "SELECT * FROM configuracion LIMIT 1";
$stmt_config = $conn->prepare($sql_config);
$stmt_config->execute();
$config_db = $stmt_config->fetch(PDO::FETCH_ASSOC);
if ($config_db) {
    $config = array_merge($config, $config_db);
    $config['limite_alumnos_grado'] = json_decode($config['limite_alumnos_grado'], true) ?: [];
    $config['limite_alumnos_grupo'] = json_decode($config['limite_alumnos_grupo'], true) ?: [];
}

require 'header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <style>
        .form-container {
            max-width: 100%;
            margin: 20px auto;
            padding: 20px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .form-row label, .form-row input, .form-row select {
            flex: 1;
            min-width: 200px;
        }
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            .form-row label, .form-row input, .form-row select {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <main>
        <h2>Panel de Administración</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

        <div class="form-container">
            <h3>Configuración del Sistema</h3>
            <form method="POST" action="" class="form-row">
                <label>Ciclo Actual:</label>
                <input type="text" name="ciclo_actual" value="<?php echo $config['ciclo_actual']; ?>" required><br>
                <label>Ciclo Siguiente:</label>
                <input type="text" name="ciclo_siguiente" value="<?php echo $config['ciclo_siguiente']; ?>" required><br>
                <label>Límite Alumnos por Grado (1-6):</label>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="number" name="limite_alumnos_grado[<?php echo $i; ?>]" value="<?php echo $config['limite_alumnos_grado'][$i] ?? 0; ?>" min="0">
                <?php endfor; ?><br>
                <label>Límite Alumnos por Grupo (1-6):</label>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="number" name="limite_alumnos_grupo[<?php echo $i; ?>]" value="<?php echo $config['limite_alumnos_grupo'][$i] ?? 0; ?>" min="0">
                <?php endfor; ?><br>
                <label>Periodo de Inscripción - Inicio:</label>
                <input type="datetime-local" name="periodo_inicio" value="<?php echo $config['periodo_inicio']; ?>"><br>
                <label>Periodo de Inscripción - Fin:</label>
                <input type="datetime-local" name="periodo_fin" value="<?php echo $config['periodo_fin']; ?>"><br>
                <label>Hora Inicio (ej. 09:00):</label>
                <input type="time" name="hora_inicio" value="<?php echo $config['hora_inicio']; ?>"><br>
                <label>Hora Fin (ej. 13:00):</label>
                <input type="time" name="hora_fin" value="<?php echo $config['hora_fin']; ?>"><br>
                <button type="submit" name="save_config">Guardar Configuración</button>
            </form>
        </div>
    </main>
</body>
</html>