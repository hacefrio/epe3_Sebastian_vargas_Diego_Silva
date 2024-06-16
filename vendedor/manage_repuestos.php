<?php
session_start();
if ($_SESSION['Tipo'] != 'Vendedor') {
    header('Location: /login.php');
    exit();
}

include '../db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

// Función para obtener todos los repuestos
function getRepuestos($pdo) {
    $stmt = $pdo->query("SELECT * FROM repuestos");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Insertar un nuevo repuesto
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

// Eliminar un repuesto
if (isset($_GET['delete'])) {
    $RepuestoID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM repuestos WHERE RepuestoID = ?");
    $stmt->execute([$RepuestoID]);

    header("Location: manage_repuestos.php");
    exit();
}

// Actualizar un repuesto
if (isset($_POST['update'])) {
    $RepuestoID = $_POST['RepuestoID'];
    $NombreRepuesto = $_POST['NombreRepuesto'];
    $PrecioUnitario = $_POST['PrecioUnitario'];
    $CantidadStock = $_POST['CantidadStock'];
    $Proveedor = $_POST['Proveedor'];

    $stmt = $pdo->prepare("UPDATE repuestos SET NombreRepuesto = ?, PrecioUnitario = ?, CantidadStock = ?, Proveedor = ? WHERE RepuestoID = ?");
    $stmt->execute([$NombreRepuesto, $PrecioUnitario, $CantidadStock, $Proveedor, $RepuestoID]);

    header("Location: manage_repuestos.php");
    exit();
}

$repuestos = getRepuestos($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function toggleVisibility(section) {
            var addSection = document.getElementById('addSection');
            var viewSection = document.getElementById('viewSection');
            if (section === 'add') {
                addSection.style.display = 'block';
                viewSection.style.display = 'none';
            } else {
                addSection.style.display = 'none';
                viewSection.style.display = 'block';
            }
        }
    </script>
</head>
<body class="container">

    <!-- Botones para alternar vistas -->
    <div class="text-center mb-4">

        <h1 class="text-center mb-4">Gestionar Repuestos</h1>
        <button onclick="toggleVisibility('add')" class="btn btn-primary">Agregar Repuesto</button>
        <button onclick="toggleVisibility('view')" class="btn btn-secondary">Ver Repuestos</button>
        <a href="../login.php" class="btn btn-danger">Cerrar Sesión</a>
    
    </div>




    <div id="addSection" style="display:none;" class="mt-4">
        <form action="manage_repuestos.php" method="post">
            <div class="mb-3">
                <label for="NombreRepuesto" class="form-label">Nombre del repuesto</label>
                <input type="text" class="form-control" name="NombreRepuesto" required>
            </div>
            <div class="mb-3">
                <label for="PrecioUnitario" class="form-label">Precio unitario</label>
                <input type="number" class="form-control" name="PrecioUnitario" required>
            </div>
            <div class="mb-3">
                <label for="CantidadStock" class="form-label">Cantidad Stock</label>
                <input type="number" class="form-control" name="CantidadStock" required>
            </div>
            <div class="mb-3">
                <label for="Proveedor" class="form-label">Proveedor</label>
                <input type="text" class="form-control" name="Proveedor" required>
            </div>
            <button type="submit" name="add" class="btn btn-primary">Agregar Repuesto</button>
        </form>
    </div>

    <div id="viewSection" style="display:none;" class="mt-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Repuesto</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad Stock</th>
                    <th>Proveedor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repuestos as $repuesto): ?>
                <tr>
                    <form action="manage_repuestos.php" method="post">
                        <td><input type="hidden" name="RepuestoID" value="<?= $repuesto['RepuestoID']; ?>"><?= $repuesto['RepuestoID']; ?></td>
                        <td><input type="text" class="form-control" name="NombreRepuesto" value="<?= $repuesto['NombreRepuesto']; ?>"></td>
                        <td><input type="number" class="form-control" name="PrecioUnitario" value="<?= $repuesto['PrecioUnitario']; ?>"></td>
                        <td><input type="number" class="form-control" name="CantidadStock" value="<?= $repuesto['CantidadStock']; ?>"></td>
                        <td><input type="text" class="form-control" name="Proveedor" value="<?= $repuesto['Proveedor']; ?>"></td>
                        <td>
                            <button type="submit" name="update" class="btn btn-success">Actualizar</button>
                            <a href="?delete=<?= $repuesto['RepuestoID']; ?>" class="btn btn-danger">Eliminar</a>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>