<?php
require_once 'conexion.php';

$productos = [];

    $sql = "SELECT id_producto, nombre, marca, talla, color, precio, stock, imagen
        FROM productos
        WHERE stock > 0
        ORDER BY nombre ASC";

$resultado = $conn->query($sql);

if ($resultado) {
    while ($producto = $resultado->fetch_assoc()) {
        $productos[] = $producto;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar venta - Fashion Light System</title>
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

        <nav class="menu" id="menu-principal" aria-label="Navegación principal">
            <a href="index.html">Inicio</a>
            <a href="inventario.php">Inventario</a>
            <a href="venta.php" class="activo">Registrar venta</a>
            <a href="listar_ventas.php">Historial de ventas</a>
            <a href="tutorial.html">Tutorial</a>
            <a href="documentacion.html">Documentación</a>
        </nav>
    </header>

    <main class="contenedor pagina-formulario">

        <section class="cabecera-pagina">
            <span class="etiqueta">PUNTO DE VENTA</span>
            <h1>Registrar venta</h1>
            <p>
                Selecciona el producto, indica la cantidad y registra los datos
                básicos del cliente para calcular el valor de la operación.
            </p>
        </section>

        <section class="formulario">

            <form
                action="guardar_venta.php"
                method="POST"
                id="form-venta"
                >

                <div class="seccion-producto">
                    <div class="titulo-seccion-formulario">
                        <div>
                            <span class="numero-formulario">01</span>
                            <h2>Información del producto</h2>
                        </div>
                        <p>Selecciona una prenda y define la cantidad que deseas registrar.</p>
                    </div>

                    <div class="campo campo-producto">
                        <label for="producto">Producto *</label>

                        <select
                            id="producto"
                            name="producto_id"
                            required>

                            <option value="">
                                Seleccione un producto
                            </option>

                            <?php foreach ($productos as $producto): ?>
                                <option
                                    value="<?php echo (int) $producto['id_producto']; ?>"
                                    data-precio="<?php echo htmlspecialchars($producto['precio'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-stock="<?php echo (int) $producto['stock']; ?>"
                                    data-imagen="<?php echo htmlspecialchars($producto['imagen'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $producto['nombre'] .
                                        ' - ' .
                                        $producto['marca'] .
                                        ' - Talla ' .
                                        $producto['talla'] .
                                        ' - ' .
                                        $producto['color'] .
                                        ' | Stock: ' .
                                        $producto['stock'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>

                                </option>
                            <?php endforeach; ?>

                        </select>

                        <img id="imagen-producto-venta" class="imagen-producto-venta" alt="" aria-hidden="true">
                    </div>

                    <div class="producto-grid">

                        <div class="campo">
                            <label for="cantidad">Cantidad *</label>

                            <input
                                type="number"
                                id="cantidad"
                                name="cantidad"
                                min="1"
                                step="1"
                                value="1"
                                required>

                            <small>
                                Ingresa una cantidad disponible en el inventario.
                            </small>
                        </div>

                        <div class="campo campo-subtotal">
                            <label for="subtotal">Subtotal</label>

                            <output
                                id="subtotal"
                                class="campo-calculado"
                                aria-live="polite">
                                $0
                            </output>

                            <small>
                                El subtotal se calcula automáticamente.
                            </small>
                        </div>

                    </div>
                </div>

                <div>
                    <h2>Información del cliente</h2>

                    <div class="formulario-grid">

                        <div class="campo">
                            <label for="nombre_cliente">Nombre del cliente *</label>

                            <input
                                type="text"
                                id="nombre_cliente"
                                name="nombre_cliente"
                                maxlength="100"
                                autocomplete="name"
                                placeholder="Ej. María González"
                                required>
                        </div>

                        <div class="campo">
                            <label for="documento_cliente">Documento *</label>

                            <input
                                type="text"
                                id="documento_cliente"
                                name="documento_cliente"
                                maxlength="30"
                                autocomplete="off"
                                placeholder="Ej. 1234567890"
                                required>
                        </div>

                    </div>
                </div>

                <div
                    id="mensaje-validacion"
                    class="mensaje-formulario"
                    role="alert"
                    aria-live="polite">
                </div>

                <?php if (empty($productos)): ?>
                    <div class="nota" role="alert">
                        <strong>No hay productos disponibles.</strong>
                        <p>
                            No se encontraron productos con existencias para
                            realizar una venta.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="grupo-botones">

                    <a
                        href="index.html"
                        class="boton secundario">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="boton principal"
                        <?php echo empty($productos) ? 'disabled' : ''; ?>>
                        Registrar venta
                    </button>

                </div>

            </form>

        </section>

        <section class="informacion-venta">

            <article class="tarjeta-informacion">
                <h2>Antes de registrar</h2>

                <ul>
                    <li>Verifica que el producto seleccionado sea correcto.</li>
                    <li>Comprueba que la cantidad no supere el stock disponible.</li>
                    <li>Revisa los datos básicos del cliente.</li>
                    <li>Confirma el valor mostrado antes de enviar el formulario.</li>
                </ul>
            </article>

            <article class="tarjeta-informacion">
                <h2>Actualización del inventario</h2>

                <p>
                    Al guardar correctamente la venta, el backend consultará
                    nuevamente el precio del producto, registrará la operación
                    y descontará del inventario las unidades vendidas.
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