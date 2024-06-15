<?php
session_start();
if ($_SESSION['Tipo'] != 'Administrador') {
    header('Location: /index.php');
    exit();
}

include '../db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

// Función para obtener todos los usuarios
function getUsuarios($pdo) {
    $stmt = $pdo->query("SELECT * FROM usuarios");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Insertar un nuevo usuario
if (isset($_POST['add'])) {
    $Rut = $_POST['Rut'];
    $Correo = $_POST['Correo'];
    $Contraseña = password_hash($_POST['Contraseña'], PASSWORD_DEFAULT); // Hash de contraseña
    $Tipo = $_POST['Tipo'];

    $stmt = $pdo->prepare("INSERT INTO usuarios (Rut, Correo, Contraseña, Tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$Rut, $Correo, $Contraseña, $Tipo]);

    header("Location: manage_usuarios.php"); // Redirección consistente
    exit();
}

// Eliminar un usuario
if (isset($_GET['delete'])) {
    $Rut = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE Rut = ?");
    $stmt->execute([$Rut]);

    header("Location: manage_usuarios.php"); // Redirección consistente
    exit();
}

// Actualizar un usuario
if (isset($_POST['update'])) {
    $Rut = $_POST['Rut'];
    $Correo = $_POST['Correo'];
    $Contraseña = password_hash($_POST['Contraseña'], PASSWORD_DEFAULT); // Hash de contraseña
    $Tipo = $_POST['Tipo'];

    $stmt = $pdo->prepare("UPDATE usuarios SET Correo = ?, Contraseña = ?, Tipo = ? WHERE Rut = ?");
    $stmt->execute([$Correo, $Contraseña, $Tipo, $Rut]);

    header("Location: manage_usuarios.php"); // Redirección consistente
    exit();
}

$usuarios = getUsuarios($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
    <!-- Enlace a Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
        }
    </style>
</head>
<body class="container">
    <h1 class="text-center mb-4">Gestionar Usuarios</h1>
    <div class="row mb-3">
        <div class="col-md-6 mx-auto">
            <form action="manage_usuarios.php" method="post" class="card card-body">
                <div class="mb-3">
                    <label for="Rut" class="form-label">Rut</label>
                    <input type="text" class="form-control" id="Rut" name="Rut" placeholder="Rut" required>
                </div>
                <div class="mb-3">
                    <label for="Correo" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="Correo" name="Correo" placeholder="Correo" required>
                </div>
                <div class="mb-3">
                    <label for="Contraseña" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="Contraseña" name="Contraseña" placeholder="Contraseña" required>
                </div>
                <div class="mb-3">
                    <label for="Tipo" class="form-label">Tipo</label>
                    <input type="text" class="form-control" id="Tipo" name="Tipo" placeholder="Tipo" required>
                </div>
                <button type="submit" name="add" class="btn btn-primary">Agregar Usuario</button>
            </form>
        </div>
    </div>
    <hr>
    <h2 class="text-center">Lista de Usuarios</h2>
    <table class="table table-striped">
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
                <form action="manage_usuarios.php" method="post">
                    <td><input type="text" name="Rut" class="form-control" value="<?= $usuario['Rut']; ?>" readonly></td>
                    <td><input type="text" name="Correo" class="form-control" value="<?= $usuario['Correo']; ?>"></td>
                    <td><input type="password" name="Contraseña" class="form-control" placeholder="Nueva contraseña"></td>
                    <td><input type="text" name="Tipo" class="form-control" value="<?= $usuario['Tipo']; ?>"></td>
                    <td>
                        <button type="submit" name="update" class="btn btn-success">Actualizar</button>
                        <a href="?delete=<?= $usuario['Rut']; ?>" class="btn btn-danger">Eliminar</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Enlace a Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>