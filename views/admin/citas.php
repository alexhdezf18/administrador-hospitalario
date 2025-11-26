<?php
session_start();
$error_db = null;
$citas = [];
$pacientes = [];
$medicos = [];

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

try {
    // A. CONSULTA DE CITAS (CON JOIN)
    // Necesitamos JOIN porque la tabla 'citas' solo tiene IDs (1, 5). 
    // Queremos mostrar NOMBRES ("Juan", "Dr. House").
    $sqlCitas = "
        SELECT c.id, c.fecha_cita, c.hora_cita, c.estado,
               p.nombre AS paciente_nombre, p.apellidos AS paciente_apellido,
               u_med.nombre AS medico_nombre, u_med.apellidos AS medico_apellido, m.especialidad
        FROM citas c
        JOIN usuarios p ON c.paciente_id = p.id              -- Unimos con usuarios para saber nombre del paciente
        JOIN medicos m ON c.medico_id = m.id                 -- Unimos con medicos para saber datos del doctor
        JOIN usuarios u_med ON m.usuario_id = u_med.id       -- Unimos médico con usuarios para saber SU nombre
        ORDER BY c.fecha_cita DESC, c.hora_cita ASC
    ";
    $citas = $pdo->query($sqlCitas)->fetchAll(PDO::FETCH_ASSOC);

    // B. LISTA DE PACIENTES (Para el Select)
    $pacientes = $pdo->query("SELECT id, nombre, apellidos FROM usuarios WHERE rol = 'paciente'")->fetchAll(PDO::FETCH_ASSOC);

    // C. LISTA DE MÉDICOS (Para el Select)
    // Traemos el ID de la tabla 'medicos' (no del usuario) porque eso es lo que guarda la tabla citas
    $sqlMedicos = "SELECT m.id, u.nombre, u.apellidos, m.especialidad 
                   FROM medicos m 
                   JOIN usuarios u ON m.usuario_id = u.id";
    $medicos = $pdo->query($sqlMedicos)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_db = "Error de conexión: " . $e->getMessage();
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Citas Médicas</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCita">
        <i class="fas fa-calendar-plus"></i> Agendar Cita
    </button>
</div>

<?php if(isset($_GET['mensaje'])): ?>
    <div class="alert alert-success">Cita agendada correctamente.</div>
<?php endif; ?>
<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="table-responsive bg-white shadow-sm rounded p-3">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Especialidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($citas)): ?>
                <?php foreach ($citas as $cita): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></td>
                    <td><?= date('H:i', strtotime($cita['hora_cita'])) ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($cita['paciente_nombre'] . " " . $cita['paciente_apellido']) ?></td>
                    <td>Dr. <?= htmlspecialchars($cita['medico_nombre'] . " " . $cita['medico_apellido']) ?></td>
                    <td><small class="text-muted"><?= htmlspecialchars($cita['especialidad']) ?></small></td>
                    <td>
                        <?php 
                            $badgeColor = match($cita['estado']) {
                                'pendiente' => 'warning text-dark',
                                'confirmada' => 'primary',
                                'completada' => 'success',
                                'cancelada' => 'danger',
                                default => 'secondary'
                            };
                        ?>
                        <span class="badge bg-<?= $badgeColor ?>"><?= ucfirst($cita['estado']) ?></span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" title="Cancelar"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted">No hay citas registradas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalNuevaCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agendar Nueva Cita</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/controllers/citas_controller.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">

                    <div class="mb-3">
                        <label class="form-label">Paciente</label>
                        <select name="paciente_id" class="form-select" required>
                            <option value="">Seleccione Paciente...</option>
                            <?php foreach ($pacientes as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['nombre'] . " " . $p['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Médico</label>
                        <select name="medico_id" class="form-select" required>
                            <option value="">Seleccione Médico...</option>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    Dr. <?= htmlspecialchars($m['nombre'] . " " . $m['apellidos']) ?> (<?= $m['especialidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Motivo (Opcional)</label>
                        <textarea name="motivo" class="form-control" rows="2"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Cita</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>