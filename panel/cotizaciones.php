<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

// CERRAR COTIZACION
if(isset($_GET['cerrar'])){
    $id = $_GET['cerrar'];
    mysqli_query($conexion, "UPDATE cotizaciones SET estado='Cerrada' WHERE id='$id'");
    header("Location: cotizaciones.php");
    exit();
}

// GUARDAR COTIZACION
if(isset($_POST['guardar'])){

    $cliente     = $_POST['cliente'];
    $empresa     = $_POST['empresa'];
    $canal_venta = $_POST['canal_venta'];
    $estado      = "Pendiente";
    $usuario     = $_SESSION['usuario'];

    mysqli_query($conexion, "INSERT INTO cotizaciones
        (cliente, empresa, canal_venta, estado, usuario)
        VALUES ('$cliente','$empresa','$canal_venta','$estado','$usuario')");

    $cotizacion_id = mysqli_insert_id($conexion);
    $total_general = 0;

    foreach($_POST['producto'] as $index => $producto_id){

        $cantidad   = $_POST['cantidad'][$index];
        $tipo_venta = $_POST['tipo_venta'][$index];

        if($tipo_venta == "Mayoreo" && $cantidad < 5){
            echo "<script>
            alert('El precio mayoreo aplica mínimo desde 5 piezas');
            window.location='cotizaciones.php';
            </script>";
            exit();
        }

        $sql_producto = mysqli_query($conexion,
            "SELECT * FROM productos WHERE id='$producto_id'");
        $producto = mysqli_fetch_array($sql_producto);

        $nombre_producto = $producto['nombre'];
        $stock_actual    = $producto['stock'];

        if($cantidad > $stock_actual){
            echo "<script>
            alert('Stock insuficiente para: $nombre_producto');
            window.location='cotizaciones.php';
            </script>";
            exit();
        }

        if($tipo_venta == "Mayoreo"){
            $precio = $producto['precio_mayoreo'];
        }else{
            $precio = $producto['precio_base'];
        }

        $subtotal       = floatval($precio) * intval($cantidad);
        $total_general += $subtotal;

        mysqli_query($conexion, "INSERT INTO detalle_cotizacion
            (cotizacion_id, producto, cantidad, tipo_venta, precio, subtotal)
            VALUES ('$cotizacion_id','$nombre_producto','$cantidad',
            '$tipo_venta','$precio','$subtotal')");

        mysqli_query($conexion, "UPDATE productos
            SET stock = stock - $cantidad WHERE id='$producto_id'");
    }

    mysqli_query($conexion, "UPDATE cotizaciones
        SET total='$total_general' WHERE id='$cotizacion_id'");

    header("Location: cotizaciones.php");
    exit();
}

// BUSCADOR
$buscar = "";
if(isset($_GET['buscar'])){
    $buscar = $_GET['buscar'];
}

// ESTADISTICAS
$hoy = date('Y-m-d');

$cot_hoy = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT COUNT(*) as total FROM cotizaciones WHERE DATE(fecha)='$hoy'")
)['total'] ?? 0;

$ventas_cerradas = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT SUM(total) as suma FROM cotizaciones WHERE estado='Cerrada'")
)['suma'] ?? 0;

$clientes_empresa = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT COUNT(*) as total FROM clientes WHERE empresa != ''")
)['total'] ?? 0;

// PRODUCTOS PARA JS
$productos_js = [];
$res_p = mysqli_query($conexion, "SELECT id, nombre, precio_base, precio_mayoreo FROM productos ORDER BY nombre");

while($pjs = mysqli_fetch_array($res_p)){
    $productos_js[] = $pjs;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BAAK - Cotizaciones</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="content">

    <h1>Módulo de Cotizaciones</h1>

    <!-- CARDS -->
    <div class="cards">
        <div class="card">
            <h3>Cotizaciones del Día</h3>
            <p><?php echo $cot_hoy; ?></p>
        </div>

        <div class="card">
            <h3>Ventas Cerradas</h3>
            <p>$<?php echo number_format($ventas_cerradas, 2); ?></p>
        </div>

        <div class="card">
            <h3>Clientes Empresas</h3>
            <p><?php echo $clientes_empresa; ?></p>
        </div>
    </div>

    <!-- ACCIONES -->
    <div class="acciones-top">

        <form method="GET" class="buscador">

            <input type="text"
            name="buscar"
            placeholder="Buscar cliente..."
            value="<?php echo $buscar; ?>">

            <button type="submit">
                Buscar
            </button>

            <a href="cotizaciones.php" class="btn-limpiar">
                Limpiar
            </a>

        </form>

        <button class="btn-nuevo" onclick="abrirModal()">
            + Nueva Cotización
        </button>

    </div>

    <!-- TABLA -->
    <div class="tabla-reciente">

        <h2>Lista de Cotizaciones</h2>

        <table>

            <thead>
                <tr>

                    <th>Cliente</th>
                    <th>Empresa</th>
                    <th>Total</th>
                    <th>Canal</th>
                    <th>Estado</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                    <th>Detalle</th>
                    <th>Acción</th>

                </tr>
            </thead>

            <tbody>

            <?php

            if($buscar != ""){
                $sql_buscar = "SELECT * FROM cotizaciones
                WHERE cliente LIKE '%$buscar%'
                ORDER BY id DESC";
            }else{
                $sql_buscar = "SELECT * FROM cotizaciones
                ORDER BY id DESC";
            }

            $resultado = mysqli_query($conexion, $sql_buscar);

            while($mostrar = mysqli_fetch_array($resultado)){

            ?>

            <tr>

                <td>
                    <?php echo $mostrar['cliente']; ?>
                </td>

                <td>
                    <?php echo $mostrar['empresa']; ?>
                </td>

                <td>
                    $<?php echo number_format($mostrar['total'], 2); ?>
                </td>

                <td>
                    <?php echo $mostrar['canal_venta']; ?>
                </td>

                <td>
                    <?php echo $mostrar['estado']; ?>
                </td>

                <!-- USUARIO -->
                <td>
                    <?php echo $mostrar['usuario']; ?>
                </td>

                <!-- FECHA -->
                <td>
                    <?php echo date('d/m/Y H:i', strtotime($mostrar['fecha'])); ?>
                </td>

                <!-- DETALLE -->
                <td>

                <?php

                $detalle = mysqli_query($conexion,
                    "SELECT * FROM detalle_cotizacion
                    WHERE cotizacion_id='".$mostrar['id']."'");

                while($d = mysqli_fetch_array($detalle)){

                    echo "&bull; ".$d['producto'].
                    " (".$d['cantidad']." x $".$d['precio'].") = $".
                    number_format($d['subtotal'],2)."<br>";

                }

                ?>

                </td>

                <!-- ACCIONES -->
                <td>

                <?php if($mostrar['estado'] == 'Pendiente'){ ?>

                    <a href="?cerrar=<?php echo $mostrar['id']; ?>"
                    onclick="return confirm('¿Cerrar cotización?')">

                        Cerrar

                    </a>

                <?php } else { ?>

                    <span class="estado-ok">
                        ✔ Cerrada
                    </span>

                <?php } ?>

                <br>

                <a href="cotizacion_pdf.php?id=<?php echo $mostrar['id']; ?>"
                target="_blank"
                style="color:#16a34a; font-weight:bold; font-size:13px;">

                    📄 PDF

                </a>

                </td>

            </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<!-- MODAL -->
<div class="modal" id="modalCotizacion">

<div class="modal-contenido modal-grande">

    <span class="cerrar" onclick="cerrarModal()">
        &times;
    </span>

    <h2>Nueva Cotización</h2>

    <form method="POST">

        <select name="cliente" required>

            <option value="">
                Seleccionar Cliente
            </option>

            <?php

            $clientes = mysqli_query($conexion,
            "SELECT * FROM clientes ORDER BY nombre");

            while($c = mysqli_fetch_array($clientes)){

            ?>

            <option value="<?php echo $c['nombre']; ?>">
                <?php echo $c['nombre']; ?>
            </option>

            <?php } ?>

        </select>

        <input type="text"
        name="empresa"
        placeholder="Empresa">

        <select name="canal_venta" required>

            <option value="">
                Canal de Venta
            </option>

            <option value="WhatsApp">WhatsApp</option>
            <option value="Facebook">Facebook</option>
            <option value="Empresa">Empresa</option>
            <option value="Recomendación">Recomendación</option>

        </select>

        <!-- PRODUCTOS -->
        <div id="contenedor-productos">

            <div class="producto-item">

                <select name="producto[]"
                required
                onchange="calcularTotal()">

                    <option value="">
                        Selecciona Producto
                    </option>

                    <?php foreach($productos_js as $p){ ?>

                    <option value="<?php echo $p['id']; ?>"
                        data-normal="<?php echo $p['precio_base']; ?>"
                        data-mayoreo="<?php echo $p['precio_mayoreo']; ?>">

                        <?php echo $p['nombre']; ?> |
                        Normal: $<?php echo number_format($p['precio_base'],2); ?> |
                        Mayoreo: $<?php echo number_format($p['precio_mayoreo'],2); ?>

                    </option>

                    <?php } ?>

                </select>

                <input type="number"
                name="cantidad[]"
                placeholder="Cantidad"
                min="1"
                required
                oninput="calcularTotal()">

                <select name="tipo_venta[]"
                required
                onchange="calcularTotal()">

                    <option value="">
                        Tipo Venta
                    </option>

                    <option value="Por Pieza">
                        Por Pieza
                    </option>

                    <option value="Mayoreo">
                        Mayoreo (mín. 5 pzas)
                    </option>

                </select>

            </div>

        </div>

        <!-- TOTAL -->
        <div class="total-preview-box">

            <strong>
                Total estimado:
                $<span id="total_preview">0.00</span>
            </strong>

        </div>

        <button type="button"
        class="btn-agregar"
        onclick="agregarProducto()">

            + Agregar Producto

        </button>

        <button type="submit" name="guardar">

            Guardar Cotización

        </button>

    </form>

</div>
</div>

<script>

var productosData = <?php echo json_encode($productos_js); ?>;

function getProductoData(id){

    for(var i=0; i<productosData.length; i++){

        if(productosData[i]['id'] == id)
            return productosData[i];

    }

    return null;

}

function calcularTotal(){

    var items = document.querySelectorAll('.producto-item');
    var totalGeneral = 0;

    items.forEach(function(item){

        var sel = item.querySelector('[name="producto[]"]');

        var cantidad =
        parseFloat(
        item.querySelector('[name="cantidad[]"]').value
        ) || 0;

        var tipo =
        item.querySelector('[name="tipo_venta[]"]').value;

        var id = sel.value;

        if(!id || !cantidad || !tipo) return;

        var datos = getProductoData(id);

        if(!datos) return;

        var precio =
        (tipo == 'Mayoreo')
        ? parseFloat(datos['precio_mayoreo'])
        : parseFloat(datos['precio_base']);

        totalGeneral += precio * cantidad;

    });

    document.getElementById('total_preview').innerText =
    totalGeneral.toFixed(2);

}

function abrirModal(){

    document.getElementById(
    'modalCotizacion'
    ).style.display = 'flex';

}

function cerrarModal(){

    document.getElementById(
    'modalCotizacion'
    ).style.display = 'none';

}

window.onclick = function(event){

    let modal =
    document.getElementById('modalCotizacion');

    if(event.target == modal)
        modal.style.display = 'none';

}

function agregarProducto(){

    let contenedor =
    document.getElementById(
    'contenedor-productos'
    );

    let opciones = '';

    productosData.forEach(function(p){

        opciones += `<option value="${p.id}"
            data-normal="${p.precio_base}"
            data-mayoreo="${p.precio_mayoreo}">

            ${p.nombre}
            | Normal: $${parseFloat(p.precio_base).toFixed(2)}
            | Mayoreo: $${parseFloat(p.precio_mayoreo).toFixed(2)}

        </option>`;

    });

    let nuevo = `

    <div class="producto-item">

        <select name="producto[]"
        required
        onchange="calcularTotal()">

            <option value="">
                Selecciona Producto
            </option>

            ${opciones}

        </select>

        <input type="number"
        name="cantidad[]"
        placeholder="Cantidad"
        min="1"
        required
        oninput="calcularTotal()">

        <select name="tipo_venta[]"
        required
        onchange="calcularTotal()">

            <option value="">
                Tipo Venta
            </option>

            <option value="Por Pieza">
                Por Pieza
            </option>

            <option value="Mayoreo">
                Mayoreo (mín. 5 pzas)
            </option>

        </select>

        <button type="button"
        onclick="this.parentElement.remove(); calcularTotal()"
        class="btn-quitar">

            ✕

        </button>

    </div>`;

    contenedor.insertAdjacentHTML('beforeend', nuevo);

}

</script>

</body>
</html>