<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="p-2 text-center text-white border-bottom border-secondary mb-3">
            <h6 class="text-uppercase mb-0">Menú</h6>
            <small class="text-muted"><?= ucfirst($_SESSION['rol'] ?? 'Usuario') ?></small>
        </div>

        <ul class="nav flex-column">
            
            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/views/admin/index.php">
                        <i class="fas fa-home me-2"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/views/admin/usuarios.php">
                        <i class="fas fa-users-cog me-2"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/views/admin/citas.php">
                        <i class="fas fa-calendar-alt me-2"></i> Gestionar Citas
                    </a>
                </li>

            <?php elseif(isset($_SESSION['rol']) && $_SESSION['rol'] == 'medico'): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/views/medico/index.php">
                        <i class="fas fa-calendar-check me-2"></i> Mi Agenda
                    </a>
                </li>
                <?php elseif(isset($_SESSION['rol']) && $_SESSION['rol'] == 'paciente'): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/views/paciente/index.php">
                        <i class="fas fa-home me-2"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/views/paciente/historial.php">
                        <i class="fas fa-file-medical me-2"></i> Mi Historial
                    </a>
                </li>
                
            <?php elseif(isset($_SESSION['rol']) && $_SESSION['rol'] == 'recepcionista'): ?>
                 <li class="nav-item">
                    <a class="nav-link text-white" href="/views/recepcionista/index.php">
                        <i class="fas fa-home me-2"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/views/admin/citas.php">
                        <i class="fas fa-calendar-plus me-2"></i> Citas
                    </a>
                </li>
            <?php endif; ?>

        </ul>
        
        <div class="mt-5 px-3">
            <a href="/controllers/logout.php" class="btn btn-outline-danger btn-sm w-100">
                <i class="fas fa-sign-out-alt me-2"></i> Salir
            </a>
        </div>
    </div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">