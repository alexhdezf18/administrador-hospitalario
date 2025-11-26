<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

// 1. Seguridad: Solo pacientes
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: /index.php");
    exit();
}

$proximas_citas = [];

try {
    // 2. Traer citas FUTURAS del paciente
    $sql = "
        SELECT c.fecha_cita, c.hora_cita, c.estado, 
               u_med.nombre, u_med.apellidos, m.especialidad
        FROM citas c
        JOIN medicos m ON c.medico_id = m.id
        JOIN usuarios u_med ON m.usuario_id = u_med.id
        WHERE c.paciente_id = :uid 
        AND c.fecha_cita >= CURDATE()
        AND c.estado IN ('pendiente', 'confirmada')
        ORDER BY c.fecha_cita ASC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $proximas_citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Mis Próximas Citas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($proximas_citas)): ?>
                                <?php foreach ($proximas_citas as $cita): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></td>
                                    <td><?= date('H:i', strtotime($cita['hora_cita'])) ?></td>
                                    <td>Dr. <?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellidos']) ?></td>
                                    <td><?= htmlspecialchars($cita['especialidad']) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= ucfirst($cita['estado']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4">No tienes citas próximas agendadas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-muted">¿Necesitas atención?</h5>
                <p class="card-text">Comunícate con recepción para agendar una nueva cita.</p>
                <a href="#" class="btn btn-outline-primary disabled">Agendar (Llamar)</a>
            </div>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>