<?php
session_start();

$venta = $_SESSION['venta_confirmada'] ?? null;

if (!$venta) {
    header('Location: venta.php');
    exit;
}

/*
 * Una vez mostrada la información, se elimina la sesión de
 * confirmación para evitar reutilizar accidentalmente los datos
 * al actualizar la página.
 */
unset($_SESSION['venta_confirmada']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta confirmada - Fashion Light System</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <header class="encabezado">
        <a href="index.html" class="marca"><img src="img/fashion_light_logo_sin_fondo.png" alt="Logo de Fashion Light System"><span>Fashion Light System</span></a>

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

        <section
            class="confirmacion">

            <div class="icono-confirmacion" aria-hidden="true">
                ✓
            </div>

            <span class="etiqueta">
                OPERACIÓN COMPLETADA
            </span>

            <h1>¡Venta registrada correctamente!</h1>

            <p class="texto-confirmacion">
                La operación fue almacenada correctamente en el sistema
                y las unidades correspondientes fueron descontadas del inventario.
            </p>

            <div class="resumen-venta">

                <div class="resumen-encabezado">
                    <h2>Resumen de la venta</h2>

                    <span class="numero-venta">
                        Venta #<?php echo (int) $venta['id_venta']; ?>
                    </span>
                </div>

                <dl class="datos-venta">

                    <div>
                        <dt>Producto</dt>
                        <dd>
                            <?php
                            echo htmlspecialchars(
                                $venta['producto'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Cantidad</dt>
                        <dd>
                            <?php echo (int) $venta['cantidad']; ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Precio unitario</dt>
                        <dd>
                            $<?php echo number_format(
                                (float) $venta['precio_unitario'],
                                0,
                                ',',
                                '.'
                            ); ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Subtotal</dt>
                        <dd>
                            $<?php echo number_format(
                                (float) $venta['subtotal'],
                                0,
                                ',',
                                '.'
                            ); ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Cliente</dt>
                        <dd>
                            <?php
                            echo htmlspecialchars(
                                $venta['nombre_cliente'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Documento</dt>
                        <dd>
                            <?php
                            echo htmlspecialchars(
                                $venta['documento_cliente'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Fecha</dt>
                        <dd>
                            <?php
                            $fecha = strtotime($venta['fecha']);

                            echo $fecha
                                ? date('d/m/Y H:i:s', $fecha)
                                : htmlspecialchars(
                                    $venta['fecha'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>
                        </dd>
                    </div>

                </dl>

                <div class="total-venta">
                    <span>Total de la venta</span>

                    <strong>
                        $<?php echo number_format(
                            (float) $venta['total'],
                            0,
                            ',',
                            '.'
                        ); ?>
                    </strong>
                </div>

            </div>

            <div class="grupo-botones">

                <a
                    href="venta.php"
                    class="boton principal">

                    Registrar otra venta

                </a>

                <a
                    href="listar_ventas.php"
                    class="boton secundario">

                    Ver historial de ventas

                </a>

                <a
                    href="index.html"
                    class="boton secundario">

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