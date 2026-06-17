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

// GUARDAR NUEVO EQUIPO
if(isset($_POST['guardar'])){
    $nombre       = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $estado       = mysqli_real_escape_string($conexion, $_POST['estado']);
    
    mysqli_query($conexion, "INSERT INTO equipos (nombre, descripcion, estado) VALUES ('$nombre','$descripcion','$estado')");
    header("Location: equipos.php");
    exit();
}

// ELIMINAR EQUIPO
if(isset($_GET['eliminar'])){
    $id = intval($_GET['eliminar']);
    mysqli_query($conexion, "DELETE FROM equipos WHERE id='$id'");
    header("Location: equipos.php");
    exit();
}

// PROCESAR EDICIÓN
if(isset($_POST['editar'])){
    $id            = intval($_POST['id']);
    $nombre        = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $estado        = mysqli_real_escape_string($conexion, $_POST['estado']);
    
    mysqli_query($conexion, "UPDATE equipos SET nombre='$nombre', descripcion='$descripcion', estado='$estado' WHERE id='$id'");
    header("Location: equipos.php");
    exit();
}

$equipo_editar = null;
if(isset($_GET['editar'])){
    $id = intval($_GET['editar']);
    $res = mysqli_query($conexion, "SELECT * FROM equipos WHERE id='$id'");
    $equipo_editar = mysqli_fetch_array($res);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Equipos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">🛠️</span>
        <h2>Módulo de Equipos</h2>
    </div>
</div>

<div class="content">

    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar equipo..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button type="submit" class="btn-panel btn-verde">Buscar</button>
            <a href="equipos.php" class="btn-limpiar">Limpiar</a>
        </form>
        <button class="btn-nuevo" onclick="abrirModal()">+ Nuevo Equipo</button>
    </div>

    <div class="tabla-reciente">
        <h2>Lista de Equipos</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($buscar != ""){
                $sql = "SELECT * FROM equipos WHERE nombre LIKE '%$buscar%' ORDER BY id DESC";
            } else {
                $sql = "SELECT * FROM equipos ORDER BY id DESC";
            }
            $resultado = mysqli_query($conexion, $sql);
            while($mostrar = mysqli_fetch_array($resultado)){
            ?>
            <tr>
                <td><?php echo htmlspecialchars($mostrar['nombre']); ?></td>
                <td><?php echo htmlspecialchars($mostrar['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($mostrar['estado']); ?></td>
                <td style="text-align: center; display: flex; gap: 8px; justify-content: center;">
                    <a href="?editar=<?php echo $mostrar['id']; ?>" class="btn-panel btn-verde">
                        ✏️ Editar
                    </a>
                    <a href="#"
                       onclick="confirmarAccion('¿Eliminar equipo?', 'El equipo <strong><?php echo htmlspecialchars($mostrar['nombre'], ENT_QUOTES); ?></strong> será eliminado permanentemente del sistema.', '🗑️', 'confirm-rojo', 'Sí, eliminar', '?eliminar=<?php echo $mostrar['id']; ?>')"
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

<div class="modal" id="modalEquipo">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <?php if($equipo_editar){ ?>
            <h2>Editar Equipo</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $equipo_editar['id']; ?>">
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($equipo_editar['nombre']); ?>" required>
                <input type="text" name="descripcion" value="<?php echo htmlspecialchars($equipo_editar['descripcion']); ?>">
                <select name="estado" required>
                    <option value="Activo" <?php if($equipo_editar['estado']=='Activo') echo 'selected'; ?>>Activo</option>
                    <option value="Mantenimiento" <?php if($equipo_editar['estado']=='Mantenimiento') echo 'selected'; ?>>Mantenimiento</option>
                </select>
                <div class="modal-footer">
                    <button type="submit" name="editar" class="btn-guardar">Guardar Cambios</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        <?php } else { ?>
            <h2>Nuevo Equipo</h2>
            <form method="POST">
                <input type="text" name="nombre" placeholder="Nombre del equipo" required>
                <input type="text" name="descripcion" placeholder="Descripción">
                <select name="estado" required>
                    <option value="Activo">Activo</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                </select>
                <div class="modal-footer">
                    <button type="submit" name="guardar" class="btn-guardar">Guardar Equipo</button>
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
document.getElementById('modal-confirmar').addEventListener('click', function(e){
    if(e.target === this) cerrarConfirmar();
});

function abrirModal(){
    document.getElementById("modalEquipo").classList.add("active");
}
function cerrarModal(){
    document.getElementById("modalEquipo").classList.remove("active");
    if(window.location.search.includes('editar')) { window.location.href = 'equipos.php'; }
}
</script>

<?php if($equipo_editar){ ?>
<script>document.getElementById("modalEquipo").classList.add("active");</script>
<?php } ?>

</body>
</html>