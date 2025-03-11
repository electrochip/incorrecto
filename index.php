<?php
session_start();
require_once 'database.php';

if (isset($_POST['login'])) {
    $curp = strtoupper($_POST['curp']);
    $sql = "SELECT curp, es_admin FROM alumnos WHERE curp = :curp";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curp' => $curp]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['curp'];
        $_SESSION['es_admin'] = $user['es_admin'];
        header("Location: " . (isset($_GET['admin']) && $_GET['admin'] ? 'dashboard.php' : 'inscripcion.php'));
        exit();
    } else {
        $error = "CURP o NIEV no válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Control Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <div class="card text-center">
            <div class="card-header bg-dark text-white">
                <h2>¡Bienvenido al Sistema de Control Escolar!</h2>
            </div>
            <div class="card-body">
                <h5 class="card-title">Usuario ID: No identificado</h5>
                <p class="card-text">Rol: Alumno</p>
                <a href="#" class="btn btn-danger disabled">Cerrar Sesión</a>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Iniciar Sesión (Alumnos)</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="curp" class="form-label">CURP o NIEV:</label>
                        <input type="text" name="curp" id="curp" class="form-control" required>
                        <div class="invalid-feedback">Por favor, ingresa tu CURP o NIEV.</div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>