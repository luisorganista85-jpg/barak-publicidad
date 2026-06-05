<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

// CERRAR COTIZACION
if(isset($_GET['cerrar'])){
    $id = mysqli_real_escape_string($conexion, $_GET['cerrar']);
    mysqli_query($conexion, "UPDATE cotizaciones SET estado='Cerrada' WHERE id='$id'");
    header("Location: cotizaciones.php");
    exit();
}

// GUARDAR NUEVA COTIZACION
if(isset($_POST['guardar'])){
    $cliente     = mysqli_real_escape_string($conexion, $_POST['cliente']);
    $empresa     = mysqli_real_escape_string($conexion, $_POST['empresa']);
    $canal_venta = mysqli_real_escape_string($conexion, $_POST['canal_venta']);
    $estado      = "Pendiente";
    $usuario     = $_SESSION['usuario'];

    mysqli_query($conexion, "INSERT INTO cotizaciones
        (cliente, empresa, canal_venta, estado, usuario)
        VALUES ('$cliente','$empresa','$canal_venta','$estado','$usuario')");

    $cotizacion_id = mysqli_insert_id($conexion);
    $total_general = 0;

    foreach($_POST['producto'] as $index => $producto_id){
        $cantidad   = intval($_POST['cantidad'][$index]);
        $tipo_venta = mysqli_real_escape_string($conexion, $_POST['tipo_venta'][$index]);

        if($cantidad >= 10){ $tipo_venta = "Mayoreo"; }

        $sql_producto = mysqli_query($conexion, "SELECT * FROM productos WHERE id='$producto_id'");
        $producto = mysqli_fetch_array($sql_producto);

        if($cantidad > $producto['stock']){
            echo "<script>alert('Stock insuficiente para: ".$producto['nombre']."'); window.location='cotizaciones.php';</script>";
            exit();
        }

        $precio = ($tipo_venta == "Mayoreo") ? $producto['precio_mayoreo'] : $producto['precio_base'];
        $subtotal = floatval($precio) * $cantidad;
        $total_general += $subtotal;

        $nombre_p = mysqli_real_escape_string($conexion, $producto['nombre']);
        mysqli_query($conexion, "INSERT INTO detalle_cotizacion
            (cotizacion_id, producto, cantidad, tipo_venta, precio, subtotal)
            VALUES ('$cotizacion_id','$nombre_p','$cantidad','$tipo_venta','$precio','$subtotal')");

        mysqli_query($conexion, "UPDATE productos SET stock = stock - $cantidad WHERE id='$producto_id'");
    }

    mysqli_query($conexion, "UPDATE cotizaciones SET total='$total_general' WHERE id='$cotizacion_id'");
    header("Location: cotizaciones.php");
    exit();
}

// PROCESAR ACTUALIZACIÓN / EDICIÓN DE COTIZACIÓN
if(isset($_POST['actualizar'])){
    $cotizacion_id = mysqli_real_escape_string($conexion, $_POST['cotizacion_id']);
    $cliente       = mysqli_real_escape_string($conexion, $_POST['cliente']);
    $empresa       = mysqli_real_escape_string($conexion, $_POST['empresa']);
    $canal_venta   = mysqli_real_escape_string($conexion, $_POST['canal_venta']);
    
    $detalles_viejos = mysqli_query($conexion, "SELECT * FROM detalle_cotizacion WHERE cotizacion_id='$cotizacion_id'");
    while($dv = mysqli_fetch_array($detalles_viejos)){
        $prod_nombre = mysqli_real_escape_string($conexion, $dv['producto']);
        mysqli_query($conexion, "UPDATE productos SET stock = stock + ".$dv['cantidad']." WHERE nombre='$prod_nombre'");
    }
    
    mysqli_query($conexion, "DELETE FROM detalle_cotizacion WHERE cotizacion_id='$cotizacion_id'");
    
    $total_general = 0;

    foreach($_POST['producto'] as $index => $producto_id){
        $cantidad   = intval($_POST['cantidad'][$index]);
        $tipo_venta = mysqli_real_escape_string($conexion, $_POST['tipo_venta'][$index]);

        if($cantidad >= 10){ $tipo_venta = "Mayoreo"; }

        $sql_producto = mysqli_query($conexion, "SELECT * FROM productos WHERE id='$producto_id'");
        $producto = mysqli_fetch_array($sql_producto);

        if($cantidad > $producto['stock']){
            echo "<script>alert('Stock insuficiente tras la edición para: ".$producto['nombre']."'); window.location='cotizaciones.php';</script>";
            exit();
        }

        $precio = ($tipo_venta == "Mayoreo") ? $producto['precio_mayoreo'] : $producto['precio_base'];
        $subtotal = floatval($precio) * $cantidad;
        $total_general += $subtotal;

        $nombre_p = mysqli_real_escape_string($conexion, $producto['nombre']);
        mysqli_query($conexion, "INSERT INTO detalle_cotizacion
            (cotizacion_id, producto, cantidad, tipo_venta, precio, subtotal)
            VALUES ('$cotizacion_id','$nombre_p','$cantidad','$tipo_venta','$precio','$subtotal')");

        mysqli_query($conexion, "UPDATE productos SET stock = stock - $cantidad WHERE id='$producto_id'");
    }

    mysqli_query($conexion, "UPDATE cotizaciones SET cliente='$cliente', empresa='$empresa', canal_venta='$canal_venta', total='$total_general' WHERE id='$cotizacion_id'");
    header("Location: cotizaciones.php");
    exit();
}

// BUSCADOR
$buscar = "";
if(isset($_GET['buscar'])){ $buscar = mysqli_real_escape_string($conexion, $_GET['buscar']); }

// ESTADISTICAS
date_default_timezone_set('America/Mexico_City');
$hoy = date('Y-m-d');

$cot_hoy = mysqli_fetch_array(mysqli_query($conexion, "SELECT COUNT(*) as total FROM cotizaciones WHERE DATE(fecha) = '$hoy'"))['total'] ?? 0;
$ventas_cerradas = mysqli_fetch_array(mysqli_query($conexion, "SELECT SUM(total) as suma FROM cotizaciones WHERE estado='Cerrada'"))['suma'] ?? 0;
$clientes_empresa = mysqli_fetch_array(mysqli_query($conexion, "SELECT COUNT(*) as total FROM clientes WHERE empresa != ''"))['total'] ?? 0;

// PRODUCTOS PARA JS
$productos_js = [];
$res_p = mysqli_query($conexion, "SELECT id, nombre, precio_base, precio_mayoreo FROM productos ORDER BY nombre");
while($pjs = mysqli_fetch_array($res_p)){ $productos_js[] = $pjs; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Cotizaciones</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">📄</span>
        <h2>Módulo de Cotizaciones</h2>
    </div>
</div>

<div class="content">
    <div class="cards">
        <div class="card"><h3>Cotizaciones del Día</h3><p><?php echo $cot_hoy; ?></p></div>
        <div class="card"><h3>Ventas Cerradas</h3><p>$<?php echo number_format($ventas_cerradas, 2); ?></p></div>
        <div class="card"><h3>Clientes Empresas</h3><p><?php echo $clientes_empresa; ?></p></div>
    </div>

    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button type="submit">Buscar</button>
            <a href="cotizaciones.php" class="btn-limpiar">Limpiar</a>
        </form>
        <button class="btn-nuevo" onclick="abrirModal()">+ Nueva Cotización</button>
    </div>

    <div class="tabla-reciente" style="width: 100%; max-height: 500px; overflow-y: auto; border: 1px solid #e3e6f0; border-radius: 8px;">
        <div style="padding: 15px; background: #f8f9fc; border-bottom: 1px solid #e3e6f0; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin:0; font-size: 18px; color:#4e73df;">
                <?php echo ($buscar != "") ? "🔍 Resultados de Búsqueda" : "📋 Filtro Eficiente: 5 Pendientes e Historial Cerrado"; ?>
            </h2>
        </div>

        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead style="position: sticky; top: 0; background-color: #4e73df; color: white; z-index: 10;">
                <tr>
                    <th>Cliente</th><th>Empresa</th><th>Total</th><th>Canal</th><th>Estado</th><th>Usuario</th><th>Fecha</th><th>Detalle</th><th style="text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($buscar != ""){
                $sql_buscar = "SELECT * FROM cotizaciones WHERE cliente LIKE '%$buscar%' ORDER BY id DESC";
            }else{
                $sql_buscar = "(SELECT * FROM cotizaciones WHERE estado='Pendiente' ORDER BY id DESC LIMIT 5)
                               UNION ALL
                               (SELECT * FROM cotizaciones WHERE estado='Cerrada' ORDER BY id DESC LIMIT 10)
                               ORDER BY id DESC";
            }

            $resultado = mysqli_query($conexion, $sql_buscar);
            while($mostrar = mysqli_fetch_array($resultado)){
            ?>
            <tr style="border-bottom: 1px solid #eaecf4;">
                <td style="padding: 12px;"><?php echo $mostrar['cliente']; ?></td>
                <td style="padding: 12px;"><?php echo $mostrar['empresa']; ?></td>
                <td style="padding: 12px;"><strong>$<?php echo number_format($mostrar['total'], 2); ?></strong></td>
                <td style="padding: 12px;"><?php echo $mostrar['canal_venta']; ?></td>
                <td style="padding: 12px;">
                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: <?php echo ($mostrar['estado']=='Cerrada')?'#e2f6ed':'#fff3cd'; ?>; color: <?php echo ($mostrar['estado']=='Cerrada')?'#155724':'#856404'; ?>;">
                        <?php echo $mostrar['estado']; ?>
                    </span>
                </td>
                <td style="padding: 12px;"><?php echo $mostrar['usuario']; ?></td>
                <td style="padding: 12px; font-size: 12px;"><?php echo date('d/m/Y H:i', strtotime($mostrar['fecha'])); ?></td>
                <td style="padding: 12px; font-size: 12px;">
                <?php
                $detalle = mysqli_query($conexion, "SELECT * FROM detalle_cotizacion WHERE cotizacion_id='".$mostrar['id']."'");
                while($d = mysqli_fetch_array($detalle)){
                    echo "&bull; ".$d['producto']." (".$d['cantidad']." x $".$d['precio'].")<br>";
                }
                ?>
                </td>
                <td style="padding: 12px; text-align: center;">
                <?php if($mostrar['estado'] == 'Pendiente'){ ?>
                    <a href="?cerrar=<?php echo $mostrar['id']; ?>" onclick="return confirm('¿Cerrar cotización?')" style="color: #16a34a; font-weight: bold; text-decoration: none;">Cerrar Venta</a> | 
                    <a href="#" onclick='abrirEditar(<?php echo json_encode($mostrar); ?>)' style="color: #db7a00; font-weight: bold; text-decoration: none;">✏ Editar</a>
                <?php } else { ?>
                    <span style="color: #16a34a; font-weight: bold;">✔ Cerrada</span>
                <?php } ?>
                <br>
                <a href="cotizacion_pdf.php?id=<?php echo $mostrar['id']; ?>" target="_blank" style="color:#e74a3b; font-weight:bold; font-size:12px; text-decoration:none; display:inline-block; margin-top:5px;">📄 Ver PDF</a>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL NUEVO -->
<div class="modal" id="modalCotizacion">
<div class="modal-contenido modal-grande">
    <span class="cerrar" onclick="cerrarModal()">&times;</span>
    <h2>Nueva Cotización</h2>
    <form method="POST">
        <select name="cliente" required>
            <option value="">Seleccionar Cliente</option>
            <?php
            $clientes = mysqli_query($conexion, "SELECT * FROM clientes ORDER BY nombre");
            while($c = mysqli_fetch_array($clientes)){ echo "<option value='".$c['nombre']."'>".$c['nombre']."</option>"; }
            ?>
        </select>
        <input type="text" name="empresa" placeholder="Empresa">
        <select name="canal_venta" required>
            <option value="">Canal de Venta</option>
            <option value="WhatsApp">WhatsApp</option>
            <option value="Facebook">Facebook</option>
               <option value="Empresa">Empresa</option>
            <option value="Recomendación">Recomendación</option>
        </select>
        <div id="contenedor-productos">
            <div class="producto-item">
                <select name="producto[]" required onchange="calcularTotal('contenedor-productos', 'total_preview')">
                    <option value="">Selecciona Producto</option>
                    <?php foreach($productos_js as $p){ ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre']; ?></option>
                    <?php } ?>
                </select>
                <input type="number" name="cantidad[]" class="txt-cantidad" placeholder="Cantidad" min="1" required oninput="calcularTotal('contenedor-productos', 'total_preview')">
                <select name="tipo_venta[]" class="slc-tipo" required onchange="calcularTotal('contenedor-productos', 'total_preview')">
                    <option value="Por Pieza">Por Pieza</option>
                    <option value="Mayoreo">Mayoreo</option>
                </select>
            </div>
        </div>
        <div class="total-preview-box"><strong>Total estimado: $<span id="total_preview">0.00</span></strong></div>
        <button type="button" class="btn-agregar" onclick="agregarProducto('contenedor-productos', 'total_preview')">+ Agregar Producto</button>
        <button type="submit" name="guardar">Guardar Cotización</button>
    </form>
</div>
</div>

<!-- MODAL EDITAR -->
<div class="modal" id="modalEditar">
<div class="modal-contenido modal-grande">
    <span class="cerrar" onclick="cerrarEditar()">&times;</span>
    <h2>Modificar Cotización #<span id="edit_num_tit"></span></h2>
    <form method="POST">
        <input type="hidden" name="cotizacion_id" id="edit_id">
        <input type="text" name="cliente" id="edit_cliente" required placeholder="Cliente">
        <input type="text" name="empresa" id="edit_empresa" placeholder="Empresa">
        <select name="canal_venta" id="edit_canal" required>
            <option value="WhatsApp">WhatsApp</option>
            <option value="Facebook">Facebook</option>
        </select>
        
        <div id="contenedor-editar-productos"></div>
        
        <div class="total-preview-box"><strong>Total Corregido: $<span id="total_edit_preview">0.00</span></strong></div>
        <button type="button" class="btn-agregar" onclick="agregarProducto('contenedor-editar-productos', 'total_edit_preview')">+ Añadir Otro Producto</button>
        <button type="submit" name="actualizar" style="background:#db7a00;">Actualizar y Regenerar PDF</button>
    </form>
</div>
</div>

<div class="footer-panel"><strong>SGI BARAK</strong> — Sistema de Gestión Integral | © 2026</div>

<script>
var productosData = <?php echo json_encode($productos_js); ?>;

function getProductoData(id){
    for(var i=0; i<productosData.length; i++){ if(productosData[i]['id'] == id) return productosData[i]; }
    return null;
}

function getProductoIdByNombre(nombre){
    for(var i=0; i<productosData.length; i++){ if(productosData[i]['nombre'] == nombre) return productosData[i]['id']; }
    return "";
}

function calcularTotal(contenedorId, labelId){
    var items = document.querySelectorAll('#' + contenedorId + ' .producto-item');
    var totalGeneral = 0;
    items.forEach(function(item){
        var sel = item.querySelector('select[name="producto[]"]');
        var inputCantidad = item.querySelector('.txt-cantidad');
        var selectTipo = item.querySelector('.slc-tipo');
        
        if(!sel || !inputCantidad || !selectTipo) return;
        var cantidad = parseFloat(inputCantidad.value) || 0;
        var id = sel.value;
        
        if(cantidad >= 10) { selectTipo.value = "Mayoreo"; } 
        else if(cantidad > 0 && cantidad < 10) { selectTipo.value = "Por Pieza"; }
        
        var tipo = selectTipo.value;
        var datos = getProductoData(id);
        if(!datos) return;
        
        var precio = (tipo == 'Mayoreo') ? parseFloat(datos['precio_mayoreo']) : parseFloat(datos['precio_base']);
        totalGeneral += precio * cantidad;
    });
    document.getElementById(labelId).innerText = totalGeneral.toFixed(2);
}

function abrirModal() { document.getElementById('modalCotizacion').style.display = 'flex'; }
function cerrarModal() { document.getElementById('modalCotizacion').style.display = 'none'; }
function cerrarEditar() { document.getElementById('modalEditar').style.display = 'none'; }

function agregarProducto(contenedorId, labelId){
    let contenedor = document.getElementById(contenedorId);
    let opciones = '';
    productosData.forEach(function(p){ opciones += `<option value="${p.id}">${p.nombre}</option>`; });
    let nuevo = `<div class="producto-item">
        <select name="producto[]" required onchange="calcularTotal('${contenedorId}', '${labelId}')"><option value="">Selecciona</option>${opciones}</select>
        <input type="number" name="cantidad[]" class="txt-cantidad" placeholder="Cantidad" min="1" required oninput="calcularTotal('${contenedorId}', '${labelId}')">
        <select name="tipo_venta[]" class="slc-tipo" required onchange="calcularTotal('${contenedorId}', '${labelId}')"><option value="Por Pieza">Por Pieza</option><option value="Mayoreo">Mayoreo</option></select>
        <button type="button" onclick="this.parentElement.remove(); calcularTotal('${contenedorId}', '${labelId}')" class="btn-quitar">✕</button>
    </div>`;
    contenedor.insertAdjacentHTML('beforeend', nuevo);
}

function abrirEditar(cotizacion){
    document.getElementById('edit_id').value = cotizacion.id;
    document.getElementById('edit_num_tit').innerText = cotizacion.id;
    document.getElementById('edit_cliente').value = cotizacion.cliente;
    document.getElementById('edit_empresa').value = cotizacion.empresa;
    document.getElementById('edit_canal').value = cotizacion.canal_venta;
    
    let contenedor = document.getElementById('contenedor-editar-productos');
    contenedor.innerHTML = '<p style="color:#858796; padding:10px;">Cargando historial anterior...</p>'; 
    
    document.getElementById('modalEditar').style.display = 'flex';

    fetch('get_detalle_cotizacion.php?id=' + cotizacion.id)
        .then(response => response.json())
        .then(detalles => {
            contenedor.innerHTML = '';
            
            if(detalles.length === 0) {
                agregarProducto('contenedor-editar-productos', 'total_edit_preview');
                return;
            }

            detalles.forEach(function(d) {
                let opciones = '';
                let prodIdActual = getProductoIdByNombre(d.producto);

                productosData.forEach(function(p){
                    let selected = (p.id == prodIdActual) ? 'selected' : '';
                    opciones += `<option value="${p.id}" ${selected}>${p.nombre}</option>`;
                });

                let selPieza = (d.tipo_venta === 'Por Pieza') ? 'selected' : '';
                let selMayoreo = (d.tipo_venta === 'Mayoreo') ? 'selected' : '';

                let nuevo = `<div class="producto-item">
                    <select name="producto[]" required onchange="calcularTotal('contenedor-editar-productos', 'total_edit_preview')">
                        ${opciones}
                    </select>
                    <input type="number" name="cantidad[]" class="txt-cantidad" value="${d.cantidad}" placeholder="Cantidad" min="1" required oninput="calcularTotal('contenedor-editar-productos', 'total_edit_preview')">
                    <select name="tipo_venta[]" class="slc-tipo" required onchange="calcularTotal('contenedor-editar-productos', 'total_edit_preview')">
                        <option value="Por Pieza" ${selPieza}>Por Pieza</option>
                        <option value="Mayoreo" ${selMayoreo}>Mayoreo</option>
                    </select>
                    <button type="button" onclick="this.parentElement.remove(); calcularTotal('contenedor-editar-productos', 'total_edit_preview')" class="btn-quitar">✕</button>
                </div>`;
                
                contenedor.insertAdjacentHTML('beforeend', nuevo);
            });

            calcularTotal('contenedor-editar-productos', 'total_edit_preview');
        })
        .catch(error => {
            console.error("Error:", error);
            contenedor.innerHTML = '<p style="color:red; padding:10px;">Error al mapear productos anteriores.</p>';
        });
}
</script>
</body>
</html>