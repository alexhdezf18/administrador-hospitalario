<?php
session_start();
session_unset();    // Elimina las variables de sesión
session_destroy();  // Destruye la sesión por completo
header("Location: ../index.php"); // Regresa al Login
exit();
?>