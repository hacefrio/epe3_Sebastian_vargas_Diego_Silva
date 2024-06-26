<?php
session_start();
if ($_SESSION['Tipo'] != 'Vendedor') {
    header('Location: /login.php');
    exit();
}

include '../db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

function getRepuestos($pdo, $searchTerm = '', $page = 1, $perPage = 10) {
    $start = ($page - 1) * $perPage;
    $searchTerm = '%' . $searchTerm . '%';
    $stmt = $pdo->prepare("SELECT * FROM repuestos WHERE NombreRepuesto LIKE ? OR Proveedor LIKE ? ORDER BY RepuestoID LIMIT ?, ?");
    $stmt->bindParam(1, $searchTerm);
    $stmt->bindParam(2, $searchTerm);
    $stmt->bindParam(3, $start, PDO::PARAM_INT);
    $stmt->bindParam(4, $perPage, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalRepuestos($pdo, $searchTerm = '') {
    $searchTerm = '%' . $searchTerm . '%';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM repuestos WHERE NombreRepuesto LIKE ? OR Proveedor LIKE ?");
    $stmt->execute([$searchTerm, $searchTerm]);
    return $stmt->fetchColumn();
}

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$perPage = 10;

$repuestos = getRepuestos($pdo, $search, $page, $perPage);
$totalRepuestos = getTotalRepuestos($pdo, $search);
$totalPages = ceil($totalRepuestos / $perPage);

if (isset($_POST['add'])) {
    $NombreRepuesto = $_POST['NombreRepuesto'];
    $PrecioUnitario = $_POST['PrecioUnitario'];
    $CantidadStock = $_POST['CantidadStock'];
    $Proveedor = $_POST['Proveedor'];

    $stmt = $pdo->prepare("INSERT INTO repuestos (NombreRepuesto, PrecioUnitario, CantidadStock, Proveedor) VALUES (?, ?, ?, ?)");
    $stmt->execute([$NombreRepuesto, $PrecioUnitario, $CantidadStock, $Proveedor]);

    header("Location: manage_repuestos.php");
    exit();
}

if (isset($_GET['delete'])) {
    $RepuestoID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM repuestos WHERE RepuestoID = ?");
    $stmt->execute([$RepuestoID]);

    header("Location: manage_repuestos.php?search=$search&page=$page");
    exit();
}

if (isset($_POST['update'])) {
    $RepuestoID = $_POST['RepuestoID'];
    $NombreRepuesto = $_POST['NombreRepuesto'];
    $PrecioUnitario = $_POST['PrecioUnitario'];
    $CantidadStock = $_POST['CantidadStock'];
    $Proveedor = $_POST['Proveedor'];

    $stmt = $pdo->prepare("UPDATE repuestos SET NombreRepuesto = ?, PrecioUnitario = ?, CantidadStock = ?, Proveedor = ? WHERE RepuestoID = ?");
    $stmt->execute([$NombreRepuesto, $PrecioUnitario, $CantidadStock, $Proveedor, $RepuestoID]);

    header("Location: manage_repuestos.php?search=$search&page=$page");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function showSection(section) {
            var addSection = document.getElementById('addRepuestoSection');
            var viewSection = document.getElementById('viewRepuestoSection');
            if (section === 'add') {
                addSection.style.display = 'block';
                viewSection.style.display = 'none';
            } else {
                addSection.style.display = 'none';
                viewSection.style.display = 'block';
            }
        }
        window.onload = function() {
            showSection('view'); // Muestra la tabla de repuestos al cargar la página
        };
    </script>
</head>
<body class="container">
    <h1 class="text-center mb-4">Gestionar Repuestos</h1>
    <div class="text-center mb-4">
        <button onclick="showSection('add')" class="btn btn-primary">Agregar Repuesto</button>
        <button onclick="showSection('view')" class="btn btn-secondary">Ver Repuestos</button>
        <a href="../login.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

    <div id="addRepuestoSection" style="display:none;">
        <h2>Agregar Nuevo Repuesto</h2>
        <form action="manage_repuestos.php" method="post">
            <div class="mb-3">
                <label for="NombreRepuesto" class="form-label">Nombre del repuesto</label>
                <input type="text" class="form-control" name="NombreRepuesto" required>
            </div>
            <div class="mb-3">
                <label for="PrecioUnitario" class="form-label">Precio Unitario</label>
                <input type="number" class="form-control" name="PrecioUnitario" required>
            </div>
            <div class="mb-3">
                <label for="CantidadStock" class="form-label">Cantidad en Stock</label>
                <input type="number" class="form-control" name="CantidadStock" required>
            </div>
            <div class="mb-3">
                <label for="Proveedor" class="form-label">Proveedor</label>
                <input type="text" class="form-control" name="Proveedor" required>
            </div>
            <button type="submit" name="add" class="btn btn-primary">Agregar Repuesto</button>
        </form>
    </div>

    <div id="viewRepuestoSection" style="display:none;">
        <h2>Lista de Repuestos</h2>
        <form action="manage_repuestos.php" method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o proveedor" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-outline-secondary">Buscar</button>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad en Stock</th>
                    <th>Proveedor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repuestos as $repuesto): ?>
                <tr>
                    <form action="manage_repuestos.php?search=<?= htmlspecialchars($search) ?>&page=<?= $page ?>" method="post">
                        <td><input type="text" name="RepuestoID" value="<?= htmlspecialchars($repuesto['RepuestoID']); ?>" readonly class="form-control"></td>
                        <td><input type="text" name="NombreRepuesto" value="<?= htmlspecialchars($repuesto['NombreRepuesto']); ?>" class="form-control"></td>
                        <td><input type="number" name="PrecioUnitario" value="<?= htmlspecialchars($repuesto['PrecioUnitario']); ?>" class="form-control"></td>
                        <td><input type="number" name="CantidadStock" value="<?= htmlspecialchars($repuesto['CantidadStock']); ?>" class="form-control"></td>
                        <td><input type="text" name="Proveedor" value="<?= htmlspecialchars($repuesto['Proveedor']); ?>" class="form-control"></td>
                        <td>
                            <button type="submit" name="update" class="btn btn-success">Actualizar</button>
                            <a href="?delete=<?= htmlspecialchars($repuesto['RepuestoID']); ?>&search=<?= htmlspecialchars($search) ?>&page=<?= $page ?>" class="btn btn-danger">Eliminar</a>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
