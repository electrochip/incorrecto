<?php
// Control de Cambios
// Hash: i1j2k3l4m5n6o7p8q9r0s1t2u3v4 (MD5 del contenido sin este comentario)
// Versión: v2.3
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Evitar múltiples sesiones simultáneas para alumnos
if (isset($_SESSION['user_id']) && !isset($_SESSION['es_admin'])) {
    $current_session_id = session_id();
    $curp = $_SESSION['user_id'];

    require 'database.php';
    $sql_session = "SELECT session_id FROM sesiones_activas WHERE curp_alumno = :curp";
    $stmt_session = $conn->prepare($sql_session);
    $stmt_session->execute([':curp' => $curp]);
    $stored_session = $stmt_session->fetchColumn();

    if ($stored_session && $stored_session != $current_session_id) {
        session_destroy();
        header("Location: index.php?error=Sesión activa en otro dispositivo");
        exit();
    } else {
        $sql_update = "INSERT INTO sesiones_activas (curp_alumno, session_id, last_activity) 
                       VALUES (:curp, :session_id, NOW()) 
                       ON DUPLICATE KEY UPDATE session_id = :session_id, last_activity = NOW()";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':curp' => $curp, ':session_id' => $current_session_id]);
    }

    // Cerrar sesión por inactividad (5 minutos = 300 segundos)
    $inactivity_limit = 300;
    $sql_activity = "SELECT last_activity FROM sesiones_activas WHERE curp_alumno = :curp";
    $stmt_activity = $conn->prepare($sql_activity);
    $stmt_activity->execute([':curp' => $curp]);
    $last_activity = strtotime($stmt_activity->fetchColumn());

    if (time() - $last_activity > $inactivity_limit) {
        $sql_delete = "DELETE FROM sesiones_activas WHERE curp_alumno = :curp";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->execute([':curp' => $curp]);
        session_destroy();
        header("Location: index.php?error=Sesión expirada por inactividad");
        exit();
    }
}

// Proteger módulos de administrador
if (isset($_SESSION['es_admin']) && !$_SESSION['es_admin'] && !in_array(basename($_SERVER['PHP_SELF']), ['index.php', 'inscripcion.php', 'generate_pdf.php', 'logout.php'])) {
    header("Location: index.php");
    exit();
}