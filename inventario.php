<?php
require_once 'conexion.php';

$imagenes = [
    'camiseta' => 'img/Camiseta basica.jpg',
    'jean' => 'img/Jean clásico.jpg',
    'blusa' => 'img/Blusa casual.jpg',
    'chaqueta' => 'img/Chaqueta deportiva.jpg',
    'vestido' => 'img/Vestido floral.jpg',
    'falda' => 'img/Falda plisada.jpg',
    'pantalón' => 'img/Pantalón casual.jpg',
    'camisa' => 'img/Camisa manga larga.jpg',
    'estampada' => 'img/Camisa manga larga.jpg',
    'buzo' => 'img/Buzo con capota.jpg',
    'sudadera' => 'img/Buzo con capota.jpg'
];

$productos = [];
$producto_editar = null;

$id_editar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
if ($id_editar) {
    $stmt_editar = $conn->prepare(
        'SELECT id_producto, nombre, marca, talla, color, precio, stock, imagen
         FROM productos WHERE id_producto = ?'
    );
    $stmt_editar->bind_param('i', $id_editar);
    $stmt_editar->execute();
    $producto_editar = $stmt_editar->get_result()->fetch_assoc();
    $stmt_editar->close();
}

$resultado = $conn->query(
    'SELECT id_producto, nombre, marca, talla, color, precio, stock, imagen
     FROM productos ORDER BY nombre ASC'
);

if ($resultado) {
    while ($producto = $resultado->fetch_assoc()) {
        $nombre = strtolower($producto['nombre']);
        $producto['imagen_mostrar'] = $producto['imagen'];

        if (!$producto['imagen_mostrar']) {
            foreach ($imagenes as $clave => $ruta) {
                if (strpos($nombre, $clave) !== false) {
                    $producto['imagen_mostrar'] = $ruta;
                    break;
                }
            }
        }

        $productos[] = $producto;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Fashion Light System</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header class="encabezado">
        <a href="index.html" class="marca">
            <img src="img/fashion_light_logo_sin_fondo.png" alt="Logo de Fashion Light System">
            <span>Fashion Light System</span>
        </a>
        <button class="boton-menu" id="boton-menu" type="button" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="menu-principal">Menú</button>
        <nav class="menu" id="menu-principal" aria-label="Navegación principal">
            <a href="index.html">Inicio</a>
            <a href="inventario.php" class="activo">Inventario</a>
            <a href="venta.php">Registrar venta</a>
            <a href="listar_ventas.php">Historial de ventas</a>
            <a href="tutorial.html">Tutorial</a>
            <a href="documentacion.html">Documentación</a>
        </nav>
    </header>

    <main class="contenedor pagina-inventario">
        <section class="encabezado-pagina">
            <span class="etiqueta-seccion">GESTIÓN DE PRODUCTOS</span>
            <h1>Inventario de prendas</h1>
            <p>Consulta, registra, edita y elimina prendas con su imagen.</p>
        </section>

        <div class="inventario-layout">
        <section class="formulario formulario-producto" aria-labelledby="titulo-nuevo-producto">
            <h2 id="titulo-nuevo-producto"><?php echo $producto_editar ? 'Editar producto' : 'Registrar producto'; ?></h2>
            <form action="<?php echo $producto_editar ? 'actualizar_producto.php' : 'guardar_producto.php'; ?>" method="POST" enctype="multipart/form-data">
                <?php if ($producto_editar): ?>
                    <input type="hidden" name="id_producto" value="<?php echo (int) $producto_editar['id_producto']; ?>">
                <?php endif; ?>
                <div class="formulario-grid">
                    <div class="campo"><label for="nombre">Nombre *</label><input id="nombre" name="nombre" maxlength="100" value="<?php echo htmlspecialchars($producto_editar['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="campo"><label for="marca">Marca *</label><input id="marca" name="marca" maxlength="100" value="<?php echo htmlspecialchars($producto_editar['marca'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="campo"><label for="talla">Talla *</label><input id="talla" name="talla" maxlength="20" value="<?php echo htmlspecialchars($producto_editar['talla'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="campo"><label for="color">Color *</label><input id="color" name="color" maxlength="50" value="<?php echo htmlspecialchars($producto_editar['color'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="campo"><label for="precio">Precio *</label><input id="precio" name="precio" type="number" min="0" step="0.01" value="<?php echo htmlspecialchars($producto_editar['precio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="campo"><label for="stock">Stock *</label><input id="stock" name="stock" type="number" min="0" step="1" value="<?php echo htmlspecialchars($producto_editar['stock'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="campo completo"><label for="imagen">Imagen del producto</label><input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp"><small>Formatos permitidos: JPG, PNG o WEBP. Máximo 5 MB.</small></div>
                </div>
                <button class="boton principal" type="submit"><?php echo $producto_editar ? 'Actualizar producto' : 'Guardar producto'; ?></button>
                <?php if ($producto_editar): ?><a class="boton secundario" href="inventario.php">Cancelar edición</a><?php endif; ?>
            </form>
        </section>

        <section class="panel-inventario" aria-labelledby="titulo-inventario">
            <div class="barra-inventario">
                <div><h2 id="titulo-inventario">Productos registrados</h2><p>Imágenes y existencias actuales de la tienda.</p></div>
                <div class="filtro-contenedor"><label for="filtro-talla">Filtrar por talla</label><select id="filtro-talla"><option value="todas">Todas las tallas</option><option>XS</option><option>S</option><option>M</option><option>L</option><option>XL</option></select></div>
            </div>
            <div class="tabla-contenedor">
                <table id="tabla-inventario" class="tabla-datos">
                    <caption>Inventario de productos de Fashion Light System</caption>
                    <thead><tr><th>Imagen</th><th>Producto</th><th>Marca</th><th>Talla</th><th>Color</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr data-talla="<?php echo htmlspecialchars($producto['talla'], ENT_QUOTES, 'UTF-8'); ?>">
                            <td><?php if ($producto['imagen_mostrar']): ?><img class="imagen-producto" src="<?php echo htmlspecialchars($producto['imagen_mostrar'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>"><?php else: ?><span class="sin-imagen">Sin imagen</span><?php endif; ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($producto['marca'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($producto['talla'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($producto['color'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>$<?php echo number_format((float) $producto['precio'], 0, ',', '.'); ?></td>
                            <td class="<?php echo (int) $producto['stock'] < 5 ? 'stock-bajo' : 'stock-disponible'; ?>"><?php echo (int) $producto['stock']; ?></td>
                            <td class="acciones-tabla">
                                <a class="boton-tabla boton-editar" href="inventario.php?editar=<?php echo (int) $producto['id_producto']; ?>">Editar</a>
                                <form action="eliminar_producto.php" method="POST" onsubmit="return confirm('¿Desea eliminar este producto?');">
                                    <input type="hidden" name="id_producto" value="<?php echo (int) $producto['id_producto']; ?>">
                                    <button class="boton-tabla boton-eliminar" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        </div>

        <section class="nota-inventario"><div><span class="etiqueta-seccion">OPERACIÓN</span><h2>Control del inventario</h2><p>Los productos nuevos quedan disponibles para registrar ventas después de guardarlos.</p></div><a href="venta.php" class="boton exito">Registrar una venta</a></section>
    </main>
    <footer class="pie-pagina"><div class="contenedor"><p>Fashion Light System — Proyecto académico SENA 2026</p></div></footer>
    <script src="js/funciones.js"></script>
</body>
</html>