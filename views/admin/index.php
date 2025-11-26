<?php
session_start();

// 1. Seguridad: Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// 2. Incluir el Header y Sidebar (La estructura visual)
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Panel de Control</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Usuarios Registrados</h5>
                <p class="card-text display-4">0</p>
                <small>Pacientes, Médicos y Staff</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Citas para Hoy</h5>
                <p class="card-text display-4">0</p>
                <small>Agendadas en el sistema</small>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info" role="alert">
    Bienvenido al sistema de administración. [cite_start]Desde aquí puedes gestionar usuarios y ver reportes globales[cite: 6].
</div>

<?php
// 3. Incluir el Footer (Cierre de etiquetas)
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
?>