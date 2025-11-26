<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

$pagos = [];
try {
    // Consulta: Traer todos los pagos con detalle de quién pagó y qué cita fue
    $sql = "
        SELECT p.id, p.monto, p.metodo_pago, p.fecha_pago,
               u.nombre, u.apellidos
        FROM pagos p
        JOIN citas c ON p.cita_id = c.id
        JOIN usuarios u ON c.paciente_id = u.id
        ORDER BY p.fecha_pago DESC
    ";
    $pagos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reporte Financiero</h1>
    <button onclick="window.print()" class="btn btn-outline-secondary">
        <i class="fas fa-print me-2"></i> Imprimir Reporte
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title text-success mb-4">Historial de Ingresos</h5>
        
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th># Folio</th>
                        <th>Fecha de Pago</th>
                        <th>Paciente</th>
                        <th>Método</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pagos)): ?>
                        <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><?= $pago['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                            <td><?= htmlspecialchars($pago['nombre'] . ' ' . $pago['apellidos']) ?></td>
                            <td><?= ucfirst($pago['metodo_pago']) ?></td>
                            <td class="text-end fw-bold">$<?= number_format($pago['monto'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr class="table-dark">
                            <td colspan="4" class="text-end"><strong>TOTAL INGRESOS:</strong></td>
                            <td class="text-end">
                                <strong>$<?= number_format(array_sum(array_column($pagos, 'monto')), 2) ?></strong>
                            </td>
                        </tr>

                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No hay registros financieros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        #sidebarMenu, header, button { display: none; }
        main { width: 100%; margin: 0; padding: 0; }
    }
</style>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>