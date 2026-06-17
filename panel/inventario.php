<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

// GUARDAR NUEVO PRODUCTO
if(isset($_POST['guardar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $nombre         = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $categoria      = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $precio         = mysqli_real_escape_string($conexion, $_POST['precio']);
    $precio_mayoreo = mysqli_real_escape_string($conexion, $_POST['precio_mayoreo']);
    $stock          = intval($_POST['stock']);
    
    mysqli_query($conexion, "INSERT INTO productos (nombre, categoria, precio_base, precio_mayoreo, stock, activo) VALUES ('$nombre','$categoria','$precio','$precio_mayoreo','$stock', 1)");
    header("Location: inventario.php");
    exit();
}

// EDITAR PRODUCTO
if(isset($_POST['editar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $id             = intval($_POST['id']);
    $nombre         = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $categoria      = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $precio         = mysqli_real_escape_string($conexion, $_POST['precio']);
    $precio_mayoreo = mysqli_real_escape_string($conexion, $_POST['precio_mayoreo']);
    $stock          = intval($_POST['stock']);
    
    mysqli_query($conexion, "UPDATE productos SET nombre='$nombre', categoria='$categoria', precio_base='$precio', precio_mayoreo='$precio_mayoreo', stock='$stock' WHERE id='$id'");
    header("Location: inventario.php");
    exit();
}

// ELIMINAR (BORRADO LÓGICO - PAPELERA DE RECICLAJE)
if(isset($_GET['eliminar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $id = intval($_GET['eliminar']);
    $eliminado_por = mysqli_real_escape_string($conexion, $_SESSION['usuario']);
    $fecha_actual = date('Y-m-d H:i:s');
    
    mysqli_query($conexion, "UPDATE productos SET activo=0, deleted_by='$eliminado_por', deleted_at='$fecha_actual' WHERE id='$id'");
    header("Location: inventario.php");
    exit();
}

// AJUSTAR STOCK RÁPIDO
if(isset($_POST['ajustar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $id    = intval($_POST['id']);
    $stock = intval($_POST['stock']);
    mysqli_query($conexion, "UPDATE productos SET stock='$stock' WHERE id='$id'");
    header("Location: inventario.php");
    exit();
}

// BUSCADOR
$buscar = "";
if(isset($_GET['buscar'])){
    $buscar = $_GET['buscar'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Inventario</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">📦</span>
        <h2>Módulo de Inventario</h2>
    </div>
</div>

<div class="content">

    <div class="acciones-top" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <form method="GET" class="buscador" style="display:inline-flex; gap:5px;">
            <input type="text" name="buscar" placeholder="Buscar producto..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button type="submit" class="btn-panel btn-verde">Buscar</button>
            <a href="inventario.php" class="btn-limpiar">Limpiar</a>
        </form>
        
        <?php if($_SESSION['rol'] != "Empleado"){ ?>
            <button class="btn-nuevo" onclick="abrirModalNuevo()">+ Nuevo Producto</button>
        <?php } ?>
    </div>

    <div class="tabla-reciente" style="width: 100%; max-height: 550px; overflow-y: auto; border: 1px solid #e3e6f0; border-radius: 8px; margin-top:15px;">
        <div style="padding: 15px; background: #f8f9fc; border-bottom: 1px solid #e3e6f0;">
            <h2 style="margin:0; font-size: 17px; color:#4e73df;">
                <?php 
                    if($buscar != "") { echo "🔍 Resultados de Búsqueda para: '" . htmlspecialchars($buscar) . "'"; }
                    else { echo "📋 Control de Existencias (Ordenado por menor Stock)"; }
                ?>
            </h2>
        </div>

        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead style="position: sticky; top: 0; background-color: #4e73df; color: white; z-index: 10;">
                <tr>
                    <th style="padding: 12px; text-align: left;">Producto</th>
                    <th style="padding: 12px; text-align: left;">Categoría</th>
                    <th style="padding: 12px; text-align: left;">Precio Normal</th>
                    <th style="padding: 12px; text-align: left;">Precio Mayoreo</th>
                    <th style="padding: 12px; text-align: left;">Stock</th>
                    <th style="padding: 12px; text-align: left;">Estado</th>
                    <?php if($_SESSION['rol'] != "Empleado"){ ?>
                        <th style="padding: 12px; text-align: left; width: 180px;">Ajustar Stock</th>
                        <th style="padding: 12px; text-align: center; width: 160px;">Acciones</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php
            if($buscar != ""){
                $buscar_clean = mysqli_real_escape_string($conexion, $buscar);
                $sql = "SELECT * FROM productos WHERE activo=1 AND (nombre LIKE '%$buscar_clean%' OR categoria LIKE '%$buscar_clean%') ORDER BY stock ASC";
            } else {
                $sql = "SELECT * FROM productos WHERE activo=1 ORDER BY stock ASC";
            }
            
            $resultado = mysqli_query($conexion, $sql);
            if(mysqli_num_rows($resultado) == 0){
                echo "<tr><td colspan='8' style='text-align:center; padding:20px; color:#858796;'>No hay productos que coincidan con la búsqueda.</td></tr>";
            }
            
            while($mostrar = mysqli_fetch_array($resultado)){
                if($mostrar['stock'] <= 0){
                    $estado = "Agotado"; $badge = "badge-agotado";
                } elseif($mostrar['stock'] <= 5){
                    $estado = "Stock Bajo"; $badge = "badge-bajo";
                } else {
                    $estado = "Disponible"; $badge = "badge-disponible";
                }
            ?>
            <tr style="border-bottom: 1px solid #eaecf4;">
                <td style="padding: 12px;"><?php echo htmlspecialchars($mostrar['nombre']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($mostrar['categoria']); ?></td>
                <td style="padding: 12px;">$<?php echo number_format($mostrar['precio_base'], 2); ?></td>
                <td style="padding: 12px;">$<?php echo number_format($mostrar['precio_mayoreo'], 2); ?></td>
                <td style="padding: 12px;"><strong><?php echo $mostrar['stock']; ?></strong></td>
                <td style="padding: 12px;"><span class="badge <?php echo $badge; ?>"><?php echo $estado; ?></span></td>
                
                <?php if($_SESSION['rol'] != "Empleado"){ ?>
                <td style="padding: 12px;">
                    <form method="POST" style="display:flex; gap:6px; align-items:center; margin:0;">
                        <input type="hidden" name="id" value="<?php echo $mostrar['id']; ?>">
                        <input type="number" name="stock" class="input-stock" value="<?php echo $mostrar['stock']; ?>" min="0" style="width:70px; padding:4px;">
                        <button type="submit" name="ajustar" class="btn-panel btn-verde" style="font-size:11px; padding:4px 8px;">Actualizar</button>
                    </form>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                        <button class="btn-panel btn-naranja" style="font-size:11px; padding:5px 8px; background:#db7a00; border-color:#db7a00; color:white; border-radius:4px; font-weight:bold; cursor:pointer;"
                                onclick="abrirModalEditar(<?php echo $mostrar['id']; ?>,'<?php echo addslashes($mostrar['nombre']); ?>','<?php echo addslashes($mostrar['categoria']); ?>','<?php echo $mostrar['precio_base']; ?>','<?php echo $mostrar['precio_mayoreo']; ?>',<?php echo $mostrar['stock']; ?>)">
                            ✎ Editar
                        </button>
                        <a href="#" 
                           onclick="confirmarAccion('¿Mover a la papelera?', 'El producto <strong><?php echo htmlspecialchars($mostrar['nombre'], ENT_QUOTES); ?></strong> será enviado a la papelera.', '🗑️', 'confirm-rojo', 'Sí, eliminar', '?eliminar=<?php echo $mostrar['id']; ?>')" 
                           class="btn-panel btn-rojo" style="font-size:11px; padding:5px 8px; text-decoration:none; text-align:center;">
                           🗑️ Eliminar
                        </a>
                    </div>
                </td>
                <?php } ?>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php if($_SESSION['rol'] != "Empleado"){ ?>
<div class="modal" id="modalProducto">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <h2 id="modalTitulo">Nuevo Producto</h2>
        <form method="POST" id="formProducto">
            <input type="hidden" name="id" id="campoId">
            <input type="text" name="nombre" id="campoNombre" placeholder="Nombre del producto" required>
            <input type="text" name="categoria" id="campoCategoria" placeholder="Categoría">
            <input type="number" step="0.01" min="0" name="precio" id="campoPrecio" placeholder="Precio normal" required>
            <input type="number" step="0.01" min="0" name="precio_mayoreo" id="campoPrecioMayoreo" placeholder="Precio mayoreo" required>
            <input type="number" min="0" name="stock" id="campoStock" placeholder="Stock" required>
            
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="submit" name="guardar" id="btnSubmit" class="btn-guardar">Guardar Producto</button>
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-confirmar">
    <div class="confirm-box">
        <span class="confirm-icono" id="confirm-icono">⚠️</span>
        <p class="confirm-titulo" id="confirm-titulo">¿Estás seguro?</p>
        <p class="confirm-msg" id="confirm-msg"></p>
        <div class="confirm-acciones">
            <button class="btn-cancelar-confirm" onclick="cerrarConfirmar()">Cancelar</button>
            <button class="btn-ok-confirm" id="confirm-btn-ok" onclick="ejecutarConfirmar()">Confirmar</button>
        </div>
    </div>
</div>

<div id="toast-container"></div>

<script>
// ── MODAL DE CONFIRMACIÓN ─────────────────────────────────────────────
var _confirmarUrl = '';
function confirmarAccion(titulo, mensaje, icono, colorClass, textoBtn, url) {
    _confirmarUrl = url;
    document.getElementById('confirm-titulo').innerText = titulo;
    document.getElementById('confirm-msg').innerHTML = mensaje;
    document.getElementById('confirm-icono').innerText = icono;
    var btnOk = document.getElementById('confirm-btn-ok');
    btnOk.className = 'btn-ok-confirm ' + colorClass;
    btnOk.innerText = textoBtn;
    document.getElementById('modal-confirmar').classList.add('active');
}
function cerrarConfirmar() {
    document.getElementById('modal-confirmar').classList.remove('active');
    _confirmarUrl = '';
}
function ejecutarConfirmar() {
    if(_confirmarUrl) { window.location.href = _confirmarUrl; }
}

// ── MODAL PRODUCTO ─────────────────────────────────────────────────────
function abrirModalNuevo(){
    document.getElementById('modalTitulo').textContent = 'Nuevo Producto';
    document.getElementById('campoId').value = '';
    document.getElementById('campoNombre').value = '';
    document.getElementById('campoCategoria').value = '';
    document.getElementById('campoPrecio').value = '';
    document.getElementById('campoPrecioMayoreo').value = '';
    document.getElementById('campoStock').value = '';
    document.getElementById('btnSubmit').name = 'guardar';
    document.getElementById('btnSubmit').textContent = 'Guardar Producto';
    document.getElementById('modalProducto').classList.add('active');
}

function abrirModalEditar(id, nombre, categoria, precio, precioMayoreo, stock){
    document.getElementById('modalTitulo').textContent = 'Editar Producto';
    document.getElementById('campoId').value = id;
    document.getElementById('campoNombre').value = nombre;
    document.getElementById('campoCategoria').value = categoria;
    document.getElementById('campoPrecio').value = precio;
    document.getElementById('campoPrecioMayoreo').value = precioMayoreo;
    document.getElementById('campoStock').value = stock;
    document.getElementById('btnSubmit').name = 'editar';
    document.getElementById('btnSubmit').textContent = 'Guardar Cambios';
    document.getElementById('modalProducto').classList.add('active');
}

function cerrarModal(){
    document.getElementById('modalProducto').classList.remove('active');
}
</script>
<?php } ?>

<div class="footer-panel"><strong>SGI BARAK</strong> — Sistema de Gestión Integral | © 2026</div>

</body>
</html>