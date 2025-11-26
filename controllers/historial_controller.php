<?php
// controllers/historial_controller.php
session_start();
require_once '../config/db.php';

// Validar que sea un POST y que el usuario sea Médico
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['rol']) && $_SESSION['rol'] == 'medico') {
    
    // 1. Recibir datos del formulario
    $cita_id = $_POST['cita_id'];
    $paciente_id = $_POST['paciente_id'];
    $medico_id = $_POST['medico_id'];
    
    // Limpiamos los textos para evitar espacios extra
    $motivo = trim($_POST['motivo_consulta']);
    $diagnostico = trim($_POST['diagnostico']);
    $tratamiento = trim($_POST['tratamiento']);

    try {
        // 2. INICIAR TRANSACCIÓN (Modo seguro: Todo o nada)
        $pdo->beginTransaction();

        // PASO A: Insertar en la tabla Historial Médico
        $sqlHistorial = "INSERT INTO historial_medico 
                        (cita_id, paciente_id, medico_id, motivo_consulta, diagnostico, tratamiento) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmtH = $pdo->prepare($sqlHistorial);
        $stmtH->execute([$cita_id, $paciente_id, $medico_id, $motivo, $diagnostico, $tratamiento]);

        // PASO B: Actualizar estado de la Cita a 'completada'
        // Esto es importante para que la cita deje de aparecer como pendiente en la agenda
        $sqlUpdate = "UPDATE citas SET estado = 'completada' WHERE id = ?";
        $stmtU = $pdo->prepare($sqlUpdate);
        $stmtU->execute([$cita_id]);

        // 3. CONFIRMAR CAMBIOS (Guardar definitivamente)
        $pdo->commit();

        // Redirigir al médico a su agenda con mensaje de éxito
        header("Location: /views/medico/index.php?mensaje=consulta_finalizada");
        exit();

    } catch (PDOException $e) {
        // Si algo falla, deshacemos cualquier cambio parcial
        $pdo->rollBack();
        
        // Redirigimos con el mensaje de error
        $error = urlencode("Error al guardar la consulta: " . $e->getMessage());
        header("Location: /views/medico/index.php?error=" . $error);
        exit();
    }

} else {
    // Si intentan entrar directo sin ser médico
    header("Location: /index.php");
    exit();
}
?>