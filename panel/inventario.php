<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

if(isset($_POST['guardar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $nombre         = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $categoria      = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $precio         = $_POST['precio'];
    $precio_mayoreo = $_POST['precio_mayoreo'];
    $stock          = intval($_POST['stock']);
    mysqli_query($conexion, "INSERT INTO productos (nombre, categoria, precio_base, precio_mayoreo, stock) VALUES ('$nombre','$categoria','$precio','$precio_mayoreo','$stock')");
    header("Location: inventario.php");
    exit();
}

if(isset($_POST['editar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $id             = intval($_POST['id']);
    $nombre         = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $categoria      = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $precio         = $_POST['precio'];
    $precio_mayoreo = $_POST['precio_mayoreo'];
    $stock          = intval($_POST['stock']);
    mysqli_query($conexion, "UPDATE productos SET nombre='$nombre', categoria='$categoria', precio_base='$precio', precio_mayoreo='$precio_mayoreo', stock='$stock' WHERE id='$id'");
    header("Location: inventario.php");
    exit();
}

if(isset($_GET['eliminar'])){
    if($_SESSION['rol'] == "Empleado"){
        echo "<script>alert('Acceso denegado.'); window.location='inventario.php';</script>";
        exit();
    }
    $id = intval($_GET['eliminar']);
    mysqli_query($conexion, "DELETE FROM productos WHERE id='$id'");
    header("Location: inventario.php");
    exit();
}

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
    <title>SGI BAAK - Inventario</title>
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

    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar producto..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button type="submit">Buscar</button>
            <a href="inventario.php" class="btn-limpiar">Limpiar</a>
        </form>
        <?php if($_SESSION['rol'] != "Empleado"){ ?>
            <button class="btn-nuevo" onclick="abrirModalNuevo()">+ Nuevo Producto</button>
        <?php } ?>
    </div>

    <div class="tabla-reciente">
        <h2>Productos Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio Normal</th>
                    <th>Precio Mayoreo</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <?php if($_SESSION['rol'] != "Empleado"){ ?>
                        <th>Ajustar Stock</th>
                        <th>Acciones</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php
            if($buscar != ""){
                $sql = "SELECT * FROM productos WHERE nombre LIKE '%$buscar%' OR categoria LIKE '%$buscar%' ORDER BY stock ASC";
            } else {
                $sql = "SELECT * FROM productos ORDER BY stock ASC";
            }
            $resultado = mysqli_query($conexion, $sql);
            while($mostrar = mysqli_fetch_array($resultado)){
                if($mostrar['stock'] <= 0){
                    $estado = "Agotado"; $badge = "badge-agotado";
                } elseif($mostrar['stock'] <= 5){
                    $estado = "Stock Bajo"; $badge = "badge-bajo";
                } else {
                    $estado = "Disponible"; $badge = "badge-disponible";
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($mostrar['nombre']); ?></td>
                <td><?php echo htmlspecialchars($mostrar['categoria']); ?></td>
                <td>$<?php echo number_format($mostrar['precio_base'], 2); ?></td>
                <td>$<?php echo number_format($mostrar['precio_mayoreo'], 2); ?></td>
                <td><strong><?php echo $mostrar['stock']; ?></strong></td>
                <td><span class="badge <?php echo $badge; ?>"><?php echo $estado; ?></span></td>
                <?php if($_SESSION['rol'] != "Empleado"){ ?>
                <td>
                    <form method="POST" style="display:flex; gap:6px; align-items:center;">
                        <input type="hidden" name="id" value="<?php echo $mostrar['id']; ?>">
                        <input type="number" name="stock" class="input-stock" value="<?php echo $mostrar['stock']; ?>" min="0">
                        <button type="submit" name="ajustar" class="btn-ajustar">Actualizar</button>
                    </form>
                </td>
                <td>
                    <button class="btn-ajustar" onclick="abrirModalEditar(<?php echo $mostrar['id']; ?>,'<?php echo addslashes($mostrar['nombre']); ?>','<?php echo addslashes($mostrar['categoria']); ?>','<?php echo $mostrar['precio_base']; ?>','<?php echo $mostrar['precio_mayoreo']; ?>',<?php echo $mostrar['stock']; ?>)">✎ Editar</button>
                    <a href="?eliminar=<?php echo $mostrar['id']; ?>" onclick="return confirm('¿Eliminar «<?php echo addslashes($mostrar['nombre']); ?>»?')" style="color:#dc3545; margin-left:6px;">🗑 Eliminar</a>
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
            <input type="number" step="0.01" min="0" name="precio" id="campoPrecio" placeholder="Precio normal">
            <input type="number" step="0.01" min="0" name="precio_mayoreo" id="campoPrecioMayoreo" placeholder="Precio mayoreo">
            <input type="number" min="0" name="stock" id="campoStock" placeholder="Stock">
            <button type="submit" name="guardar" id="btnSubmit">Guardar Producto</button>
        </form>
    </div>
</div>

<script>
function abrirModalNuevo(){
    document.getElementById('modalTitulo').textContent     = 'Nuevo Producto';
    document.getElementById('campoId').value               = '';
    document.getElementById('campoNombre').value           = '';
    document.getElementById('campoCategoria').value        = '';
    document.getElementById('campoPrecio').value           = '';
    document.getElementById('campoPrecioMayoreo').value    = '';
    document.getElementById('campoStock').value            = '';
    document.getElementById('btnSubmit').name              = 'guardar';
    document.getElementById('btnSubmit').textContent       = 'Guardar Producto';
    document.getElementById('modalProducto').style.display = 'flex';
}
function abrirModalEditar(id, nombre, categoria, precio, precioMayoreo, stock){
    document.getElementById('modalTitulo').textContent     = 'Editar Producto';
    document.getElementById('campoId').value               = id;
    document.getElementById('campoNombre').value           = nombre;
    document.getElementById('campoCategoria').value        = categoria;
    document.getElementById('campoPrecio').value           = precio;
    document.getElementById('campoPrecioMayoreo').value    = precioMayoreo;
    document.getElementById('campoStock').value            = stock;
    document.getElementById('btnSubmit').name              = 'editar';
    document.getElementById('btnSubmit').textContent       = 'Guardar Cambios';
    document.getElementById('modalProducto').style.display = 'flex';
}
function cerrarModal(){
    document.getElementById('modalProducto').style.display = 'none';
}
window.onclick = function(event){
    let modal = document.getElementById('modalProducto');
    if(event.target == modal){ modal.style.display = 'none'; }
}
</script>
<?php } ?>

</body>
</html>