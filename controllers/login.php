<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Verificamos contraseña (sea texto plano o encriptada)
            if (password_verify($password, $usuario['password']) || $password === $usuario['password']) {
                
                // Guardar datos en sesión
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redireccionar según el ROL
                switch ($usuario['rol']) {
                    case 'admin':
                        header("Location: ../views/admin/index.php"); 
                        break;
                    case 'recepcionista':
                        header("Location: ../views/recepcionista/index.php");
                        break;
                    case 'medico':
                        header("Location: ../views/medico/index.php");
                        break;
                    case 'paciente':
                        header("Location: ../views/paciente/index.php");
                        break;
                    default:
                        header("Location: ../index.php");
                }
                exit();
            } else {
                header("Location: ../index.php?error=Contraseña incorrecta");
                exit();
            }
        } else {
            header("Location: ../index.php?error=Usuario no encontrado");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../index.php?error=Error de conexión");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>