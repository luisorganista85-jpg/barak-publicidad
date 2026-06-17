<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

// BUSCADOR
$buscar = "";
if(isset($_GET['buscar'])){
    $buscar = $_GET['buscar'];
}

// GUARDAR NUEVO PROVEEDOR
if(isset($_POST['guardar'])){
    $nombre    = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo    = mysqli_real_escape_string($conexion, $_POST['correo']);
    $productos = mysqli_real_escape_string($conexion, $_POST['productos']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    
    mysqli_query($conexion, "INSERT INTO proveedores (nombre, telefono, correo, productos, direccion) VALUES ('$nombre','$telefono','$correo','$productos','$direccion')");
    header("Location: proveedores.php");
    exit();
}

// BORRADO LÓGICO
if(isset($_GET['eliminar'])){
    $id         = intval($_GET['eliminar']);
    $deleted_by = mysqli_real_escape_string($conexion, $_SESSION['usuario']);
    mysqli_query($conexion, "UPDATE proveedores SET activo=0, deleted_at=NOW(), deleted_by='$deleted_by' WHERE id='$id'");
    header("Location: proveedores.php");
    exit();
}

// PROCESAR EDICIÓN
if(isset($_POST['editar'])){
    $id        = intval($_POST['id']);
    $nombre    = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo    = mysqli_real_escape_string($conexion, $_POST['correo']);
    $productos = mysqli_real_escape_string($conexion, $_POST['productos']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    
    mysqli_query($conexion, "UPDATE proveedores SET nombre='$nombre', telefono='$telefono', correo='$correo', productos='$productos', direccion='$direccion' WHERE id='$id'");
    header("Location: proveedores.php");
    exit();
}

$proveedor_editar = null;
if(isset($_GET['editar'])){
    $id = intval($_GET['editar']);
    $res = mysqli_query($conexion, "SELECT * FROM proveedores WHERE id='$id'");
    $proveedor_editar = mysqli_fetch_array($res);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Proveedores</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">🚚</span>
        <h2>Módulo de Proveedores</h2>
    </div>
</div>

<div class="content">

    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar proveedor..." value="<?php echo $buscar; ?>">
            <button type="submit" class="btn-panel btn-verde">Buscar</button>
            <a href="proveedores.php" class="btn-limpiar">Limpiar</a>
        </form>
        <button class="btn-nuevo" onclick="abrirModal()">+ Nuevo Proveedor</button>
    </div>

    <div class="tabla-reciente">
        <h2>Lista de Proveedores</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Productos</th>
                    <th>Dirección</th>
                    <th style="width: 220px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($buscar != ""){
                $sql = "SELECT * FROM proveedores WHERE activo=1 AND (nombre LIKE '%$buscar%' OR productos LIKE '%$buscar%' OR telefono LIKE '%$buscar%') ORDER BY id DESC";
            } else {
                $sql = "SELECT * FROM proveedores WHERE activo=1 ORDER BY id DESC";
            }
            $resultado = mysqli_query($conexion, $sql);
            while($mostrar = mysqli_fetch_array($resultado)){
            ?>
            <tr>
                <td><?php echo $mostrar['nombre']; ?></td>
                <td><?php echo $mostrar['telefono']; ?></td>
                <td><?php echo $mostrar['correo']; ?></td>
                <td><?php echo $mostrar['productos']; ?></td>
                <td><?php echo $mostrar['direccion']; ?></td>
                <td style="text-align: center; display: flex; gap: 8px; justify-content: center;">
                    <a href="?editar=<?php echo $mostrar['id']; ?>" class="btn-panel btn-verde">✏️ Editar</a>
                    <a href="#"
                       onclick="confirmarAccion('¿Mover a la papelera?', 'El proveedor <strong><?php echo htmlspecialchars($mostrar['nombre'], ENT_QUOTES); ?></strong> será enviado a la papelera.', '🗑️', 'confirm-rojo', 'Sí, eliminar', '?eliminar=<?php echo $mostrar['id']; ?>')"
                       class="btn-panel btn-rojo">🗑️ Eliminar</a>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="modalProveedor">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <?php if($proveedor_editar){ ?>
            <h2>Editar Proveedor</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $proveedor_editar['id']; ?>">
                <input type="text" name="nombre" value="<?php echo $proveedor_editar['nombre']; ?>" required>
                <input type="text" name="telefono" value="<?php echo $proveedor_editar['telefono']; ?>">
                <input type="email" name="correo" value="<?php echo $proveedor_editar['correo']; ?>">
                <input type="text" name="productos" value="<?php echo $proveedor_editar['productos']; ?>">
                <textarea name="direccion"><?php echo $proveedor_editar['direccion']; ?></textarea>
                <div class="modal-footer">
                    <button type="submit" name="editar" class="btn-guardar">Guardar Cambios</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        <?php } else { ?>
            <h2>Nuevo Proveedor</h2>
            <form method="POST">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <input type="email" name="correo" placeholder="Correo">
                <input type="text" name="productos" placeholder="Productos">
                <textarea name="direccion" placeholder="Dirección"></textarea>
                <div class="modal-footer">
                    <button type="submit" name="guardar" class="btn-guardar">Guardar Proveedor</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        <?php } ?>
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
function cerrarConfirmar() { document.getElementById('modal-confirmar').classList.remove('active'); _confirmarUrl = ''; }
function ejecutarConfirmar() { if(_confirmarUrl) window.location.href = _confirmarUrl; }
function abrirModal(){ document.getElementById("modalProveedor").classList.add("active"); }
function cerrarModal(){ document.getElementById("modalProveedor").classList.remove("active"); if(window.location.search.includes('editar')) window.location.href = 'proveedores.php'; }
</script>

<?php if($proveedor_editar){ ?>
<script>document.getElementById("modalProveedor").classList.add("active");</script>
<?php } ?>

</body>
</html>