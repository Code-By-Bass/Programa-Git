<?php
require_once 'conexion.php';

$ventas = [];

$sql = "
    SELECT
        id_venta,
        fecha,
        total
    FROM ventas
    ORDER BY fecha DESC, id_venta DESC
";

$resultado = $conn->query($sql);

if ($resultado) {
    while ($venta = $resultado->fetch_assoc()) {
        $ventas[] = $venta;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de ventas - Fashion Light System</title>
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
            <a href="listar_ventas.php" class="activo">
                Historial de ventas
            </a>
            <a href="tutorial.html">Tutorial</a>
            <a href="documentacion.html">Documentación</a>

        </nav>
    </header>

    <main class="historial">

        <section class="cabecera-pagina">
            <span class="etiqueta">CONSULTA DE OPERACIONES</span>

            <h1>Historial de ventas</h1>

            <p>
                Consulta las operaciones registradas en Fashion Light System,
                organizadas desde la venta más reciente hasta la más antigua.
            </p>
        </section>

        <section class="seccion historial-panel">

            <div class="cabecera-modulo historial-cabecera">
                <div>
                    <h2>Ventas registradas</h2>

                    <p>
                        Total de operaciones consultadas:
                        <strong><?php echo count($ventas); ?></strong>
                    </p>
                </div>

                <a
                    href="venta.php"
                    class="boton principal">
                    Registrar nueva venta
                </a>
            </div>

            <?php if (!empty($ventas)): ?>

                <div class="tabla-responsive">

                    <table class="tabla-datos tabla-ventas">

                        <caption>
                            Historial de ventas registradas
                        </caption>

                        <thead>
                            <tr>
                                <th scope="col">ID de venta</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($ventas as $venta): ?>

                                <tr>

                                    <td>
                                        #<?php
                                        echo (int) $venta['id_venta'];
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        $fecha = strtotime($venta['fecha']);

                                        echo $fecha
                                            ? date(
                                                'd/m/Y H:i:s',
                                                $fecha
                                            )
                                            : htmlspecialchars(
                                                $venta['fecha'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>
                                    </td>

                                    <td class="valor-total">
                                        $<?php
                                        echo number_format(
                                            (float) $venta['total'],
                                            0,
                                            ',',
                                            '.'
                                        );
                                        ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div
                    class="nota estado-vacio"
                    role="status">

                    <h2>No hay ventas registradas</h2>

                    <p>
                        Actualmente no existen operaciones almacenadas
                        en el historial de ventas.
                    </p>

                    <a
                        href="venta.php"
                        class="boton principal">
                        Registrar la primera venta
                    </a>

                </div>

            <?php endif; ?>

        </section>

        <section class="cards">

            <article class="card">
                <h2>Consulta de información</h2>

                <p>
                    El historial permite visualizar el identificador, la fecha
                    y el valor total de cada venta almacenada en la base de datos.
                </p>
            </article>

            <article class="card">
                <h2>Información actualizada</h2>

                <p>
                    Los registros mostrados corresponden a las operaciones
                    almacenadas en la tabla <strong>ventas</strong> de
                    Fashion Light System.
                </p>
            </article>

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