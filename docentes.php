<?php
session_start();
require_once 'header.php';
require_once 'database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['es_admin']) {
    header("Location: index.php");
    exit();
}

// Lógica similar a alumnos (implementar según base de datos)
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-chalkboard-teacher"></i> Gestión de Docentes</h2>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-plus"></i> Agregar Docente</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="primer_apellido" class="form-label">Primer Apellido:</label>
                    <input type="text" name="primer_apellido" id="primer_apellido" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" name="add" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Docente</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Lista de Docentes</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Primer Apellido</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="3">Implementar consulta de docentes</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>