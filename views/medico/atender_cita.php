<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

// 1. Seguridad: Solo médicos
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'medico') {
    header("Location: /index.php");
    exit();
}

// 2. Validar que venga el ID de la cita
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$cita_id = $_GET['id'];
$cita = null;

try {
    // 3. Obtener datos de la cita y del paciente para mostrarlos
    // Verificamos que la cita realmente pertenezca a este médico (Seguridad)
    $sql = "
        SELECT c.*, p.nombre, p.apellidos, p.email
        FROM citas c
        JOIN usuarios p ON c.paciente_id = p.id
        JOIN medicos m ON c.medico_id = m.id
        WHERE c.id = :id AND m.usuario_id = :uid
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $cita_id, 'uid' => $_SESSION['user_id']]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cita) {
        // Si no se encuentra la cita o no es de este médico
        header("Location: index.php?error=" . urlencode("Cita no encontrada o acceso denegado."));
        exit();
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
// Nota: No incluimos sidebar aquí para dar más espacio a la escritura (Modo Focus)
?>

<div class="container py-4">
    <div class="row mb-3">
        <div class="col">
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver a la Agenda</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Consulta Médica en Curso</h4>
            <span class="badge bg-light text-primary fs-6">Cita #<?= $cita['id'] ?></span>
        </div>
        
        <div class="card-body">
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Paciente:</strong> <?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellidos']) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Motivo Inicial:</strong> <?= htmlspecialchars($cita['observaciones'] ?? 'Sin especificar') ?>
                    </div>
                </div>
            </div>

            <form action="/controllers/historial_controller.php" method="POST">
                <input type="hidden" name="cita_id" value="<?= $cita['id'] ?>">
                <input type="hidden" name="paciente_id" value="<?= $cita['paciente_id'] ?>">
                <input type="hidden" name="medico_id" value="<?= $cita['medico_id'] ?>">

                <div class="mb-4">
                    <label class="form-label fw-bold text-primary">1. Motivo de Consulta y Síntomas (Anamnesis)</label>
                    <textarea name="motivo_consulta" class="form-control" rows="3" required placeholder="Describa los síntomas detallados del paciente..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-primary">2. Diagnóstico Médico</label>
                    <textarea name="diagnostico" class="form-control" rows="2" required placeholder="Ej: Infección respiratoria aguda... (Puede usar códigos CIE-10)"></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-primary">3. Tratamiento y Receta</label>
                    <div class="p-3 bg-light border rounded">
                        <textarea name="tratamiento" class="form-control" rows="4" required placeholder="Ej: Amoxicilina 500mg cada 8 horas por 7 días... Recomendaciones: Reposo..."></textarea>
                        <small class="text-muted"><i class="fas fa-info-circle"></i> Esta información se guardará en el expediente permanente del paciente.</small>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i> Finalizar Consulta y Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>