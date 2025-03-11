<?php
// Control de Cambios
// Hash: k1l2m3n4o5p6q7r8s9t0u1v2w3x4 (MD5 del contenido sin este comentario)
// Versión: v1.2
?>
<div class="dropdown">
    <button class="dropbtn">Menú <span class="arrow">▼</span></button>
    <div class="dropdown-content">
        <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
        <?php if ($_SESSION['es_admin']) { ?>
            <a href="alumnos.php"><i class="fas fa-users"></i> Alumnos</a>
            <a href="docentes.php"><i class="fas fa-chalkboard-teacher"></i> Docentes</a>
            <a href="inscripcion.php"><i class="fas fa-user-plus"></i> Inscripción</a>
            <a href="importar_alumnos.php"><i class="fas fa-file-upload"></i> Importar Alumnos</a>
            <a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a>
            <a href="admin.php"><i class="fas fa-cogs"></i> Administración</a>
            <a href="registro_alumnos.php"><i class="fas fa-user-edit"></i> Registro Alumnos</a>
        <?php } else { ?>
            <a href="inscripcion.php"><i class="fas fa-user-plus"></i> Inscribirme</a>
        <?php } ?>
        <a href="omniconsultas.php"><i class="fas fa-search"></i> Consultas</a>
        <a href="cambiar_contrasena.php"><i class="fas fa-key"></i> Cambiar Contraseña</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>
</div>