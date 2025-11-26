<?php
session_start();
require_once '../config/db.php';

// Solo el admin puede crear usuarios [cite: 28]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['rol'] == 'admin') {
    
    $accion = $_POST['accion'];

    if ($accion == 'crear') {
        $nombre = trim($_POST['nombre']);
        $apellidos = trim($_POST['apellidos']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        
        // Datos extra (si vienen del formulario)
        $especialidad = $_POST['especialidad'] ?? null;
        $cedula = $_POST['cedula'] ?? null;

        // Encriptar contraseña siempre
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // 1. INICIAR TRANSACCIÓN (Modo Dios activado)
            $pdo->beginTransaction();

            // 2. Insertar en tabla USUARIOS
            $sqlUser = "INSERT INTO usuarios (nombre, apellidos, email, password, rol) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sqlUser);
            $stmt->execute([$nombre, $apellidos, $email, $password_hash, $rol]);
            
            // Obtener el ID del usuario recién creado
            $user_id = $pdo->lastInsertId();

            // 3. Insertar datos específicos según el rol
            if ($rol == 'medico') {
                if(empty($especialidad)) throw new Exception("La especialidad es obligatoria para médicos");
                
                $sqlMedico = "INSERT INTO medicos (usuario_id, especialidad, cedula_profesional) VALUES (?, ?, ?)";
                $stmtMedico = $pdo->prepare($sqlMedico);
                $stmtMedico->execute([$user_id, $especialidad, $cedula]);
            }
            // Aquí podrías agregar elseif ($rol == 'paciente') para guardar sus datos extra si quisieras

            // 4. CONFIRMAR TODO (Commit)
            $pdo->commit();

            header("Location: ../views/admin/usuarios.php?mensaje=creado");
            exit();

        } catch (Exception $e) {
            // SI ALGO FALLA, DESHACER TODO (Rollback)
            $pdo->rollBack();
            
            // Si es error de duplicado (email repetido)
            if ($e->getCode() == 23000) {
                $error = "El correo electrónico ya está registrado.";
            } else {
                $error = $e->getMessage();
            }
            header("Location: ../views/admin/usuarios.php?error=" . urlencode($error));
            exit();
        }
    }
} else {
    header("Location: ../index.php");
}
?>