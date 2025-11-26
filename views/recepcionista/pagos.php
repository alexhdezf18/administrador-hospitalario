<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

// Seguridad: Recepcionista o Admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol'], ['recepcionista', 'admin'])) {
    header("Location: /index.php");
    exit();
}

$pendientes_pago = [];

try {
    // MAGIA SQL: "Dame las citas completadas que NO estén en la tabla de pagos"
    $sql = "
        SELECT c.id, c.fecha_cita, c.hora_cita, 
               p.nombre AS pac_nombre, p.apellidos AS pac_apellido,
               m_u.nombre AS doc_nombre, m_u.apellidos AS doc_apellido,
               m.especialidad
        FROM citas c
        JOIN usuarios p ON c.paciente_id = p.id
        JOIN medicos m ON c.medico_id = m.id
        JOIN usuarios m_u ON m.usuario_id = m_u.id
        WHERE c.estado = 'completada'
        AND c.id NOT IN (SELECT cita_id FROM pagos) -- Excluir las que ya se pagaron
        ORDER BY c.fecha_cita DESC
    ";
    $pendientes_pago = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Caja y Facturación</h1>
</div>

<?php if(isset($_GET['mensaje'])): ?>
    <div class="alert alert-success">Pago registrado exitosamente. 💰</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Consultas Pendientes de Cobro</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Folio Cita</th>
                    <th>Fecha</th>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pendientes_pago)): ?>
                    <?php foreach ($pendientes_pago as $cita): ?>
                    <tr>
                        <td>#<?= $cita['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></td>
                        <td><?= htmlspecialchars($cita['pac_nombre'] . ' ' . $cita['pac_apellido']) ?></td>
                        <td>Dr. <?= htmlspecialchars($cita['doc_nombre']) ?> (<?= $cita['especialidad'] ?>)</td>
                        <td>
                            <button class="btn btn-success btn-sm" 
                                    onclick="prepararCobro(<?= $cita['id'] ?>, '<?= $cita['pac_nombre'] ?>')">
                                <i class="fas fa-cash-register me-1"></i> Cobrar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4">No hay cobros pendientes. ¡Todo al día!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalCobro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Registrar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/controllers/pagos_controller.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="cita_id" id="inputCitaId">
                    
                    <p class="lead text-center">Cobrando a: <strong id="nombrePacienteDisplay"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Monto a Cobrar ($)</label>
                        <input type="number" name="monto" class="form-control form-control-lg" placeholder="0.00" step="0.50" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select name="metodo_pago" class="form-select" required>
                            <option value="efectivo">Efectivo 💵</option>
                            <option value="tarjeta">Tarjeta de Crédito/Débito 💳</option>
                            <option value="transferencia">Transferencia Bancaria 🏦</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function prepararCobro(idCita, nombrePaciente) {
        document.getElementById('inputCitaId').value = idCita;
        document.getElementById('nombrePacienteDisplay').innerText = nombrePaciente;
        // Abrir el modal manualmente con Bootstrap JS
        var myModal = new bootstrap.Modal(document.getElementById('modalCobro'));
        myModal.show();
    }
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>