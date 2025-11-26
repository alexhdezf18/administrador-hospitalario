<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Universitaria</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="/assets/css/estilos.css?v=2.0">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🏥 Sistema Hospitalario</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Hola, <strong><?php echo $_SESSION['nombre'] ?? 'Usuario'; ?></strong>
                </span>
                <a href="/controllers/logout.php" class="btn btn-danger btn-sm">Salir</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">