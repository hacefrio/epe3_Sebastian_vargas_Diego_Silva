<?php
session_start();

// Redirigir al login si el usuario no está logeado
if (!isset($_SESSION['rut'])) {
    header('Location: login.php');
    exit();
}

// Suponemos que el tipo de usuario está almacenado en la sesión bajo la clave 'Tipo'
$tipoUsuario = $_SESSION['Tipo'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container">
    
    <!-- Botones para alternar vistas -->
    <div class="text-center mb-4">


    <h1>Bienvenido a la Página Principal</h1>

        <?php if ($tipoUsuario == 'Administrador'): ?>
        <h2>Opciones de Administrador</h2>
        <a href="administrador/manage_usuarios.php" class="btn btn-primary">Agregar Usuarios</a>
        <?php elseif ($tipoUsuario == 'Vendedor'): ?>
        <h2>Opciones de Vendedor</h2>
        <a href="vendedor/manage_repuestos.php" class="btn btn-primary">Agregar Repuesto</a>
        <?php endif; ?>
        <a href="login.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

</body>
</html>