<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

if($_SESSION['rol'] != "Super Administrador" && $_SESSION['rol'] != "Administrador"):
    header("Location: dashboard.php");
    exit();
endif;

include("../config/conexion.php");

if(isset($_POST['guardar'])){
    $nombre   = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $usuario  = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol      = $_POST['rol'];

    if($_SESSION['rol'] == "Administrador" && $rol != "Empleado"){
        echo "<script>alert('No tienes permisos para asignar este rol.'); window.location='usuarios.php';</script>";
        exit();
    }

    $verificar = mysqli_query($conexion, "SELECT id FROM usuarios WHERE usuario='$usuario'");
    if(mysqli_num_rows($verificar) > 0){
        echo "<script>alert('El nombre de usuario ya existe, elige otro'); window.location='usuarios.php';</script>";
        exit();
    }

    mysqli_query($conexion, "INSERT INTO usuarios(nombre, usuario, password, rol) VALUES('$nombre','$usuario','$password','$rol')");
    header("Location: usuarios.php");
    exit();
}

if(isset($_GET['eliminar'])){
    if($_SESSION['rol'] != "Super Administrador"){
        echo "<script>alert('No tienes permisos para eliminar usuarios'); window.location='usuarios.php';</script>";
        exit();
    }
    $id = intval($_GET['eliminar']);
    $consulta = mysqli_query($conexion, "SELECT usuario FROM usuarios WHERE id=$id");
    $datos = mysqli_fetch_assoc($consulta);
    if($datos['usuario'] == $_SESSION['usuario']){
        echo "<script>alert('No puedes eliminar tu propia cuenta'); window.location='usuarios.php';</script>";
        exit();
    }
    mysqli_query($conexion, "DELETE FROM usuarios WHERE id=$id");
    header("Location: usuarios.php");
    exit();
}

if(isset($_POST['editar'])){
    if($_SESSION['rol'] != "Super Administrador"){
        echo "<script>alert('No tienes permisos para editar usuarios'); window.location='usuarios.php';</script>";
        exit();
    }
    $id      = intval($_POST['id']);
    $nombre  = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $rol     = $_POST['rol'];
    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', usuario='$usuario', password='$password', rol='$rol' WHERE id=$id");
    } else {
        mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', usuario='$usuario', rol='$rol' WHERE id=$id");
    }
    header("Location: usuarios.php");
    exit();
}

$buscar = "";
if(isset($_GET['buscar'])){
    $buscar = mysqli_real_escape_string($conexion, $_GET['buscar']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BAAK - Usuarios</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">🔐</span>
        <h2>Módulo de Administración de Usuarios</h2>
    </div>
</div>

<div class="content">

    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar usuario..." value="<?php echo $buscar; ?>">
            <button type="submit">Buscar</button>
            <a href="usuarios.php" class="btn-limpiar">Limpiar</a>
        </form>
        <button class="btn-nuevo" onclick="abrirModalNuevo()">+ Nuevo Usuario</button>
    </div>

    <div class="tabla-reciente">
        <h2>Usuarios Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <?php if($_SESSION['rol'] == "Super Administrador"){ ?>
                        <th>Acciones</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php
            $condicion_rol = ($_SESSION['rol'] == "Administrador") ? "AND rol != 'Super Administrador'" : "";
            if($buscar != ""){
                $sql = "SELECT * FROM usuarios WHERE (nombre LIKE '%$buscar%' OR usuario LIKE '%$buscar%') $condicion_rol ORDER BY id DESC";
            } else {
                $sql = "SELECT * FROM usuarios WHERE 1=1 $condicion_rol ORDER BY id DESC";
            }
            $resultado = mysqli_query($conexion, $sql);
            while($mostrar = mysqli_fetch_array($resultado)){
                if($mostrar['rol'] == 'Super Administrador') $badge = 'badge-super';
                elseif($mostrar['rol'] == 'Administrador') $badge = 'badge-admin';
                else $badge = 'badge-empleado';
            ?>
            <tr>
                <td><?php echo $mostrar['nombre']; ?></td>
                <td><?php echo $mostrar['usuario']; ?></td>
                <td><span class="<?php echo $badge; ?>"><?php echo $mostrar['rol']; ?></span></td>
                <?php if($_SESSION['rol'] == "Super Administrador"){ ?>
                <td>
                    <a href="#" onclick="event.preventDefault(); abrirModalEditar('<?php echo $mostrar['id']; ?>','<?php echo addslashes($mostrar['nombre']); ?>','<?php echo addslashes($mostrar['usuario']); ?>','<?php echo $mostrar['rol']; ?>')">Editar</a>
                    |
                    <a href="?eliminar=<?php echo $mostrar['id']; ?>" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                </td>
                <?php } ?>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<!-- MODAL NUEVO -->
<div class="modal-overlay" id="modalNuevo">
    <div class="modal-contenido">
        <button class="cerrar-modal-x" onclick="cerrarModal('modalNuevo')">✕</button>
        <h2>Nuevo Usuario</h2>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre Completo" required>
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <select name="rol" required>
                <option value="">Seleccionar Rol</option>
                <?php if($_SESSION['rol'] == "Super Administrador"){ ?>
                    <option value="Super Administrador">Super Administrador</option>
                    <option value="Administrador">Administrador</option>
                <?php } ?>
                <option value="Empleado">Empleado</option>
            </select>
            <button type="submit" name="guardar">Guardar Usuario</button>
        </form>
    </div>
</div>

<?php if($_SESSION['rol'] == "Super Administrador"){ ?>
<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-contenido">
        <button class="cerrar-modal-x" onclick="cerrarModal('modalEditar')">✕</button>
        <h2>Editar Usuario</h2>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="nombre" id="edit_nombre" required>
            <input type="text" name="usuario" id="edit_usuario" required>
            <input type="password" name="password" placeholder="Nueva contraseña (opcional)">
            <select name="rol" id="edit_rol" required>
                <option value="Super Administrador">Super Administrador</option>
                <option value="Administrador">Administrador</option>
                <option value="Empleado">Empleado</option>
            </select>
            <button type="submit" name="editar">Guardar Cambios</button>
        </form>
    </div>
</div>
<?php } ?>

<script>
function abrirModalNuevo(){ document.getElementById('modalNuevo').classList.add('active'); }
function cerrarModal(id){ document.getElementById(id).classList.remove('active'); }
function abrirModalEditar(id, nombre, usuario, rol){
    if(document.getElementById('modalEditar')){
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_usuario').value = usuario;
        document.getElementById('edit_rol').value = rol;
        document.getElementById('modalEditar').classList.add('active');
    }
}
</script>

</body>
</html>