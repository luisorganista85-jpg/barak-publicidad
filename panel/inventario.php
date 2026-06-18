<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

function subirImagenProducto($archivo) {
    if (isset($archivo) && $archivo['error'] === 0) {
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $permitidas)) {
            $nombreImagen = uniqid('prod_') . '.' . $ext;
            move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../uploads/productos/' . $nombreImagen);
            return $nombreImagen;
        }
    }
    return null;
}

function guardarVariantes($conexion, $productoId) {
    mysqli_query($conexion, "DELETE FROM producto_variantes WHERE producto_id='$productoId'");
    if (isset($_POST['medida']) && is_array($_POST['medida'])) {
        foreach ($_POST['medida'] as $i => $medida) {
            $medida = mysqli_real_escape_string($conexion, trim($medida));
            $precioVar = isset($_POST['precio_variante'][$i]) ? mysqli_real_escape_string($conexion, trim($_POST['precio_variante'][$i])) : '';
            if ($medida !== '' && $precioVar !== '') {
                mysqli_query($conexion, "INSERT INTO producto_variantes (producto_id, medida, precio) VALUES ('$productoId', '$medida', '$precioVar')");
            }
        }
    }
}

// GUARDAR NUEVO PRODUCTO
if(isset($_POST['guardar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $nombre         = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $categoria      = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $descripcion    = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    $servicios      = mysqli_real_escape_string($conexion, $_POST['servicios'] ?? '');
    $precio         = mysqli_real_escape_string($conexion, $_POST['precio']);
    $precio_mayoreo = mysqli_real_escape_string($conexion, $_POST['precio_mayoreo']);
    $stock          = intval($_POST['stock']);
    $publicado      = isset($_POST['publicado']) ? 1 : 0;

    $imagen    = subirImagenProducto($_FILES['imagen'] ?? null);
    $imagenSql = $imagen ? "'$imagen'" : "NULL";

    mysqli_query($conexion, "INSERT INTO productos (nombre, categoria, descripcion, servicios, precio_base, precio_mayoreo, stock, activo, publicado, imagen) VALUES ('$nombre','$categoria','$descripcion','$servicios','$precio','$precio_mayoreo','$stock', 1, $publicado, $imagenSql)");
    $nuevoId = mysqli_insert_id($conexion);
    guardarVariantes($conexion, $nuevoId);

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
    $descripcion    = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    $servicios      = mysqli_real_escape_string($conexion, $_POST['servicios'] ?? '');
    $precio         = mysqli_real_escape_string($conexion, $_POST['precio']);
    $precio_mayoreo = mysqli_real_escape_string($conexion, $_POST['precio_mayoreo']);
    $stock          = intval($_POST['stock']);
    $publicado      = isset($_POST['publicado']) ? 1 : 0;

    $actualRes    = mysqli_query($conexion, "SELECT imagen FROM productos WHERE id='$id'");
    $imagenActual = mysqli_fetch_array($actualRes)['imagen'];

    $imagenNueva = subirImagenProducto($_FILES['imagen'] ?? null);
    $imagenFinal = $imagenNueva ? $imagenNueva : $imagenActual;
    $imagenSql   = $imagenFinal ? "'$imagenFinal'" : "NULL";

    mysqli_query($conexion, "UPDATE productos SET nombre='$nombre', categoria='$categoria', descripcion='$descripcion', servicios='$servicios', precio_base='$precio', precio_mayoreo='$precio_mayoreo', stock='$stock', publicado=$publicado, imagen=$imagenSql WHERE id='$id'");
    guardarVariantes($conexion, $id);

    header("Location: inventario.php");
    exit();
}

// ELIMINAR (BORRADO LÓGICO)
if(isset($_GET['eliminar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $id            = intval($_GET['eliminar']);
    $eliminado_por = mysqli_real_escape_string($conexion, $_SESSION['usuario']);
    $fecha_actual  = date('Y-m-d H:i:s');
    mysqli_query($conexion, "UPDATE productos SET activo=0, deleted_by='$eliminado_por', deleted_at='$fecha_actual' WHERE id='$id'");
    header("Location: inventario.php");
    exit();
}

// PUBLICAR / OCULTAR
if(isset($_GET['togglePublicado'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $id     = intval($_GET['togglePublicado']);
    $estado = intval($_GET['estado']);
    mysqli_query($conexion, "UPDATE productos SET publicado=$estado WHERE id='$id'");
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

$buscar = isset($_GET['buscar']) ? $_GET['buscar'] : "";
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

        <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead style="position: sticky; top: 0; background-color: #4e73df; color: white; z-index: 10;">
                <tr>
                    <th style="padding: 12px; text-align: left;">Imagen</th>
                    <th style="padding: 12px; text-align: left;">Producto</th>
                    <th style="padding: 12px; text-align: left;">Categoría</th>
                    <th style="padding: 12px; text-align: left;">Precio Normal</th>
                    <th style="padding: 12px; text-align: left;">Precio Mayoreo</th>
                    <th style="padding: 12px; text-align: left;">Stock</th>
                    <th style="padding: 12px; text-align: left;">Estado</th>
                    <?php if($_SESSION['rol'] != "Empleado"){ ?>
                        <th style="padding: 12px; text-align: left;">Visible</th>
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
                echo "<tr><td colspan='10' style='text-align:center; padding:20px; color:#858796;'>No hay productos que coincidan con la búsqueda.</td></tr>";
            }

            while($mostrar = mysqli_fetch_array($resultado)){
                if($mostrar['stock'] <= 0){
                    $estado = "Agotado"; $badge = "badge-agotado";
                } elseif($mostrar['stock'] <= 5){
                    $estado = "Stock Bajo"; $badge = "badge-bajo";
                } else {
                    $estado = "Disponible"; $badge = "badge-disponible";
                }

                $resVarTabla  = mysqli_query($conexion, "SELECT medida, precio FROM producto_variantes WHERE producto_id=" . $mostrar['id']);
                $variantesArr = [];
                while ($vt = mysqli_fetch_array($resVarTabla)) { $variantesArr[] = ['medida' => $vt['medida'], 'precio' => $vt['precio']]; }
                $variantesJsonAttr = htmlspecialchars(json_encode($variantesArr), ENT_QUOTES);
            ?>
            <tr style="border-bottom: 1px solid #eaecf4;">
                <td style="padding: 12px;">
                    <?php if (!empty($mostrar['imagen'])): ?>
                        <img src="../uploads/productos/<?php echo htmlspecialchars($mostrar['imagen']); ?>" style="width:45px;height:45px;object-fit:cover;border-radius:6px;">
                    <?php else: ?>
                        <span style="font-size:11px;color:#999;">Sin imagen</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($mostrar['nombre']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($mostrar['categoria']); ?></td>
                <td style="padding: 12px;">$<?php echo number_format($mostrar['precio_base'], 2); ?></td>
                <td style="padding: 12px;">$<?php echo number_format($mostrar['precio_mayoreo'], 2); ?></td>
                <td style="padding: 12px;"><strong><?php echo $mostrar['stock']; ?></strong></td>
                <td style="padding: 12px;"><span class="badge <?php echo $badge; ?>"><?php echo $estado; ?></span></td>

                <?php if($_SESSION['rol'] != "Empleado"){ ?>
                <td style="padding: 12px;">
                    <?php if ($mostrar['publicado'] == 1): ?>
                        <a href="?togglePublicado=<?php echo $mostrar['id']; ?>&estado=0" class="badge badge-disponible" style="text-decoration:none;cursor:pointer;">👁️ Publicado</a>
                    <?php else: ?>
                        <a href="?togglePublicado=<?php echo $mostrar['id']; ?>&estado=1" class="badge badge-agotado" style="text-decoration:none;cursor:pointer;">🚫 Oculto</a>
                    <?php endif; ?>
                </td>
                <td style="padding: 12px;">
                    <form method="POST" style="display:flex; gap:6px; align-items:center; margin:0;">
                        <input type="hidden" name="id" value="<?php echo $mostrar['id']; ?>">
                        <input type="number" name="stock" class="input-stock" value="<?php echo $mostrar['stock']; ?>" min="0" style="width:70px; padding:4px;">
                        <button type="submit" name="ajustar" class="btn-panel btn-verde" style="font-size:11px; padding:4px 8px;">Actualizar</button>
                    </form>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                        <button class="btn-panel btn-naranja"
                            style="font-size:11px; padding:5px 8px; background:#db7a00; border-color:#db7a00; color:white; border-radius:4px; font-weight:bold; cursor:pointer;"
                            data-id="<?php echo $mostrar['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($mostrar['nombre'], ENT_QUOTES); ?>"
                            data-categoria="<?php echo htmlspecialchars($mostrar['categoria'], ENT_QUOTES); ?>"
                            data-descripcion="<?php echo htmlspecialchars($mostrar['descripcion'] ?? '', ENT_QUOTES); ?>"
                            data-servicios="<?php echo htmlspecialchars($mostrar['servicios'] ?? '', ENT_QUOTES); ?>"
                            data-precio="<?php echo $mostrar['precio_base']; ?>"
                            data-precio-mayoreo="<?php echo $mostrar['precio_mayoreo']; ?>"
                            data-stock="<?php echo $mostrar['stock']; ?>"
                            data-publicado="<?php echo $mostrar['publicado']; ?>"
                            data-imagen="<?php echo htmlspecialchars($mostrar['imagen'] ?? '', ENT_QUOTES); ?>"
                            data-variantes="<?php echo $variantesJsonAttr; ?>"
                            onclick="abrirModalEditarFromButton(this)">
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
    <div class="modal-contenido" style="max-height:90vh; overflow-y:auto;">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <h2 id="modalTitulo">Nuevo Producto</h2>
        <form method="POST" id="formProducto" enctype="multipart/form-data">
            <input type="hidden" name="id" id="campoId">
            <input type="text" name="nombre" id="campoNombre" placeholder="Nombre del producto" required>
            <input type="text" name="categoria" id="campoCategoria" placeholder="Categoría (impresion / promocionales / cuadros)">
            <textarea name="descripcion" id="campoDescripcion" placeholder="Descripción corta" style="width:100%; min-height:60px; margin-top:8px; padding:8px; border:1px solid #d1d3e2; border-radius:4px; font-size:13px;"></textarea>

            <label style="display:block; margin-top:10px; font-size:13px; font-weight:600;">Tags visibles en el catálogo <span style="font-weight:400; color:#888;">(separados por coma, o deja vacío)</span></label>
            <input type="text" name="servicios" id="campoServicios"
                placeholder="Ej: 🎨 Diseño personalizado, 📲 Envía tu logo"
                style="width:100%; margin-top:4px; padding:8px; border:1px solid #d1d3e2; border-radius:4px; font-size:13px;">

            <input type="number" step="0.01" min="0" name="precio" id="campoPrecio" placeholder="Precio normal" required>
            <input type="number" step="0.01" min="0" name="precio_mayoreo" id="campoPrecioMayoreo" placeholder="Precio mayoreo" required>
            <input type="number" min="0" name="stock" id="campoStock" placeholder="Stock" required>

            <label style="display:block; margin-top:12px; font-size:13px; font-weight:600;">Imagen del producto</label>
            <input type="file" name="imagen" id="campoImagen" accept="image/jpeg,image/png,image/webp">
            <img id="previewImagenActual" src="" style="width:60px; display:none; margin-top:6px; border-radius:6px;">

            <label style="display:flex; align-items:center; gap:6px; margin-top:12px; font-size:13px;">
                <input type="checkbox" name="publicado" id="campoPublicado" checked> Mostrar en página pública
            </label>

            <hr style="margin:16px 0;">
            <strong style="font-size:13px;">Medidas y precios (para el cotizador)</strong>
            <div id="contenedorVariantes" style="margin-top:8px;"></div>
            <button type="button" onclick="agregarFilaVariante()" class="btn-panel btn-verde" style="margin-top:8px;">+ Agregar medida</button>

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

function agregarFilaVariante(medida = '', precio = '') {
    const cont = document.getElementById('contenedorVariantes');
    const fila = document.createElement('div');
    fila.style = "display:flex; gap:8px; margin-top:6px;";
    fila.innerHTML = `
        <input type="text" name="medida[]" placeholder="Medida (ej. 40x60)" value="${medida}" style="flex:1; padding:6px; border:1px solid #d1d3e2; border-radius:4px;">
        <input type="number" step="0.01" name="precio_variante[]" placeholder="Precio" value="${precio}" style="width:110px; padding:6px; border:1px solid #d1d3e2; border-radius:4px;">
        <button type="button" onclick="this.parentElement.remove()" style="background:#e74a3b;color:white;border:none;border-radius:4px;padding:0 12px;cursor:pointer;">✕</button>
    `;
    cont.appendChild(fila);
}

function abrirModalNuevo(){
    document.getElementById('modalTitulo').textContent = 'Nuevo Producto';
    document.getElementById('campoId').value = '';
    document.getElementById('campoNombre').value = '';
    document.getElementById('campoCategoria').value = '';
    document.getElementById('campoDescripcion').value = '';
    document.getElementById('campoServicios').value = '';
    document.getElementById('campoPrecio').value = '';
    document.getElementById('campoPrecioMayoreo').value = '';
    document.getElementById('campoStock').value = '';
    document.getElementById('campoPublicado').checked = true;
    document.getElementById('campoImagen').value = '';
    document.getElementById('previewImagenActual').style.display = 'none';
    document.getElementById('contenedorVariantes').innerHTML = '';
    agregarFilaVariante();
    document.getElementById('btnSubmit').name = 'guardar';
    document.getElementById('btnSubmit').textContent = 'Guardar Producto';
    document.getElementById('modalProducto').classList.add('active');
}

function abrirModalEditarFromButton(btn){
    document.getElementById('modalTitulo').textContent = 'Editar Producto';
    document.getElementById('campoId').value = btn.dataset.id;
    document.getElementById('campoNombre').value = btn.dataset.nombre;
    document.getElementById('campoCategoria').value = btn.dataset.categoria;
    document.getElementById('campoDescripcion').value = btn.dataset.descripcion;
    document.getElementById('campoServicios').value = btn.dataset.servicios || '';
    document.getElementById('campoPrecio').value = btn.dataset.precio;
    document.getElementById('campoPrecioMayoreo').value = btn.dataset.precioMayoreo;
    document.getElementById('campoStock').value = btn.dataset.stock;
    document.getElementById('campoPublicado').checked = btn.dataset.publicado == '1';
    document.getElementById('campoImagen').value = '';

    const preview = document.getElementById('previewImagenActual');
    if (btn.dataset.imagen) {
        preview.src = '../uploads/productos/' + btn.dataset.imagen;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    document.getElementById('contenedorVariantes').innerHTML = '';
    const variantes = JSON.parse(btn.dataset.variantes || '[]');
    if (variantes.length === 0) {
        agregarFilaVariante();
    } else {
        variantes.forEach(v => agregarFilaVariante(v.medida, v.precio));
    }

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