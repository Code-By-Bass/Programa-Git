<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inventario.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$talla = trim($_POST['talla'] ?? '');
$color = trim($_POST['color'] ?? '');
$precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
$stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
$errores = [];

if ($nombre === '' || $marca === '' || $talla === '' || $color === '') $errores[] = 'Todos los datos descriptivos son obligatorios.';
if ($precio === false || $precio < 0) $errores[] = 'El precio debe ser un número válido.';
if ($stock === false || $stock < 0) $errores[] = 'El stock debe ser un entero igual o mayor que cero.';

$ruta_imagen = null;
if (!empty($_FILES['imagen']['name'])) {
    $archivo = $_FILES['imagen'];
    $tipos_permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipo = mime_content_type($archivo['tmp_name']);

    if ($archivo['error'] !== UPLOAD_ERR_OK || !isset($tipos_permitidos[$tipo])) $errores[] = 'La imagen debe ser JPG, PNG o WEBP válida.';
    if ($archivo['size'] > 5 * 1024 * 1024) $errores[] = 'La imagen no puede superar los 5 MB.';
    if (!$errores) {
        $nombre_archivo = 'producto_' . bin2hex(random_bytes(8)) . '.' . $tipos_permitidos[$tipo];
        $ruta_fisica = __DIR__ . '/img/' . $nombre_archivo;
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_fisica)) $errores[] = 'No fue posible guardar la imagen.';
        else $ruta_imagen = 'img/' . $nombre_archivo;
    }
}

if ($errores) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><link rel="stylesheet" href="css/estilos.css"><title>Error</title></head><body><main class="contenedor"><section class="mensaje mensaje-error"><h1>No fue posible registrar el producto</h1><ul>';
    foreach ($errores as $error) echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
    echo '</ul><a class="boton principal" href="inventario.php">Regresar al inventario</a></section></main></body></html>';
    exit;
}

$stmt = $conn->prepare('INSERT INTO productos (nombre, marca, talla, color, precio, stock, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssdis', $nombre, $marca, $talla, $color, $precio, $stock, $ruta_imagen);
if (!$stmt->execute()) die('No fue posible guardar el producto. Verifica que la columna imagen exista en la base de datos.');

header('Location: inventario.php');
exit;