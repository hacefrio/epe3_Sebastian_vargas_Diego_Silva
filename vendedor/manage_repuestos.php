<?php
session_start();
if ($_SESSION['Tipo'] != 'Vendedor') {
    header('Location: /index.php');
    exit();
}
?>
<?php
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

    $stmt = $pdo->prepare("INSERT INTO repuestos (NombreRepuesto, PrecioUnitario, CantidadStock,Proveedor) VALUES (?, ?, ?,?)");
    $stmt->execute([$NombreRepuesto, $PrecioUnitario, $CantidadStock,$Proveedor]);

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

// Formulario para actualizar
if (isset($_POST['update'])) {
    $RepuestoID = $_POST['RepuestoID'];
    $NombreRepuesto = $_POST['NombreRepuesto'];
    $PrecioUnitario = $_POST['PrecioUnitario'];
    $CantidadStock = $_POST['CantidadStock'];
    $Proveedor = $_POST['Proveedor'];

    $stmt = $pdo->prepare("UPDATE repuestos SET NombreRepuesto = ?, PrecioUnitario = ?, CantidadStock = ?, Proveedor =? WHERE RepuestoID = ?");
    $stmt->execute([$NombreRepuesto, $PrecioUnitario, $CantidadStock,$Proveedor, $RepuestoID]);

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
</head>
<body>
    <h1>Gestionar Repuestos</h1>
    <form action="manage_repuestos.php" method="post">
        <input type="text" name="NombreRepuesto" placeholder="Nombre del repuesto" required>
        <input type="number" name="PrecioUnitario" placeholder="Precio unitario" required>
        <input type="number" name="CantidadStock" placeholder="Cantidad Stock" required>
        <input type="text" name="Proveedor" placeholder="Proveedor" required>
        <button type="submit" name="add">Agregar Repuesto</button>
    </form>
    <hr>
    <h2>Lista de Repuestos</h2>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>Descripción</th>
                <th>PrecioUnitario</th>
                <th>Cantidad Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($repuestos as $repuesto): ?>
            <tr>
                <form action="manage_repuestos.php" method="post">
                <td><input type="text" name="RepuestoID" value="<?= $repuesto['RepuestoID']; ?>" readonly></td>
                    <td><input type="text" name="NombreRepuesto" value="<?= $repuesto['NombreRepuesto']; ?>"></td>
                    <td><input type="number" name="PrecioUnitario" value="<?= $repuesto['PrecioUnitario']; ?>"></td>
                    <td><input type="number" name="CantidadStock" value="<?= $repuesto['CantidadStock']; ?>"></td>
                    <td><input type="text" name="Proveedor" value="<?= $repuesto['Proveedor']; ?>"></td>
                    <td>
                        <button type="submit" name="update">Actualizar</button>
                        <a href="?delete=<?= $repuesto['RepuestoID']; ?>">Eliminar</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>