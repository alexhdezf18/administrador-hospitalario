<?php
session_start();
// 1. Seguridad y Conexión
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /index.php");
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

// 2. CONSULTAS DE ESTADÍSTICAS
try {
    // A. Contar Pacientes
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'paciente'");
    $total_pacientes = $stmt->fetchColumn();

    // B. Contar Médicos
    $stmt = $pdo->query("SELECT COUNT(*) FROM medicos");
    $total_medicos = $stmt->fetchColumn();

    // C. Citas para HOY
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE fecha_cita = CURDATE() AND estado != 'cancelada'");
    $stmt->execute();
    $citas_hoy = $stmt->fetchColumn();

    // D. Ingresos Totales (Suma de la tabla pagos)
    $stmt = $pdo->query("SELECT SUM(monto) FROM pagos");
    $total_ingresos = $stmt->fetchColumn() ?: 0; // Si es null, pon 0

} catch (PDOException $e) {
    $error = "Error al cargar estadísticas";
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Panel de Control General</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Pacientes Totales</h6>
                        <h2 class="display-4 fw-bold my-2"><?= $total_pacientes ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-primary border-0">
                <a href="usuarios.php" class="text-white text-decoration-none small">Ver lista <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Médicos Plantilla</h6>
                        <h2 class="display-4 fw-bold my-2"><?= $total_medicos ?></h2>
                    </div>
                    <i class="fas fa-user-md fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0 text-dark">Citas Hoy</h6>
                        <h2 class="display-4 fw-bold my-2 text-dark"><?= $citas_hoy ?></h2>
                    </div>
                    <i class="fas fa-calendar-day fa-3x text-dark opacity-25"></i>
                </div>
            </div>
            <div class="card-footer bg-warning border-0">
                <a href="citas.php" class="text-dark text-decoration-none small">Ir a la agenda <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Ingresos Totales</h6>
                        <h2 class="fw-bold my-2">$<?= number_format($total_ingresos, 2) ?></h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-success border-0">
                <a href="reportes.php" class="text-white text-decoration-none small">Ver detalles <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-light border mt-4">
    <h4><i class="fas fa-info-circle text-primary me-2"></i> Estado del Sistema</h4>
    <p class="mb-0">El sistema está funcionando correctamente. La base de datos está registrando transacciones en tiempo real.</p>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>