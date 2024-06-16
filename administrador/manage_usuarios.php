<?php
session_start();
if ($_SESSION['Tipo'] != 'Administrador') {
    header('Location: /index.php');
    exit();
}

include '../db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

// Función para obtener todos los usuarios con paginación y búsqueda
function getUsuarios($pdo, $searchTerm = '', $page = 1, $perPage = 10) {
    $start = ($page - 1) * $perPage;
    $searchTerm = '%' . $searchTerm . '%';
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE Rut LIKE ? OR Correo LIKE ? OR Tipo LIKE ? ORDER BY Rut LIMIT ?, ?");
    $stmt->bindParam(1, $searchTerm);
    $stmt->bindParam(2, $searchTerm);
    $stmt->bindParam(3, $searchTerm);
    $stmt->bindParam(4, $start, PDO::PARAM_INT);
    $stmt->bindParam(5, $perPage, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Contar el total de usuarios para paginación
function getTotalUsuarios($pdo, $searchTerm = '') {
    $searchTerm = '%' . $searchTerm . '%';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE Rut LIKE ? OR Correo LIKE ? OR Tipo LIKE ?");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchColumn();
}

// Procesamiento de la búsqueda y paginación
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Define cuántos usuarios quieres mostrar por página

$usuarios = getUsuarios($pdo, $search, $page, $perPage);
$totalUsuarios = getTotalUsuarios($pdo, $search);
$totalPages = ceil($totalUsuarios / $perPage);

// Actualización de usuarios
if (isset($_POST['update'])) {
    $Rut = $_POST['Rut'];
    $Correo = $_POST['Correo'];
    $Contraseña = password_hash($_POST['Contraseña'], PASSWORD_DEFAULT);
    $Tipo = $_POST['Tipo'];

    $stmt = $pdo->prepare("UPDATE usuarios SET Correo = ?, Contraseña = ?, Tipo = ? WHERE Rut = ?");
    $stmt->execute([$Correo, $Contraseña, $Tipo, $Rut]);

    header("Location: manage_usuarios.php?search=$search&page=$page");
    exit();
}

// Eliminar usuario
if (isset($_GET['delete'])) {
    $Rut = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE Rut = ?");
    $stmt->execute([$Rut]);

    header("Location: manage_usuarios.php?search=$search&page=$page");
    exit();
}

// Insertar un nuevo usuario
if (isset($_POST['add'])) {
    $Rut = $_POST['Rut'];
    $Correo = $_POST['Correo'];
    $Contraseña = password_hash($_POST['Contraseña'], PASSWORD_DEFAULT);
    $Tipo = $_POST['Tipo'];

    $stmt = $pdo->prepare("INSERT INTO usuarios (Rut, Correo, Contraseña, Tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$Rut, $Correo, $Contraseña, $Tipo]);

    header("Location: manage_usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function showSection(section) {
            document.getElementById('addUserSection').style.display = 'none';
            document.getElementById('viewUserSection').style.display = 'none';
            document.getElementById(section).style.display = 'block';
        }
    </script>
</head>
<body class="container">
    <h1 class="text-center mb-4">Gestionar Usuarios</h1>

    <!-- Botones para alternar vistas -->
    <div class="text-center mb-4">
        <button onclick="showSection('addUserSection')" class="btn btn-primary">Agregar Usuario</button>
        <button onclick="showSection('viewUserSection')" class="btn btn-secondary">Ver Usuarios</button>
    </div>

    <!-- Sección para agregar usuarios -->
    <div id="addUserSection" style="display:none;">
        <h2>Agregar Nuevo Usuario</h2>
        <form action="manage_usuarios.php" method="post">
            <div class="mb-3">
                <label for="RutNuevo" class="form-label">Rut</label>
                <input type="text" class="form-control" id="RutNuevo" name="Rut" required>
            </div>
            <div class="mb-3">
                <label for="CorreoNuevo" class="form-label">Correo</label>
                <input type="email" class="form-control" id="CorreoNuevo" name="Correo" required>
            </div>
            <div class="mb-3">
                <label for="ContraseñaNueva" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="ContraseñaNueva" name="Contraseña" required>
            </div>
            <div class="mb-3">
                <label for="TipoNuevo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="TipoNuevo" name="Tipo" required>
            </div>
            <button type="submit" name="add" class="btn btn-primary">Agregar Usuario</button>
        </form>
    </div>

    <!-- Sección para ver y editar usuarios -->
    <div id="viewUserSection" style="display:none;">
        <h2>Lista de Usuarios</h2>
        <form action="manage_usuarios.php" method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por Rut, Correo o Tipo" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-outline-secondary">Buscar</button>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Rut</th>
                    <th>Correo</th>
                    <th>Contraseña</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <form action="manage_usuarios.php?search=<?= htmlspecialchars($search) ?>&page=<?= $page ?>" method="post">
                        <td><input type="text" name="Rut" value="<?= htmlspecialchars($usuario['Rut']); ?>" readonly class="form-control"></td>
                        <td><input type="email" name="Correo" value="<?= htmlspecialchars($usuario['Correo']); ?>" class="form-control"></td>
                        <td><input type="password" name="Contraseña" placeholder="Nueva contraseña" class="form-control"></td>
                        <td><input type="text" name="Tipo" value="<?= htmlspecialchars($usuario['Tipo']); ?>" class="form-control"></td>
                        <td>
                            <button type="submit" name="update" class="btn btn-success">Actualizar</button>
                            <a href="?delete=<?= htmlspecialchars($usuario['Rut']); ?>&search=<?= htmlspecialchars($search) ?>&page=<?= $page ?>" class="btn btn-danger">Eliminar</a>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Paginación -->
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= htmlspecialchars($search) ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= htmlspecialchars($search) ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
</body>
</html>
