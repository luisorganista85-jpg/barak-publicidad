<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

$buscar = "";
if(isset($_GET['buscar'])){
    $buscar = $_GET['buscar'];
}

if(isset($_POST['guardar'])){
    $nombre    = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo    = mysqli_real_escape_string($conexion, $_POST['correo']);
    $empresa   = mysqli_real_escape_string($conexion, $_POST['empresa']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    mysqli_query($conexion, "INSERT INTO clientes (nombre, telefono, correo, empresa, direccion) VALUES ('$nombre','$telefono','$correo','$empresa','$direccion')");
    header("Location: clientes.php");
    exit();
}

// BORRADO LÓGICO — ya no hace DELETE, pone activo=0
if(isset($_GET['eliminar'])){
    $id         = intval($_GET['eliminar']);
    $deleted_by = mysqli_real_escape_string($conexion, $_SESSION['usuario']);
    mysqli_query($conexion, "UPDATE clientes SET activo=0, deleted_at=NOW(), deleted_by='$deleted_by' WHERE id='$id'");
    header("Location: clientes.php");
    exit();
}

if(isset($_POST['editar'])){
    $id        = intval($_POST['id']);
    $nombre    = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo    = mysqli_real_escape_string($conexion, $_POST['correo']);
    $empresa   = mysqli_real_escape_string($conexion, $_POST['empresa']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    mysqli_query($conexion, "UPDATE clientes SET nombre='$nombre', telefono='$telefono', correo='$correo', empresa='$empresa', direccion='$direccion' WHERE id='$id'");
    header("Location: clientes.php");
    exit();
}

$cliente_editar = null;
if(isset($_GET['editar'])){
    $id = intval($_GET['editar']);
    $res = mysqli_query($conexion, "SELECT * FROM clientes WHERE id='$id'");
    $cliente_editar = mysqli_fetch_array($res);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Clientes</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">👥</span>
        <h2>Módulo de Clientes</h2>
    </div>
</div>

<div class="content">

    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar cliente..." value="<?php echo $buscar; ?>">
            <button type="submit" class="btn-panel btn-verde">Buscar</button>
            <a href="clientes.php" class="btn-limpiar">Limpiar</a>
        </form>
        <button class="btn-nuevo" onclick="abrirModal()">+ Nuevo Cliente</button>
    </div>

    <div class="tabla-reciente">
        <h2>Lista de Clientes</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Empresa</th>
                    <th>Dirección</th>
                    <th style="width: 220px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($buscar != ""){
                $sql = "SELECT * FROM clientes WHERE activo=1 AND (nombre LIKE '%$buscar%' OR empresa LIKE '%$buscar%' OR telefono LIKE '%$buscar%') ORDER BY id DESC";
            } else {
                $sql = "SELECT * FROM clientes WHERE activo=1 ORDER BY id DESC";
            }
            $resultado = mysqli_query($conexion, $sql);
            while($mostrar = mysqli_fetch_array($resultado)){
            ?>
            <tr>
                <td><?php echo $mostrar['nombre']; ?></td>
                <td><?php echo $mostrar['telefono']; ?></td>
                <td><?php echo $mostrar['correo']; ?></td>
                <td><?php echo $mostrar['empresa']; ?></td>
                <td><?php echo $mostrar['direccion']; ?></td>
                <td style="text-align: center; display: flex; gap: 8px; justify-content: center;">
                    <a href="?editar=<?php echo $mostrar['id']; ?>" class="btn-panel btn-verde">
                        ✏️ Editar
                    </a>
                    <a href="#"
                       onclick="confirmarAccion('¿Mover a la papelera?', 'El cliente <strong><?php echo htmlspecialchars($mostrar['nombre'], ENT_QUOTES); ?></strong> será enviado a la papelera. Podrás restaurarlo después.', '🗑️', 'confirm-rojo', 'Sí, eliminar', '?eliminar=<?php echo $mostrar['id']; ?>')"
                       class="btn-panel btn-rojo">
                       🗑️ Eliminar
                    </a>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Modal Nuevo / Editar Cliente -->
<div class="modal" id="modalCliente">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>

        <?php if($cliente_editar){ ?>
            <h2>Editar Cliente</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $cliente_editar['id']; ?>">
                <input type="text" name="nombre" value="<?php echo $cliente_editar['nombre']; ?>" required>
                <input type="text" name="telefono" value="<?php echo $cliente_editar['telefono']; ?>">
                <input type="email" name="correo" value="<?php echo $cliente_editar['correo']; ?>">
                <input type="text" name="empresa" value="<?php echo $cliente_editar['empresa']; ?>">
                <textarea name="direccion"><?php echo $cliente_editar['direccion']; ?></textarea>
                <div class="modal-footer">
                    <button type="submit" name="editar" class="btn-guardar">Guardar Cambios</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        <?php } else { ?>
            <h2>Nuevo Cliente</h2>
            <form method="POST">
                <input type="text" name="nombre" placeholder="Nombre del Cliente" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <input type="email" name="correo" placeholder="Correo">
                <input type="text" name="empresa" placeholder="Empresa">
                <textarea name="direccion" placeholder="Dirección"></textarea>
                <div class="modal-footer">
                    <button type="submit" name="guardar" class="btn-guardar">Guardar Cliente</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        <?php } ?>
    </div>
</div>

<!-- Modal de Confirmación Personalizado (Sección 10) -->
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

<!-- Contenedor de Toasts -->
<div id="toast-container"></div>

<div class="footer-panel"><strong>SGI BARAK</strong> — Sistema de Gestión Integral | © 2026</div>

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

document.getElementById('modal-confirmar').addEventListener('click', function(e){
    if(e.target === this) cerrarConfirmar();
});

// ── TOAST ─────────────────────────────────────────────────────────────
function mostrarToast(mensaje, tipo) {
    tipo = tipo || 'info';
    var iconos = { exito: '✅', error: '❌', aviso: '⚠️', info: 'ℹ️' };
    var container = document.getElementById('toast-container');
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + tipo;
    toast.innerHTML = '<span class="toast-icono">' + (iconos[tipo] || 'ℹ️') + '</span>'
                    + '<span class="toast-msg">' + mensaje + '</span>'
                    + '<button class="toast-cerrar" onclick="cerrarToast(this.parentElement)">✕</button>';
    container.appendChild(toast);
    setTimeout(function(){ cerrarToast(toast); }, 3500);
}

function cerrarToast(toast) {
    if(!toast || toast.classList.contains('saliendo')) return;
    toast.classList.add('saliendo');
    setTimeout(function(){ if(toast.parentElement) toast.parentElement.removeChild(toast); }, 320);
}

// ── MODAL CLIENTE ─────────────────────────────────────────────────────
function abrirModal(){
    document.getElementById("modalCliente").classList.add("active");
}
function cerrarModal(){
    document.getElementById("modalCliente").classList.remove("active");
    if(window.location.search.includes('editar')) {
        window.location.href = 'clientes.php';
    }
}
window.onclick = function(event){
    var modal = document.getElementById("modalCliente");
    if(event.target == modal){ cerrarModal(); }
}
</script>

<?php if($cliente_editar){ ?>
<script>
document.getElementById("modalCliente").classList.add("active");
</script>
<?php } ?>

</body>
</html>