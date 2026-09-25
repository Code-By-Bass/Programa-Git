document.addEventListener('DOMContentLoaded', function () {
    inicializarMenu();
    inicializarFiltroTalla();
    inicializarCalculoVenta();
    inicializarValidacionVenta();
});


/* =========================================================
   MENÚ HAMBURGUESA
   ========================================================= */

function inicializarMenu() {
    const botonMenu = document.querySelector('.boton-menu');
    const menu = document.getElementById('menu-principal');

    if (!botonMenu || !menu) {
        return;
    }

    botonMenu.addEventListener('click', function () {
        const abierto = menu.classList.toggle('menu-abierto');

        botonMenu.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });

    const enlaces = menu.querySelectorAll('a');

    enlaces.forEach(function (enlace) {
        enlace.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                menu.classList.remove('menu-abierto');
                botonMenu.setAttribute('aria-expanded', 'false');
            }
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            menu.classList.remove('menu-abierto');
            botonMenu.setAttribute('aria-expanded', 'false');
        }
    });
}


/* =========================================================
   FILTRO DE INVENTARIO POR TALLA
   ========================================================= */

function inicializarFiltroTalla() {
    const filtro = document.getElementById('filtro-talla');
    const tabla = document.getElementById('tabla-inventario');

    if (!filtro || !tabla) {
        return;
    }

    filtro.addEventListener('change', function () {
        const tallaSeleccionada = filtro.value.toLowerCase();
        const filas = tabla.querySelectorAll('tbody tr');

        filas.forEach(function (fila) {
            const talla = fila.getAttribute('data-talla');

            if (
                tallaSeleccionada === '' ||
                tallaSeleccionada === 'todas' ||
                (talla && talla.toLowerCase() === tallaSeleccionada)
            ) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
}


/* =========================================================
   CÁLCULO AUTOMÁTICO DE VENTA
   ========================================================= */

function inicializarCalculoVenta() {
    const producto = document.getElementById('producto');
    const cantidad = document.getElementById('cantidad');
    const subtotal = document.getElementById('subtotal');
    const imagen = document.getElementById('imagen-producto-venta');

    if (!producto || !cantidad || !subtotal) {
        return;
    }

    function calcularSubtotal() {
        const opcion = producto.options[producto.selectedIndex];

        if (!opcion || !opcion.value) {
            subtotal.textContent = '$0.00';
            if (imagen) imagen.removeAttribute('src');
            return;
        }

        const precio = parseFloat(opcion.getAttribute('data-precio')) || 0;
        const cantidadIngresada = parseInt(cantidad.value, 10) || 0;
        const resultado = precio * cantidadIngresada;

        subtotal.textContent = formatearMoneda(resultado);

        if (imagen) {
            const rutaImagen = opcion.getAttribute('data-imagen');
            if (rutaImagen) {
                imagen.src = rutaImagen;
                imagen.alt = 'Imagen del producto seleccionado';
            } else {
                imagen.removeAttribute('src');
            }
        }
    }

    producto.addEventListener('change', calcularSubtotal);
    cantidad.addEventListener('input', calcularSubtotal);

    calcularSubtotal();
}


/* =========================================================
   VALIDACIÓN DEL FORMULARIO DE VENTA
   ========================================================= */

function inicializarValidacionVenta() {
    const formulario = document.getElementById('form-venta');

    if (!formulario) {
        return;
    }

    formulario.addEventListener('submit', function (evento) {
        const producto = document.getElementById('producto');
        const cantidad = document.getElementById('cantidad');
        const cliente = document.getElementById('nombre_cliente');
        const documento = document.getElementById('documento_cliente');
        const mensaje = document.getElementById('mensaje-validacion');

        let errores = [];

        limpiarErroresFormulario();

        if (!producto || !producto.value) {
            errores.push('Debe seleccionar un producto.');
            marcarCampoInvalido(producto);
        }

        const cantidadValor = cantidad
            ? parseInt(cantidad.value, 10)
            : 0;

        if (!cantidad || !Number.isInteger(cantidadValor) || cantidadValor < 1) {
            errores.push('La cantidad debe ser un número entero mayor que cero.');
            marcarCampoInvalido(cantidad);
        }

        if (!cliente || cliente.value.trim().length < 2) {
            errores.push('Debe ingresar el nombre del cliente.');
            marcarCampoInvalido(cliente);
        }

        if (!documento || documento.value.trim().length < 4) {
            errores.push('Debe ingresar un número de documento válido.');
            marcarCampoInvalido(documento);
        }

        if (errores.length > 0) {
            evento.preventDefault();

            if (mensaje) {
                mensaje.textContent = errores.join(' ');
            }

            return;
        }

        if (!confirm('¿Desea confirmar el registro de esta venta?')) {
            evento.preventDefault();
        }
    });
}


/* =========================================================
   FUNCIONES AUXILIARES
   ========================================================= */

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 2
    }).format(valor);
}


function marcarCampoInvalido(campo) {
    if (!campo) {
        return;
    }

    campo.setAttribute('aria-invalid', 'true');
}


function limpiarErroresFormulario() {
    const campos = document.querySelectorAll(
        '#form-venta input, #form-venta select'
    );

    campos.forEach(function (campo) {
        campo.removeAttribute('aria-invalid');
    });

    const mensaje = document.getElementById('mensaje-validacion');

    if (mensaje) {
        mensaje.textContent = '';
    }
}