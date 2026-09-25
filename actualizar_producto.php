<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inventario.php');
    exit;
}

$id_producto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
$nombre = trim($_POST['nombre'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$talla = trim($_POST['talla'] ?? '');
$color = trim($_POST['color'] ?? '');
$precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
$stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
$errores = [];

if (!$id_producto || $id_producto < 1) $errores[] = 'El producto seleccionado no es válido.';
if ($nombre === '' || $marca === '' || $talla === '' || $color === '') $errores[] = 'Todos los datos descriptivos son obligatorios.';
if ($precio === false || $precio < 0) $errores[] = 'El precio debe ser un número válido.';
if ($stock === false || $stock < 0) $errores[] = 'El stock debe ser un entero igual o mayor que cero.';

$stmt_actual = $conn->prepare('SELECT imagen FROM productos WHERE id_producto = ?');
$stmt_actual->bind_param('i', $id_producto);
$stmt_actual->execute();
$producto_actual = $stmt_actual->get_result()->fetch_assoc();
$stmt_actual->close();

if (!$producto_actual) $errores[] = 'El producto no existe.';
$ruta_imagen = $producto_actual['imagen'] ?? null;

if (!empty($_FILES['imagen']['name'])) {
    $archivo = $_FILES['imagen'];
    $tipos_permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipo = mime_content_type($archivo['tmp_name']);

    if ($archivo['error'] !== UPLOAD_ERR_OK || !isset($tipos_permitidos[$tipo])) $errores[] = 'La imagen debe ser JPG, PNG o WEBP válida.';
    if ($archivo['size'] > 5 * 1024 * 1024) $errores[] = 'La imagen no puede superar los 5 MB.';

    if (!$errores) {
        $nombre_archivo = 'producto_' . bin2hex(random_bytes(8)) . '.' . $tipos_permitidos[$tipo];
        $ruta_fisica = __DIR__ . '/img/' . $nombre_archivo;
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_fisica)) {
            $errores[] = 'No fue posible guardar la nueva imagen.';
        } else {
            $ruta_imagen = 'img/' . $nombre_archivo;
        }
    }
}

if ($errores) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><link rel="stylesheet" href="css/estilos.css"><title>Error al actualizar</title></head><body><main class="contenedor"><section class="mensaje mensaje-error"><h1>No fue posible actualizar el producto</h1><ul>';
    foreach ($errores as $error) echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
    echo '</ul><a class="boton principal" href="inventario.php?editar=' . (int) $id_producto . '">Regresar a la edición</a></section></main></body></html>';
    exit;
}

$stmt = $conn->prepare(
    'UPDATE productos
     SET nombre = ?, marca = ?, talla = ?, color = ?, precio = ?, stock = ?, imagen = ?
     WHERE id_producto = ?'
);
$stmt->bind_param('ssssdisi', $nombre, $marca, $talla, $color, $precio, $stock, $ruta_imagen, $id_producto);

if (!$stmt->execute()) {
    die('No fue posible actualizar el producto.');
}

header('Location: inventario.php');
exit;
