<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inventario.php');
    exit;
}

$id_producto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto < 1) {
    header('Location: inventario.php');
    exit;
}

$stmt = $conn->prepare('DELETE FROM productos WHERE id_producto = ?');
$stmt->bind_param('i', $id_producto);

if (!$stmt->execute()) {
    $mensaje = 'No se puede eliminar este producto porque está relacionado con una venta registrada.';
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><link rel="stylesheet" href="css/estilos.css"><title>No se puede eliminar</title></head><body><main class="contenedor"><section class="mensaje mensaje-error"><h1>No fue posible eliminar el producto</h1><p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p><a class="boton principal" href="inventario.php">Regresar al inventario</a></section></main></body></html>';
    exit;
}

header('Location: inventario.php');
exit;
