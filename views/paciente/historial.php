<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

// Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: /index.php");
    exit();
}

$historial = [];

try {
    // Consulta para traer el historial médico de ESTE paciente
    // Unimos con 'citas' para saber la fecha real de la atención
    $sql = "
        SELECT h.id, h.diagnostico, h.fecha_registro,
               c.fecha_cita,
               u_med.nombre AS doc_nombre, u_med.apellidos AS doc_apellido, m.especialidad
        FROM historial_medico h
        JOIN medicos m ON h.medico_id = m.id
        JOIN usuarios u_med ON m.usuario_id = u_med.id
        JOIN citas c ON h.cita_id = c.id
        WHERE h.paciente_id = :uid
        ORDER BY c.fecha_cita DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mi Historial Clínico</h1>
</div>

<div class="row">
    <?php if (!empty($historial)): ?>
        <?php foreach ($historial as $registro): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-subtitle text-muted">
                            <i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($registro['fecha_cita'])) ?>
                        </h6>
                        <span class="badge bg-primary"><?= htmlspecialchars($registro['especialidad']) ?></span>
                    </div>
                    
                    <h5 class="card-title text-primary">Diagnóstico:</h5>
                    <p class="card-text fw-bold"><?= htmlspecialchars($registro['diagnostico']) ?></p>
                    
                    <p class="mb-1">
                        <small class="text-muted">Atendido por:</small><br>
                        Dr. <?= htmlspecialchars($registro['doc_nombre'] . ' ' . $registro['doc_apellido']) ?>
                    </p>
                    
                    <div class="mt-3 text-end">
                        <a href="ver_receta.php?id=<?= $registro['id'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-prescription me-1"></i> Ver Receta / Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-light text-center border">
                Aún no tienes registros en tu historial médico.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>