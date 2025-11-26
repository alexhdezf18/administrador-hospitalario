<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Universitaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/estilos.css?v=2.0">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <h2>Bienvenido</h2>
            <p class="text-muted">Sistema de Gestión Hospitalaria</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form action="./controllers/login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="admin@hospital.com">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="******">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
            </div>
        </form>
        
        <div class="mt-3 text-center">
            <small class="text-muted">¿Olvidaste tu contraseña? Contacta al soporte.</small>
        </div>
    </div>

</body>
</html>