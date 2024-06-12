<?php
session_start();
if ($_SESSION['Tipo'] != 'Administrador') {
    header('Location: /login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
</head>
<body>
    <h1>Bienvenido Administrador</h1>
</body>
</html>