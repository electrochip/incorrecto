<?php
session_start();
require_once 'database.php';

// Verificar cambios pendientes (solo para admin)
$has_pending_changes = false;
if (isset($_SESSION['user_id']) && $_SESSION['es_admin']) {
    $sql_pending = "SELECT COUNT(*) FROM cambios_pendientes";
    $stmt_pending = $conn->prepare($sql_pending);
    $stmt_pending->execute();
    $has_pending_changes = $stmt_pending->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Escolar - Modelo Enrique Laubscher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <style>
        .navbar-custom {
            background-color: #4a2c2a;
            color: white;
        }
        .dropdown-submenu .dropdown-menu {
            position: absolute;
            left: 100%;
            top: 0;
            min-width: 200px;
        }
        .change-indicator::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background-color: red;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom p-2">
        <div class="container-fluid">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown <?php echo $has_pending_changes ? 'change-indicator' : ''; ?>">
                    <button class="btn btn-dark dropdown-toggle" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bars"></i> Menú
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="menuDropdown">
                        <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin']): ?>
                            <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                            <li><a class="dropdown-item" href="alumnos.php"><i class="fas fa-users"></i> Gestión de Alumnos</a></li>
                            <li><a class="dropdown-item" href="docentes.php"><i class="fas fa-chalkboard-teacher"></i> Gestión de Docentes</a></li>
                            <li><a class="dropdown-item" href="inscripcion_admin.php"><i class="fas fa-user-shield"></i> Inscripción Admin</a></li>
                            <li><a class="dropdown-item" href="importar_alumnos.php"><i class="fas fa-file-import"></i> Importar Alumnos</a></li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-chart-bar"></i> Reportes</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="reportes_alumnos.php"><i class="fas fa-users"></i> Alumnos</a></li>
                                    <li><a class="dropdown-item" href="reportes_asignaciones.php"><i class="fas fa-link"></i> Asignaciones</a></li>
                                    <li><a class="dropdown-item" href="imprimir_historico.php"><i class="fas fa-history"></i> Históricos</a></li>
                                </ul>
                            </li>
                            <li><a class="dropdown-item" href="panel_admin.php"><i class="fas fa-cogs"></i> Panel de Administración</a></li>
                            <li><a class="dropdown-item" href="cambiar_contrasena.php"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="inscripcion.php"><i class="fas fa-user-plus"></i> Inscripción</a></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <a class="navbar-brand mx-auto" href="#">
                <span>
                    <h1 class="m-0">MODELO ENRIQUE LAUBSCHER</h1>
                    <p class="m-0">CCT: 30EPR1536G</p>
                </span>
            </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="index.php?admin=1" class="btn btn-success">Acceso Administrador</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container mt-4">
        <?php
        if (isset($_GET['page']) && file_exists($_GET['page'] . '.php')) {
            include $_GET['page'] . '.php';
        } else {
            if (!isset($_SESSION['user_id'])) {
                echo "<div class='card text-center'>";
                echo "<div class='card-body'>";
                echo "<h2 class='card-title'>¡Bienvenido al Sistema de Control Escolar!</h2>";
                echo "<p class='card-text'>Usuario ID: No identificado</p>";
                echo "<p class='card-text'>Rol: Alumno</p>";
                echo "<a href='#' class='btn btn-danger disabled'>Cerrar Sesión</a>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<h2 class='text-center'>¡Bienvenido al Sistema de Control Escolar!</h2>";
                echo "<p class='text-center'>Usuario ID: " . (isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : 'No identificado') . "</p>";
                echo "<p class='text-center'>Rol: " . (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] ? 'Administrador' : 'Alumno') . "</p>";
                if (isset($_SESSION['es_admin']) && $_SESSION['es_admin']) {
                    echo "<h3 class='text-center mt-4'>Opciones de Administrador</h3>";
                    echo "<div class='row'>";
                    echo "<div class='col-md-4 mb-3'><a href='alumnos.php' class='btn btn-primary btn-lg w-100 rounded-pill'><i class='fas fa-users'></i> Gestión de Alumnos</a></div>";
                    echo "<div class='col-md-4 mb-3'><a href='docentes.php' class='btn btn-success btn-lg w-100 rounded-pill'><i class='fas fa-chalkboard-teacher'></i> Gestión de Docentes</a></div>";
                    echo "<div class='col-md-4 mb-3'><a href='importar_alumnos.php' class='btn btn-info btn-lg w-100 rounded-pill'><i class='fas fa-file-import'></i> Importar Alumnos</a></div>";
                    echo "<div class='col-md-4 mb-3'><a href='imprimir_historico.php' class='btn btn-warning btn-lg w-100 rounded-pill'><i class='fas fa-history'></i> Reportes</a></div>";
                    echo "<div class='col-md-4 mb-3'><a href='panel_admin.php' class='btn btn-danger btn-lg w-100 rounded-pill'><i class='fas fa-cogs'></i> Panel de Administración</a></div>";
                    echo "</div>";
                }
                echo "<p class='text-center mt-4'><a href='logout.php' class='btn btn-danger'>Cerrar Sesión</a></p>";
            }
        }
        ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inactivityTimeout = 300000;
            let inactivityTimer;

            function resetTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(logout, inactivityTimeout);
            }

            function logout() {
                fetch('logout.php', { method: 'POST' }).then(() => window.location.href = 'index.php?error=sesion_expirada');
            }

            document.addEventListener('mousemove', resetTimer);
            document.addEventListener('keypress', resetTimer);
            resetTimer();
        });
    </script>
</body>
</html>