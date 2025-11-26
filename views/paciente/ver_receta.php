<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: /index.php");
    exit();
}

$id_historial = $_GET['id'] ?? 0;
$receta = null;

try {
    // Buscamos el detalle, PERO asegurando que pertenezca al paciente logueado (Seguridad)
    $sql = "
        SELECT h.*, 
               u_med.nombre AS doc_nombre, u_med.apellidos AS doc_apellido, m.cedula_profesional, m.especialidad,
               u_pac.nombre AS pac_nombre, u_pac.apellidos AS pac_apellido
        FROM historial_medico h
        JOIN medicos m ON h.medico_id = m.id
        JOIN usuarios u_med ON m.usuario_id = u_med.id
        JOIN usuarios u_pac ON h.paciente_id = u_pac.id
        WHERE h.id = :hid AND h.paciente_id = :uid
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['hid' => $id_historial, 'uid' => $_SESSION['user_id']]);
    $receta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receta) {
        die("Receta no encontrada o no tienes permiso para verla.");
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
// Sin sidebar para estilo "Documento"
?>

<div class="container py-5">
    <div class="mb-3 no-print">
        <a href="historial.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Imprimir</button>
    </div>

    <div class="card shadow-lg border-0">
        <div class="card-header bg-white border-bottom-0 pt-4 px-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="text-primary fw-bold">Clínica Universitaria</h2>
                    <p class="text-muted mb-0">Servicios Médicos Integrales</p>
                </div>
                <div class="col-md-4 text-end">
                    <h5 class="mb-0">Receta Médica</h5>
                    <small class="text-muted">Folio: #<?= str_pad($receta['id'], 6, '0', STR_PAD_LEFT) ?></small>
                </div>
            </div>
            <hr class="mt-4 border-primary opacity-100">
        </div>

        <div class="card-body px-5 py-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-uppercase text-muted small">Médico Tratante</h6>
                    <h5 class="fw-bold">Dr. <?= htmlspecialchars($receta['doc_nombre'] . ' ' . $receta['doc_apellido']) ?></h5>
                    <p class="mb-0 small"><?= htmlspecialchars($receta['especialidad']) ?></p>
                    <p class="small">Cédula Prof: <?= htmlspecialchars($receta['cedula_profesional']) ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <h6 class="text-uppercase text-muted small">Paciente</h6>
                    <h5><?= htmlspecialchars($receta['pac_nombre'] . ' ' . $receta['pac_apellido']) ?></h5>
                    <p class="small">Fecha: <?= date('d/m/Y h:i A', strtotime($receta['fecha_registro'])) ?></p>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold text-primary">Diagnóstico</h6>
                <p class="fs-5"><?= nl2br(htmlspecialchars($receta['diagnostico'])) ?></p>
            </div>

            <div class="p-4 bg-light rounded border">
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-pills me-2"></i>Tratamiento y Prescripción</h6>
                <p class="fs-5" style="font-family: 'Courier New', Courier, monospace;">
                    <?= nl2br(htmlspecialchars($receta['tratamiento'])) ?>
                </p>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 pb-5 pt-5 text-center">
            <div class="row justify-content-center">
                <div class="col-md-4 border-top border-dark pt-2">
                    <p class="mb-0">Firma del Médico</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para imprimir solo la receta */
    @media print {
        .no-print, header, nav, footer { display: none !important; }
        body { background: white; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>