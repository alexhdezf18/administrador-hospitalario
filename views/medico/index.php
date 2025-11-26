<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

// 1. Seguridad: Solo médicos
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'medico') {
    header("Location: /index.php");
    exit();
}

$citas = [];
$medico_info = null;

try {
    // 2. OBTENER EL ID DE MÉDICO USANDO EL ID DE USUARIO
    // Primero averiguamos "quién es este doctor" en la tabla de médicos
    $sqlMedico = "SELECT id, especialidad FROM medicos WHERE usuario_id = :uid LIMIT 1";
    $stmt = $pdo->prepare($sqlMedico);
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $medico_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($medico_info) {
        $medico_id = $medico_info['id'];

        // 3. TRAER SOLO LAS CITAS DE ESTE MÉDICO
        // Unimos con la tabla usuarios (p) para saber el nombre del paciente
        $sqlCitas = "
            SELECT c.id, c.fecha_cita, c.hora_cita, c.estado, c.observaciones,
                   p.nombre AS paciente_nombre, p.apellidos AS paciente_apellido
            FROM citas c
            JOIN usuarios p ON c.paciente_id = p.id
            WHERE c.medico_id = :mid 
            AND c.estado != 'cancelada'
            ORDER BY c.fecha_cita ASC, c.hora_cita ASC
        ";
        $stmtCitas = $pdo->prepare($sqlCitas);
        $stmtCitas->execute(['mid' => $medico_id]);
        $citas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mi Agenda Médica</h1>
    <span class="badge bg-primary fs-6">Espec: <?= htmlspecialchars($medico_info['especialidad'] ?? 'General') ?></span>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Pacientes Hoy</h5>
                <p class="card-text display-6 fw-bold">
                    <?= count(array_filter($citas, function($c) { return $c['fecha_cita'] == date('Y-m-d'); })) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive bg-white shadow-sm rounded p-3">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Motivo / Observaciones</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($citas)): ?>
                <?php foreach ($citas as $cita): ?>
                <tr class="<?= $cita['fecha_cita'] == date('Y-m-d') ? 'table-warning' : '' ?>">
                    <td><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></td>
                    <td class="fw-bold"><?= date('H:i', strtotime($cita['hora_cita'])) ?></td>
                    <td><?= htmlspecialchars($cita['paciente_nombre'] . " " . $cita['paciente_apellido']) ?></td>
                    <td><small class="text-muted"><?= htmlspecialchars($cita['observaciones']) ?></small></td>
                    <td>
                        <?php if($cita['estado'] == 'completada'): ?>
                            <span class="badge bg-success">Atendido</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($cita['estado'] == 'pendiente'): ?>
                            <a href="atender_cita.php?id=<?= $cita['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-stethoscope"></i> Atender
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>Ver Historial</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center py-4">No tienes citas programadas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>