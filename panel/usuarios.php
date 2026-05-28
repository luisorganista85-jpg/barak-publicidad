<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

// SOLO ADMINISTRADOR
if($_SESSION['rol'] != "Administrador"){
    header("Location: dashboard.php");
    exit();
}

include("../config/conexion.php");

// GUARDAR USUARIO (Corregido aquí: se agregó el $_POST correcto)
if(isset($_POST['guardar'])){
    $nombre   = $_POST['nombre'];
    $usuario  = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol      = $_POST['rol'];

    mysqli_query($conexion,
        "INSERT INTO usuarios(nombre, usuario, password, rol)
        VALUES('$nombre','$usuario','$password','$rol')"
    );

    header("Location: usuarios.php");
    exit();
}

// ELIMINAR USUARIO
if(isset($_GET['eliminar'])){
    $id = intval($_GET['eliminar']);
    mysqli_query($conexion, "DELETE FROM usuarios WHERE id=$id");
    header("Location: usuarios.php");
    exit();
}

// EDITAR USUARIO
if(isset($_POST['editar'])){
    $id      = intval($_POST['id']);
    $nombre  = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $rol     = $_POST['rol'];

    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        mysqli_query($conexion, "UPDATE usuarios SET
            nombre='$nombre',
            usuario='$usuario',
            password='$password',
            rol='$rol'
            WHERE id=$id");
    }else{
        mysqli_query($conexion, "UPDATE usuarios SET
            nombre='$nombre',
            usuario='$usuario',
            rol='$rol'
            WHERE id=$id");
    }

    header("Location: usuarios.php");
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
    <title>Usuarios</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <h2>Administración de Usuarios</h2>
    </div>
</div>

<div class="content">

    <h1>Usuarios del Sistema</h1>

    <div class="acciones-top">

        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar usuario..." value="<?php echo $buscar; ?>">
            <button type="submit">Buscar</button>
            <a href="usuarios.php" class="btn-limpiar">Limpiar</a>
        </form>

        <button class="btn-nuevo" onclick="abrirModalNuevo()">
            + Nuevo Usuario
        </button>

    </div>

    <div class="tabla-reciente">
        <h2>Usuarios Registrados</h2>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php
            if($buscar != ""){
                $sql = "SELECT * FROM usuarios
                        WHERE nombre LIKE '%$buscar%'
                        OR usuario LIKE '%$buscar%'
                        ORDER BY id DESC";
            }else{
                $sql = "SELECT * FROM usuarios ORDER BY id DESC";
            }

            $resultado = mysqli_query($conexion, $sql);

            while($mostrar = mysqli_fetch_array($resultado)){
                $badge = ($mostrar['rol'] == 'Administrador') ? 'badge-admin' : 'badge-empleado';
            ?>
                <tr>
                    <td><?php echo $mostrar['nombre']; ?></td>
                    <td><?php echo $mostrar['usuario']; ?></td>
                    <td><span class="<?php echo $badge; ?>"><?php echo $mostrar['rol']; ?></span></td>

                    <td>
                        <a href="#" class="link-tabla-editar" 
                           onclick="event.preventDefault(); abrirModalEditar(
                               '<?php echo $mostrar['id']; ?>',
                               '<?php echo addslashes($mostrar['nombre']); ?>',
                               '<?php echo addslashes($mostrar['usuario']); ?>',
                               '<?php echo $mostrar['rol']; ?>'
                           )">Editar</a>

                        <span class="separador-tabla">|</span>
                        
                        <a href="?eliminar=<?php echo $mostrar['id']; ?>"
                           onclick="return confirm('¿Eliminar usuario?')"
                           class="link-tabla-eliminar">
                           Eliminar
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>
</div>

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
                <option value="Administrador">Administrador</option>
                <option value="Empleado">Empleado</option>
            </select>

            <button type="submit" name="guardar">Guardar Usuario</button>
        </form>

    </div>
</div>

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
                <option value="">Seleccionar Rol</option>
                <option value="Administrador">Administrador</option>
                <option value="Empleado">Empleado</option>
            </select>

            <button type="submit" name="editar">Guardar Cambios</button>

        </form>

    </div>
</div>

<script>
function abrirModalNuevo(){
    document.getElementById('modalNuevo').classList.add('active');
}

function abrirModalEditar(id, nombre, usuario, rol){
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_usuario').value = usuario;
    document.getElementById('edit_rol').value = rol;

    document.getElementById('modalEditar').classList.add('active');
}

function cerrarModal(id){
    document.getElementById(id).classList.remove('active');
}

document.querySelectorAll('.modal-overlay').forEach(function(el){
    el.addEventListener('click', function(e){
        if(e.target === el){
            el.classList.remove('active');
        }
    });
});
</script>

</body>
</html>