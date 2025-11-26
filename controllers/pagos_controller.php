<?php
session_start();
require_once '../config/db.php';

// Validar permisos: Solo Recepcionista o Admin pueden cobrar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && 
   (isset($_SESSION['rol']) && ($_SESSION['rol'] == 'recepcionista' || $_SESSION['rol'] == 'admin'))) {
    
    $cita_id = $_POST['cita_id'];
    $monto = $_POST['monto'];
    $metodo = $_POST['metodo_pago'];

    try {
        // Insertamos el registro del pago
        $sql = "INSERT INTO pagos (cita_id, monto, metodo_pago) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cita_id, $monto, $metodo]);

        // Redirigir con éxito
        // Nota: Si eres admin y cobras, te regresa al admin. Si eres recep, a tu panel.
        $redirect = ($_SESSION['rol'] == 'admin') ? '/views/admin/citas.php' : '/views/recepcionista/pagos.php';
        
        header("Location: $redirect?mensaje=pago_registrado");
        exit();

    } catch (PDOException $e) {
        $error = urlencode("Error al registrar pago: " . $e->getMessage());
        $redirect = ($_SESSION['rol'] == 'admin') ? '/views/admin/citas.php' : '/views/recepcionista/pagos.php';
        header("Location: $redirect?error=" . $error);
        exit();
    }

} else {
    header("Location: /index.php");
    exit();
}
?>