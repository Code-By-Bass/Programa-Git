<?php
// Datos del servidor MySQL/MariaDB de XAMPP.
$servidor = "127.0.0.1";
$usuario = "root";
$contrasena = "";
$base_datos = "fashion_light_system";
$puerto = 3306;

// Crear la conexión.
$conn = mysqli_connect(
    $servidor,
    $usuario,
    $contrasena,
    $base_datos,
    $puerto
);

// Detener el programa si la conexión falla.
if (!$conn) {
    die("No fue posible conectarse con la base de datos.");
}

// Permitir tildes, la letra ñ y otros caracteres.
mysqli_set_charset($conn, "utf8mb4");

// Mantener compatibles las bases creadas con una versión anterior.
$columna_imagen = mysqli_query($conn, "SHOW COLUMNS FROM productos LIKE 'imagen'");
if ($columna_imagen && mysqli_num_rows($columna_imagen) === 0) {
    mysqli_query($conn, "ALTER TABLE productos ADD COLUMN imagen VARCHAR(255) NULL AFTER stock");
}