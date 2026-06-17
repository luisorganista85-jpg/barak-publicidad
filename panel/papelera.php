<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

if($_SESSION['rol'] != "Super Administrador" && $_SESSION['rol'] != "Administrador"){
    header("Location: dashboard.php");
    exit();
}

include("../config/conexion.php");

$seccion = isset($_GET['sec']) ? mysqli_real_escape_string($conexion, $_GET['sec']) : 'clientes';

// RESTAURAR
if(isset($_GET['restaurar']) && isset($_GET['tipo'])){
    $id   = intval($_GET['restaurar']);
    $tipo = mysqli_real_escape_string($conexion, $_GET['tipo']);

    if($tipo == 'clientes' || $tipo == 'proveedores' || $tipo == 'productos'){
        mysqli_query($conexion, "UPDATE $tipo SET activo=1 WHERE id=$id");
    } elseif($tipo == 'cotizaciones'){
        $sql_cot = mysqli_query($conexion, "SELECT * FROM cotizaciones WHERE id=$id AND activo=0");
        if(mysqli_num_rows($sql_cot) > 0){
            $detalles = mysqli_query($conexion, "SELECT * FROM detalle_cotizacion WHERE cotizacion_id=$id");
            while($dv = mysqli_fetch_array($detalles)){
                $prod_nombre = mysqli_real_escape_string($conexion, $dv['producto']);
                $p_res = mysqli_fetch_array(mysqli_query($conexion, "SELECT stock, nombre FROM productos WHERE nombre='$prod_nombre'"));
                if($dv['cantidad'] > $p_res['stock']){
                    echo "<script>alert('No se puede restaurar. Stock insuficiente para: ".addslashes($p_res['nombre'])."'); window.location='papelera.php?sec=cotizaciones';</script>";
                    exit();
                }
            }
            $detalles = mysqli_query($conexion, "SELECT * FROM detalle_cotizacion WHERE cotizacion_id=$id");
            while($dv = mysqli_fetch_array($detalles)){
                $prod_nombre = mysqli_real_escape_string($conexion, $dv['producto']);
                mysqli_query($conexion, "UPDATE productos SET stock = stock - ".$dv['cantidad']." WHERE nombre='$prod_nombre'");
            }
            mysqli_query($conexion, "UPDATE cotizaciones SET activo=1 WHERE id=$id");
        }
    }
    header("Location: papelera.php?sec=".$tipo);
    exit();
}

// ELIMINAR PERMANENTE
if(isset($_GET['eliminar_permanente']) && isset($_GET['tipo'])){
    $id   = intval($_GET['eliminar_permanente']);
    $tipo = mysqli_real_escape_string($conexion, $_GET['tipo']);

    if($tipo == 'clientes' || $tipo == 'proveedores' || $tipo == 'productos'){
        mysqli_query($conexion, "DELETE FROM $tipo WHERE id=$id");
    } elseif($tipo == 'cotizaciones'){
        mysqli_query($conexion, "DELETE FROM detalle_cotizacion WHERE cotizacion_id=$id");
        mysqli_query($conexion, "DELETE FROM cotizaciones WHERE id=$id");
    }
    header("Location: papelera.php?sec=".$tipo);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Papelera de Reciclaje</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">🗑️</span>
        <h2>Papelera de Reciclaje Global</h2>
    </div>
</div>

<div class="content">

    <!-- TABS -->
    <div class="tabs-papelera">
        <a href="papelera.php?sec=clientes"     class="tab-p-btn <?php echo ($seccion=='clientes')?'active':''; ?>">👥 Clientes</a>
        <a href="papelera.php?sec=proveedores"  class="tab-p-btn <?php echo ($seccion=='proveedores')?'active':''; ?>">🚚 Proveedores</a>
        <a href="papelera.php?sec=productos"    class="tab-p-btn <?php echo ($seccion=='productos')?'active':''; ?>">📦 Inventario</a>
        <a href="papelera.php?sec=cotizaciones" class="tab-p-btn <?php echo ($seccion=='cotizaciones')?'active':''; ?>">📄 Cotizaciones</a>
    </div>

    <div class="tabla-reciente" style="width:100%; max-height:520px; overflow-y:auto; border:1px solid #e3e6f0; border-radius:8px;">
        <div style="padding:15px; background:#f8f9fc; border-bottom:1px solid #e3e6f0;">
            <h2 style="margin:0; font-size:17px; color:#e74a3b;">
                🚨 Elementos eliminados temporalmente (<?php echo htmlspecialchars(ucfirst($seccion)); ?>)
            </h2>
        </div>

        <table style="width:100%; border-collapse:collapse; min-width:900px;">
            <thead style="position:sticky; top:0; background-color:#5a5c69; color:white; z-index:10;">
                <tr>
                <?php if($seccion == 'clientes' || $seccion == 'proveedores'){ ?>
                    <th style="padding:12px; text-align:left;">Nombre</th>
                    <th style="padding:12px; text-align:left;">Detalles</th>
                    <th style="padding:12px; text-align:left;">Eliminado por</th>
                    <th style="padding:12px; text-align:left;">Fecha</th>
                    <th style="padding:12px; text-align:center; width:190px;">Acciones</th>
                <?php } elseif($seccion == 'productos'){ ?>
                    <th style="padding:12px; text-align:left;">Producto</th>
                    <th style="padding:12px; text-align:left;">Categoría</th>
                    <th style="padding:12px; text-align:left;">Stock</th>
                    <th style="padding:12px; text-align:left;">Eliminado por</th>
                    <th style="padding:12px; text-align:left;">Fecha</th>
                    <th style="padding:12px; text-align:center; width:190px;">Acciones</th>
                <?php } elseif($seccion == 'cotizaciones'){ ?>
                    <th style="padding:12px; text-align:left;">ID</th>
                    <th style="padding:12px; text-align:left;">Cliente</th>
                    <th style="padding:12px; text-align:left;">Total</th>
                    <th style="padding:12px; text-align:left;">Eliminado por</th>
                    <th style="padding:12px; text-align:left;">Fecha</th>
                    <th style="padding:12px; text-align:center; width:310px;">Acciones</th>
                <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php
            if($seccion == 'clientes' || $seccion == 'proveedores'){
                $res = mysqli_query($conexion, "SELECT * FROM $seccion WHERE activo=0 ORDER BY deleted_at DESC");
                if(mysqli_num_rows($res) == 0){
                    echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#858796;'>No hay registros en esta papelera.</td></tr>";
                }
                while($m = mysqli_fetch_array($res)){ ?>
                <tr style="border-bottom:1px solid #eaecf4;">
                    <td style="padding:12px;"><strong><?php echo htmlspecialchars($m['nombre']); ?></strong></td>
                    <td style="padding:12px; color:#5a5c69;"><?php echo htmlspecialchars($m['empresa'] ?? $m['telefono'] ?? 'N/A'); ?></td>
                    <td style="padding:12px;"><span class="badge-del-by"><?php echo htmlspecialchars($m['deleted_by'] ?? 'System'); ?></span></td>
                    <td style="padding:12px; font-size:12px; color:#858796;"><?php echo !empty($m['deleted_at']) ? date('d/m/Y H:i', strtotime($m['deleted_at'])) : 'N/A'; ?></td>
                    <td style="padding:12px; text-align:center;">
                        <div style="display:flex; gap:6px; justify-content:center;">
                            <button class="btn-p-action btn-p-restore"
                                onclick="abrirConfirmacion('papelera.php?restaurar=<?php echo $m['id']; ?>&tipo=<?php echo $seccion; ?>', '¿Restaurar este registro?', '<?php echo addslashes($m['nombre']); ?> volverá a estar activo en el sistema.', '♻️', 'Restaurar')">
                                ♻️ Restaurar
                            </button>
                            <button class="btn-p-action btn-p-delete"
                                onclick="abrirConfirmacion('papelera.php?eliminar_permanente=<?php echo $m['id']; ?>&tipo=<?php echo $seccion; ?>', '¿Eliminar permanentemente?', 'Esta acción es irreversible y borrará el registro del sistema para siempre.', '⛔', 'Sí, eliminar')">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                <?php }
            } elseif($seccion == 'productos'){
                $res = mysqli_query($conexion, "SELECT * FROM productos WHERE activo=0 ORDER BY deleted_at DESC");
                if(mysqli_num_rows($res) == 0){
                    echo "<tr><td colspan='6' style='text-align:center; padding:20px; color:#858796;'>No hay productos en esta papelera.</td></tr>";
                }
                while($m = mysqli_fetch_array($res)){ ?>
                <tr style="border-bottom:1px solid #eaecf4;">
                    <td style="padding:12px;"><strong><?php echo htmlspecialchars($m['nombre']); ?></strong></td>
                    <td style="padding:12px; color:#5a5c69;"><?php echo htmlspecialchars($m['codigo'] ?? $m['categoria'] ?? 'S/C'); ?></td>
                    <td style="padding:12px; text-align:center;"><strong><?php echo $m['stock']; ?></strong></td>
                    <td style="padding:12px;"><span class="badge-del-by"><?php echo htmlspecialchars($m['deleted_by'] ?? 'System'); ?></span></td>
                    <td style="padding:12px; font-size:12px; color:#858796;"><?php echo !empty($m['deleted_at']) ? date('d/m/Y H:i', strtotime($m['deleted_at'])) : 'N/A'; ?></td>
                    <td style="padding:12px; text-align:center;">
                        <div style="display:flex; gap:6px; justify-content:center;">
                            <button class="btn-p-action btn-p-restore"
                                onclick="abrirConfirmacion('papelera.php?restaurar=<?php echo $m['id']; ?>&tipo=productos', '¿Restaurar producto?', '<?php echo addslashes($m['nombre']); ?> volverá a estar disponible en inventario.', '♻️', 'Restaurar')">
                                ♻️ Restaurar
                            </button>
                            <button class="btn-p-action btn-p-delete"
                                onclick="abrirConfirmacion('papelera.php?eliminar_permanente=<?php echo $m['id']; ?>&tipo=productos', '¿Eliminar permanentemente?', 'Esta acción es irreversible y eliminará el producto del inventario para siempre.', '⛔', 'Sí, eliminar')">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                <?php }
            } elseif($seccion == 'cotizaciones'){
                $res = mysqli_query($conexion, "SELECT * FROM cotizaciones WHERE activo=0 ORDER BY deleted_at DESC");
                if(mysqli_num_rows($res) == 0){
                    echo "<tr><td colspan='6' style='text-align:center; padding:20px; color:#858796;'>No hay cotizaciones en esta papelera.</td></tr>";
                }
                while($m = mysqli_fetch_array($res)){
                    $cot_id = $m['id'];
                    $js_productos = "";
                    $query_detalles = mysqli_query($conexion, "SELECT * FROM detalle_cotizacion WHERE cotizacion_id=$cot_id");
                    while($d = mysqli_fetch_array($query_detalles)){
                        $js_productos .= "• ".addslashes($d['producto'])." (Cantidad: ".intval($d['cantidad']).")\\n";
                    }
                    if(empty($js_productos)){ $js_productos = "No se encontraron artículos adjuntos."; }
                ?>
                <tr style="border-bottom:1px solid #eaecf4;">
                    <td style="padding:12px; color:#4e73df;"><strong>#<?php echo $cot_id; ?></strong></td>
                    <td style="padding:12px;"><strong><?php echo htmlspecialchars($m['cliente']); ?></strong></td>
                    <td style="padding:12px; color:#1cc88a;"><strong>$<?php echo number_format($m['total'], 2); ?></strong></td>
                    <td style="padding:12px;"><span class="badge-del-by"><?php echo htmlspecialchars($m['deleted_by'] ?? 'System'); ?></span></td>
                    <td style="padding:12px; font-size:12px; color:#858796;"><?php echo !empty($m['deleted_at']) ? date('d/m/Y H:i', strtotime($m['deleted_at'])) : 'N/A'; ?></td>
                    <td style="padding:12px; text-align:center;">
                        <div style="display:flex; gap:6px; justify-content:center;">
                            <button class="btn-p-action btn-p-info" onclick="abrirInsumos(<?php echo $cot_id; ?>, '<?php echo $js_productos; ?>')">👁️ Ver Insumos</button>
                            <button class="btn-p-action btn-p-restore"
                                onclick="abrirConfirmacion('papelera.php?restaurar=<?php echo $m['id']; ?>&tipo=cotizaciones', '¿Restaurar cotización #<?php echo $cot_id; ?>?', 'Se volverá a descontar el stock correspondiente.', '♻️', 'Restaurar')">
                                ♻️ Restaurar
                            </button>
                            <button class="btn-p-action btn-p-delete"
                                onclick="abrirConfirmacion('papelera.php?eliminar_permanente=<?php echo $m['id']; ?>&tipo=cotizaciones', '¿Eliminar permanentemente?', 'Se borrarán la cotización y todos sus detalles de forma irreversible.', '⛔', 'Sí, eliminar')">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                <?php }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL INSUMOS COTIZACION -->
<div class="modal" id="modalInsumos">
    <div class="modal-contenido" style="max-width:450px;">
        <span class="cerrar" onclick="document.getElementById('modalInsumos').style.display='none'">&times;</span>
        <h2>📦 Insumos de Cotización <span id="insumos-id"></span></h2>
        <div id="insumos-lista" style="margin-top:16px; display:flex; flex-direction:column; gap:8px;"></div>
        <div style="margin-top:20px; text-align:right;">
            <button class="btn-cancelar" onclick="document.getElementById('modalInsumos').style.display='none'">Cerrar</button>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMACION -->
<div class="modal" id="modalConfirmacion">
    <div class="modal-contenido" style="max-width:400px; text-align:center;">
        <div id="conf-icono" style="font-size:52px; margin-bottom:12px;">⚠️</div>
        <h2 id="conf-titulo" style="margin:0 0 8px;">¿Confirmar acción?</h2>
        <p id="conf-desc" style="color:#64748b; font-size:14px; margin:0 0 24px;"></p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button class="btn-cancelar" onclick="cerrarConfirmacion()">Cancelar</button>
            <button id="conf-btn" class="btn-guardar" style="background:#dc3545;" onclick="ejecutarConfirmacion()">Confirmar</button>
        </div>
    </div>
</div>

<div class="footer-panel"><strong>SGI BARAK</strong> — Sistema de Gestión Integral | © 2026</div>

<style>
.tabs-papelera { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
.tab-p-btn { padding:10px 16px; background:#eaecf4; color:#4e73df; font-weight:bold; text-decoration:none; border-radius:4px; font-size:14px; transition:all 0.2s; border:1px solid #d1d3e2; }
.tab-p-btn:hover { background:#e3e6f0; }
.tab-p-btn.active { background:#4e73df; color:white; border-color:#4e73df; }
.btn-p-action { font-size:11px; padding:5px 8px; text-decoration:none; border-radius:4px; font-weight:bold; border:none; cursor:pointer; display:inline-block; }
.btn-p-restore { background:#4e73df; color:white; }
.btn-p-info    { background:#db7a00; color:white; }
.btn-p-delete  { background:#e74a3b; color:white; }
.badge-del-by  { background:#f1f3f9; padding:3px 6px; border-radius:4px; font-size:11px; color:#4e73df; font-weight:bold; }
</style>

<script>
var _urlConf = '';

function abrirInsumos(cotId, productosStr){
    document.getElementById('insumos-id').innerText = '#' + cotId;
    var lista = document.getElementById('insumos-lista');
    lista.innerHTML = '';
    var lineas = productosStr.split('\\n').filter(function(l){ return l.trim() != ''; });
    lineas.forEach(function(linea){
        var div = document.createElement('div');
        div.style.cssText = 'background:#f8f9fc; border-left:3px solid #4e73df; padding:8px 12px; border-radius:4px; font-size:13px; color:#343a40;';
        div.innerText = linea;
        lista.appendChild(div);
    });
    document.getElementById('modalInsumos').style.display = 'flex';
}

function abrirConfirmacion(url, titulo, descripcion, icono, btnTexto){
    _urlConf = url;
    document.getElementById('conf-icono').innerText  = icono  || '⚠️';
    document.getElementById('conf-titulo').innerText = titulo;
    document.getElementById('conf-desc').innerText   = descripcion;
    document.getElementById('conf-btn').innerText    = btnTexto || 'Confirmar';
    document.getElementById('modalConfirmacion').style.display = 'flex';
}

function ejecutarConfirmacion(){ window.location.href = _urlConf; }
function cerrarConfirmacion(){ document.getElementById('modalConfirmacion').style.display = 'none'; }

window.onclick = function(event){
    let m = document.getElementById('modalConfirmacion');
    let mi = document.getElementById('modalInsumos');
    if(event.target == m) m.style.display = 'none';
    if(event.target == mi) mi.style.display = 'none';
}
</script>
</body>
</html>