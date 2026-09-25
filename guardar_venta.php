<?php

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: venta.php');
    exit;
}

/*
 * Se reciben y limpian los datos enviados desde el formulario.
 */
$producto_id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

$nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
$documento_cliente = trim($_POST['documento_cliente'] ?? '');

$errores = [];

/*
 * Validación del producto.
 */
if ($producto_id === false || $producto_id === null || $producto_id <= 0) {
    $errores[] = 'Debe seleccionar un producto válido.';
}

/*
 * Validación de la cantidad.
 */
if ($cantidad === false || $cantidad === null || $cantidad <= 0) {
    $errores[] = 'La cantidad debe ser un número entero mayor que cero.';
}

/*
 * Validación del nombre del cliente.
 */
if ($nombre_cliente === '') {
    $errores[] = 'El nombre del cliente es obligatorio.';
} elseif (mb_strlen($nombre_cliente) > 100) {
    $errores[] = 'El nombre del cliente no puede superar los 100 caracteres.';
}

/*
 * Validación del documento.
 */
if ($documento_cliente === '') {
    $errores[] = 'El documento del cliente es obligatorio.';
} elseif (mb_strlen($documento_cliente) > 30) {
    $errores[] = 'El documento del cliente no puede superar los 30 caracteres.';
}

/*
 * Si existen errores de validación, se muestran y se permite
 * regresar al formulario.
 */
if (!empty($errores)) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error al registrar venta - Fashion Light System</title>
        <link rel="stylesheet" href="css/estilos.css">
    </head>
    <body>

        <header class="encabezado">
            <a href="index.html" class="marca">
                Fashion Light System
            </a>

                <button
                    class="boton-menu"
                    id="boton-menu"
                    type="button"
                    aria-label="Abrir menú de navegación"
                    aria-expanded="false"
                    aria-controls="menu-principal">
                    Menú
                </button>

                <nav
                    class="menu"
                    id="menu-principal"
                    aria-label="Navegación principal">
                    <a href="index.html">Inicio</a>
                    <a href="inventario.php">Inventario</a>
                    <a href="venta.php">Registrar venta</a>
                    <a href="listar_ventas.php">Historial de ventas</a>
                    <a href="tutorial.html">Tutorial</a>
                    <a href="documentacion.html">Documentación</a>
                </nav>
        </header>

        <main>

            <section class="mensaje mensaje-error" role="alert">
                <h1>No fue posible registrar la venta</h1>

                <p>
                    Se encontraron los siguientes problemas:
                </p>

                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li>
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a href="venta.php" class="boton principal">
                    Regresar al formulario
                </a>
            </section>

        </main>

        <footer class="pie-pagina">
            <div class="contenedor">
                <p>Fashion Light System — Proyecto académico SENA 2026</p>
            </div>
        </footer>

        <script src="js/funciones.js"></script>
    </body>
    </html>
    <?php
    exit;
}

/*
 * Se inicia una transacción para garantizar que el registro de la
 * venta, el detalle y la actualización del stock se realicen
 * correctamente como una sola operación.
 */
$conn->begin_transaction();

try {

    /*
     * Se consulta nuevamente el producto directamente desde la base
     * de datos. De esta manera, el precio utilizado para la venta
     * no depende del valor enviado por el navegador.
     */
    $sql_producto = "
        SELECT id_producto, nombre, precio, stock
        FROM productos
        WHERE id_producto = ?
        FOR UPDATE
    ";

    $stmt_producto = $conn->prepare($sql_producto);

    if (!$stmt_producto) {
        throw new Exception('No fue posible preparar la consulta del producto.');
    }

    $stmt_producto->bind_param('i', $producto_id);
    $stmt_producto->execute();

    $resultado_producto = $stmt_producto->get_result();
    $producto = $resultado_producto->fetch_assoc();

    $stmt_producto->close();

    if (!$producto) {
        throw new Exception('El producto seleccionado no existe.');
    }

    /*
     * Se comprueba nuevamente el stock directamente en la base
     * de datos antes de realizar la operación.
     */
    $stock_actual = (int) $producto['stock'];

    if ($cantidad > $stock_actual) {
        throw new Exception(
            'La cantidad solicitada supera el stock disponible. ' .
            'Stock actual: ' . $stock_actual . '.'
        );
    }

    /*
     * El precio real almacenado en MySQL es utilizado para calcular
     * el subtotal y el total de la venta.
     */
    $precio_unitario = (float) $producto['precio'];
    $subtotal = $precio_unitario * $cantidad;
    $total = $subtotal;

    /*
     * La tabla ventas exige un usuario. Se utiliza el primer usuario
     * disponible hasta que el sistema incorpore autenticación.
     */

    $sql_usuario = "
        SELECT id_usuario
        FROM usuarios
        ORDER BY id_usuario ASC
        LIMIT 1
    ";

    $resultado_usuario = $conn->query($sql_usuario);

    if (!$resultado_usuario || !$fila_usuario = $resultado_usuario->fetch_assoc()) {
        throw new Exception('No existe un usuario disponible para registrar la venta.');
    }

    $usuario_id = (int) $fila_usuario['id_usuario'];

    /*
     * Se registra la venta principal.
     */
    $sql_venta = "
        INSERT INTO ventas (usuario_id, fecha, total)
        VALUES (?, NOW(), ?)
    ";

    $stmt_venta = $conn->prepare($sql_venta);

    if (!$stmt_venta) {
        throw new Exception('No fue posible preparar el registro de la venta.');
    }

    $stmt_venta->bind_param('id', $usuario_id, $total);

    if (!$stmt_venta->execute()) {
        throw new Exception('No fue posible guardar la venta.');
    }

    $venta_id = $conn->insert_id;

    $stmt_venta->close();

    /*
     * Se registra el detalle de la venta.
     */
    $sql_detalle = "
        INSERT INTO detalle_venta
            (venta_id, producto_id, cantidad, precio_unitario, subtotal)
        VALUES
            (?, ?, ?, ?, ?)
    ";

    $stmt_detalle = $conn->prepare($sql_detalle);

    if (!$stmt_detalle) {
        throw new Exception(
            'No fue posible preparar el detalle de la venta.'
        );
    }

    $stmt_detalle->bind_param(
        'iiidd',
        $venta_id,
        $producto_id,
        $cantidad,
        $precio_unitario,
        $subtotal
    );

    if (!$stmt_detalle->execute()) {
        throw new Exception('No fue posible guardar el detalle de la venta.');
    }

    $stmt_detalle->close();

    /*
     * Se descuenta la cantidad vendida del inventario.
     */
    $sql_stock = "
        UPDATE productos
        SET stock = stock - ?
        WHERE id_producto = ?
          AND stock >= ?
    ";

    $stmt_stock = $conn->prepare($sql_stock);

    if (!$stmt_stock) {
        throw new Exception(
            'No fue posible preparar la actualización del inventario.'
        );
    }

    $stmt_stock->bind_param(
        'iii',
        $cantidad,
        $producto_id,
        $cantidad
    );

    if (!$stmt_stock->execute()) {
        throw new Exception(
            'No fue posible actualizar el inventario.'
        );
    }

    if ($stmt_stock->affected_rows !== 1) {
        throw new Exception(
            'El stock cambió durante la operación. La venta no fue registrada.'
        );
    }

    $stmt_stock->close();

    /*
     * Si todas las operaciones fueron exitosas, se confirma
     * la transacción.
     */
    $conn->commit();

    /*
     * Se almacenan temporalmente los datos mínimos necesarios para
     * presentar el resumen en confirmacion.php.
     */
    session_start();

    $_SESSION['venta_confirmada'] = [
        'id_venta' => $venta_id,
        'producto' => $producto['nombre'],
        'cantidad' => $cantidad,
        'precio_unitario' => $precio_unitario,
        'subtotal' => $subtotal,
        'total' => $total,
        'nombre_cliente' => $nombre_cliente,
        'documento_cliente' => $documento_cliente,
        'fecha' => date('Y-m-d H:i:s')
    ];

    header('Location: confirmacion.php');
    exit;

} catch (Throwable $e) {

    /*
     * Ante cualquier error se revierten todas las operaciones
     * realizadas dentro de la transacción.
     */
    $conn->rollback();
    error_log('Error al registrar venta: ' . $e->getMessage());

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error de procesamiento - Fashion Light System</title>
        <link rel="stylesheet" href="css/estilos.css">
    </head>
    <body>

        <header class="encabezado">
            <a href="index.html" class="marca">
                Fashion Light System
            </a>

                <button
                    class="boton-menu"
                    id="boton-menu"
                    type="button"
                    aria-label="Abrir menú de navegación"
                    aria-expanded="false"
                    aria-controls="menu-principal">
                    Menú
                </button>

                <nav
                    class="menu"
                    id="menu-principal"
                    aria-label="Navegación principal">
                    <a href="index.html">Inicio</a>
                    <a href="inventario.php">Inventario</a>
                    <a href="venta.php">Registrar venta</a>
                    <a href="listar_ventas.php">Historial de ventas</a>
                    <a href="tutorial.html">Tutorial</a>
                    <a href="documentacion.html">Documentación</a>
                </nav>
        </header>

        <main>

            <section class="mensaje mensaje-error" role="alert">
                <h1>Error al procesar la venta</h1>

                <p>
                    La operación no pudo completarse y los cambios fueron
                    revertidos para proteger la información del sistema.
                </p>

                <p>
                    Verifica los datos e inténtalo nuevamente. Si el problema
                    continúa, contacta al administrador del sistema.
                </p>

                <div class="grupo-botones">
                    <a href="venta.php" class="boton principal">
                        Intentar nuevamente
                    </a>

                    <a href="index.html" class="boton secundario">
                        Volver al inicio
                    </a>
                </div>
            </section>

        </main>

        <footer class="pie-pagina">
            <div class="contenedor">
                <p>Fashion Light System — Proyecto académico SENA 2026</p>
            </div>
        </footer>

        <script src="js/funciones.js"></script>
    </body>
    </html>
    <?php
}
?>