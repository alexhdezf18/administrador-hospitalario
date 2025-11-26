<?php
session_start();

// 1. Inicializamos la variable VACÍA para evitar el error "Undefined variable"
$usuarios = [];
$error_db = null;

// 2. Validar Rol Admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

// 3. Conexión a Base de Datos (Ruta absoluta para evitar errores)
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

// 4. Consultar usuarios
try {
    $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY fecha_creacion DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC); // Traemos los datos como array asociativo
} catch (PDOException $e) {
    $error_db = "Error al cargar usuarios: " . $e->getMessage();
}

// 5. Incluir Header y Sidebar (Rutas absolutas)
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Usuarios</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
        <i class="fas fa-plus"></i> Nuevo Usuario
    </button>
</div>

<?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'creado'): ?>
    <div class="alert alert-success">Usuario creado correctamente.</div>
<?php endif; ?>

<?php if($error_db): ?>
    <div class="alert alert-danger"><?= $error_db ?></div>
<?php endif; ?>

<div class="table-responsive bg-white shadow-sm rounded p-3">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Fecha Registro</th>
                <th>es</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="badge bg-<?= $user['rol'] == 'admin' ? 'danger' : ($user['rol'] == 'medico' ? 'success' : 'info') ?>">
                            <?= ucfirst($user['rol']) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($user['fecha_creacion'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No hay usuarios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Registrar Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="/controllers/usuarios_controller.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rol</label>
                        <select name="rol" id="selectRol" class="form-select" required onchange="toggleDoctorFields()">
                            <option value="paciente">Paciente</option>
                            <option value="medico">Médico</option>
                            <option value="recepcionista">Recepcionista</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div id="doctorFields" style="display: none;" class="bg-light p-3 rounded border">
                        <h6 class="text-primary">Datos del Médico</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Especialidad</label>
                                <select name="especialidad" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <option value="Medicina General">Medicina General</option>
                                    <option value="Cardiología">Cardiología</option>
                                    <option value="Pediatría">Pediatría</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cédula Profesional</label>
                                <input type="text" name="cedula" class="form-control">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

<script>
    function toggleDoctorFields() {
        var rol = document.getElementById('selectRol').value;
        var campos = document.getElementById('doctorFields');
        
        if (rol === 'medico') {
            campos.style.display = 'block';
        } else {
            campos.style.display = 'none';
        }
    }
</script>