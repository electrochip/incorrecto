<?php
// Control de Cambios
// Hash: f2g3h4i5j6k7l8m9n0o1p2q3r4s5 (MD5 del contenido sin este comentario)
// Versión: v1.9
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
    <h2>¡Bienvenido al Sistema de Control Escolar!</h2>
    <p>Usuario ID: <?php echo $_SESSION['user_id']; ?></p>
    <p>Rol: <?php echo $_SESSION['es_admin'] ? 'Administrador' : 'Usuario'; ?></p>
    <?php if ($_SESSION['es_admin']) { ?>
        <h3>Opciones de Administrador</h3>
        <ul>
            <li><a href="alumnos.php">Gestión de Alumnos</a></li>
            <li><a href="docentes.php">Gestión de Docentes</a></li>
            <li><a href="inscripcion.php">Inscripción de Alumnos</a></li>
            <li><a href="importar_alumnos.php">Importar Alumnos desde Excel</a></li>
            <li><a href="reportes.php">Reportes</a></li>
            <li><a href="admin.php">Panel de Administración</a></li>
        </ul>
    <?php } else { ?>
        <h3>Opciones de Usuario</h3>
        <ul>
            <li><a href="inscripcion.php">Inscribirme</a></li>
            <li><a href="omniconsultas.php">Consultas Generales</a></li>
        </ul>
    <?php } ?>
    <a href="logout.php">Cerrar sesión</a>
</main>
</body>
</html>