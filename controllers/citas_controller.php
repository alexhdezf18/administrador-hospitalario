<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $accion = $_POST['accion'];

    if ($accion == 'crear') {
        // 1. Recibir datos del formulario
        $paciente_id = $_POST['paciente_id'];
        $medico_id   = $_POST['medico_id'];
        $fecha       = $_POST['fecha'];
        $hora        = $_POST['hora'];
        $motivo      = trim($_POST['motivo']); // Opcional, lo guardaremos en 'observaciones' si gustas

        try {
            // 2. VALIDACIÓN DE DISPONIBILIDAD [Requisito del proyecto]
            // Consultamos si ya existe una cita para ese médico, en esa fecha y hora, que no esté cancelada
            $sqlCheck = "SELECT COUNT(*) FROM citas 
                         WHERE medico_id = ? AND fecha_cita = ? AND hora_cita = ? 
                         AND estado != 'cancelada'";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$medico_id, $fecha, $hora]);
            $existe = $stmtCheck->fetchColumn();

            if ($existe > 0) {
                // Si ya hay cita, devolvemos error y detenemos todo
                header("Location: /views/admin/citas.php?error=" . urlencode("El médico ya tiene una cita agendada en ese horario."));
                exit();
            }

            // 3. Si está libre, INSERTAMOS la cita
            $sqlInsert = "INSERT INTO citas (paciente_id, medico_id, fecha_cita, hora_cita, observaciones, estado) 
                          VALUES (?, ?, ?, ?, ?, 'pendiente')";
            $stmt = $pdo->prepare($sqlInsert);
            $stmt->execute([$paciente_id, $medico_id, $fecha, $hora, $motivo]);

            // Éxito
            header("Location: /views/admin/citas.php?mensaje=creado");
            exit();

        } catch (PDOException $e) {
            header("Location: /views/admin/citas.php?error=" . urlencode("Error de BD: " . $e->getMessage()));
            exit();
        }
    }

    // Aquí podemos agregar más acciones luego, como 'cancelar' o 'completar'

} else {
    header("Location: /index.php");
}
?>