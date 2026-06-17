<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

$usuario_sesion = $_SESSION['usuario'];
$verificar_estado = mysqli_query($conexion, "SELECT activo FROM usuarios WHERE usuario = '$usuario_sesion'");
$datos_estado = mysqli_fetch_assoc($verificar_estado);

if(!$datos_estado || $datos_estado['activo'] == 0){
    session_destroy();
    header("Location: /BARAK_PUBLICIDAD/login.php?error=cuenta_desactivada");
    exit();
}

if($_SESSION['rol'] != "Super Administrador" && $_SESSION['rol'] != "Administrador"){
    header("Location: dashboard.php");
    exit();
}

if(isset($_POST['guardar'])){
    $nombre   = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $usuario  = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol      = mysqli_real_escape_string($conexion, $_POST['rol']);

    if($_SESSION['rol'] == "Administrador" && $rol != "Empleado"){
        header("Location: usuarios.php?msg=sin_permiso"); exit();
    }

    $verificar = mysqli_query($conexion, "SELECT id FROM usuarios WHERE usuario='$usuario'");
    if(mysqli_num_rows($verificar) > 0){
        header("Location: usuarios.php?msg=usuario_existe"); exit();
    }

    mysqli_query($conexion, "INSERT INTO usuarios(nombre, usuario, password, rol, activo) VALUES('$nombre','$usuario','$password','$rol', 1)");
    header("Location: usuarios.php?msg=guardado"); exit();
}

if(isset($_GET['toggle'])){
    if($_SESSION['rol'] != "Super Administrador"){
        header("Location: usuarios.php?msg=sin_permiso"); exit();
    }
    $id = intval($_GET['toggle']);
    $consulta = mysqli_query($conexion, "SELECT usuario, activo FROM usuarios WHERE id=$id");
    $datos = mysqli_fetch_assoc($consulta);
    if($datos['usuario'] == $_SESSION['usuario']){
        header("Location: usuarios.php?msg=mismo_usuario"); exit();
    }
    $nuevo_estado = ($datos['activo'] == 1) ? 0 : 1;
    mysqli_query($conexion, "UPDATE usuarios SET activo=$nuevo_estado WHERE id=$id");
    header("Location: usuarios.php"); exit();
}

if(isset($_POST['editar'])){
    if($_SESSION['rol'] != "Super Administrador" && $_SESSION['rol'] != "Administrador"){
        header("Location: usuarios.php?msg=sin_permiso"); exit();
    }
    $id      = intval($_POST['id']);
    $nombre  = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $rol     = mysqli_real_escape_string($conexion, $_POST['rol']);

    $check_target = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT rol FROM usuarios WHERE id=$id"));
    if($_SESSION['rol'] == "Administrador" && $check_target['rol'] == "Super Administrador"){
        header("Location: usuarios.php?msg=no_editar_super"); exit();
    }

    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', usuario='$usuario', password='$password', rol='$rol' WHERE id=$id");
    } else {
        mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', usuario='$usuario', rol='$rol' WHERE id=$id");
    }
    header("Location: usuarios.php?msg=editado"); exit();
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
    <title>SGI BARAK - Usuarios</title>
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

    <?php if(isset($_GET['msg'])){ ?>
        <div class="alert-exito" style="display:flex; justify-content:space-between; align-items:center;">
            <span>
                <?php
                if($_GET['msg'] == 'sin_permiso')    echo "⚠️ Acción exclusiva del Súper Administrador.";
                if($_GET['msg'] == 'usuario_existe') echo "⚠️ El nombre de usuario ya existe, elige otro.";
                if($_GET['msg'] == 'mismo_usuario')  echo "❌ No puedes desactivar tu propia cuenta activa.";
                if($_GET['msg'] == 'no_editar_super')echo "❌ Un Administrador no puede editar a un Super Administrador.";
                if($_GET['msg'] == 'guardado')       echo "✅ Usuario registrado correctamente.";
                if($_GET['msg'] == 'editado')        echo "✅ Cambios guardados correctamente.";
                ?>
            </span>
            <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px;">✕</button>
        </div>
    <?php } ?>

    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar" placeholder="Buscar usuario..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button type="submit">Buscar</button>
            <a href="usuarios.php" class="btn-limpiar">Limpiar</a>
        </form>
        <button class="btn-nuevo" onclick="abrirModalNuevo()">+ Nuevo Usuario</button>
    </div>

    <div class="tabla-reciente" style="width:100%; max-height:550px; overflow-y:auto; border:1px solid #e3e6f0; border-radius:8px; margin-top:15px;">
        <div style="padding:15px; background:#f8f9fc; border-bottom:1px solid #e3e6f0;">
            <h2 style="margin:0; font-size:17px; color:#4e73df;">
                <?php echo ($buscar != "") ? "🔍 Resultados de Búsqueda para: '".htmlspecialchars($buscar)."'" : "📋 Gestión y Permisos de Cuentas"; ?>
            </h2>
        </div>
        <table style="width:100%; border-collapse:collapse; min-width:800px;">
            <thead style="position:sticky; top:0; background-color:#4e73df; color:white; z-index:10;">
                <tr>
                    <th style="padding:12px; text-align:left;">Nombre</th>
                    <th style="padding:12px; text-align:left;">Usuario</th>
                    <th style="padding:12px; text-align:left;">Rol del Sistema</th>
                    <th style="padding:12px; text-align:left;">Estado Cuenta</th>
                    <th style="padding:12px; text-align:center; width:220px;">Acciones</th>
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
            if(mysqli_num_rows($resultado) == 0){
                echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#858796;'>No se encontraron cuentas registradas.</td></tr>";
            }
            while($mostrar = mysqli_fetch_array($resultado)){
                if($mostrar['rol'] == 'Super Administrador') {
                    $badge_style = "background:#5a5c69; color:white; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:11px;";
                } elseif($mostrar['rol'] == 'Administrador') {
                    $badge_style = "background:#4e73df; color:white; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:11px;";
                } else {
                    $badge_style = "background:#36b9cc; color:white; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:11px;";
                }
                $estado_txt   = ($mostrar['activo'] == 1) ? 'Activo' : 'Inactivo';
                $estado_style = ($mostrar['activo'] == 1)
                    ? "background:#e2f6ed; color:#155724; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:12px;"
                    : "background:#f8d7da; color:#721c24; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:12px;";
            ?>
            <tr style="border-bottom:1px solid #eaecf4;">
                <td style="padding:12px;"><strong><?php echo htmlspecialchars($mostrar['nombre']); ?></strong></td>
                <td style="padding:12px; color:#4e73df;"><?php echo htmlspecialchars($mostrar['usuario']); ?></td>
                <td style="padding:12px;"><span style="<?php echo $badge_style; ?>"><?php echo htmlspecialchars($mostrar['rol']); ?></span></td>
                <td style="padding:12px;"><span style="<?php echo $estado_style; ?>"><?php echo $estado_txt; ?></span></td>
                <td style="padding:12px; text-align:center;">
                    <div style="display:flex; gap:8px; justify-content:center; align-items:center;">
                        <button style="font-size:11px; padding:5px 8px; background:#db7a00; color:white; border:none; border-radius:4px; font-weight:bold; cursor:pointer;"
                                onclick="abrirModalEditar('<?php echo $mostrar['id']; ?>','<?php echo addslashes($mostrar['nombre']); ?>','<?php echo addslashes($mostrar['usuario']); ?>','<?php echo $mostrar['rol']; ?>')">
                            ✏️ Editar
                        </button>
                        <?php if($_SESSION['rol'] == "Super Administrador"){ ?>
                            <?php if($mostrar['activo'] == 1){ ?>
                                <button style="font-size:11px; padding:5px 8px; background:#dc3545; color:white; border:none; border-radius:4px; font-weight:bold; cursor:pointer;"
                                        onclick="abrirConfirmacion('usuarios.php?toggle=<?php echo $mostrar['id']; ?>', '¿Desactivar este usuario?', 'No podrá iniciar sesión en el SGI.', '🔒', 'Desactivar')">
                                    ❌ Desactivar
                                </button>
                            <?php } else { ?>
                                <button style="font-size:11px; padding:5px 8px; background:#1cc88a; color:white; border:none; border-radius:4px; font-weight:bold; cursor:pointer;"
                                        onclick="abrirConfirmacion('usuarios.php?toggle=<?php echo $mostrar['id']; ?>', '¿Activar este usuario?', 'Volverá a poder iniciar sesión en el SGI.', '✅', 'Activar')">
                                    ✅ Activar
                                </button>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL NUEVO USUARIO -->
<div class="modal" id="modalNuevo">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal('modalNuevo')">&times;</span>
        <h2>Nuevo Usuario</h2>
        <form method="POST">
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Nombre Completo:</label>
            <input type="text" name="nombre" placeholder="Nombre completo del personal" required>
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Usuario de Acceso:</label>
            <input type="text" name="usuario" placeholder="Ej: luis_barak" required>
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Contraseña:</label>
            <input type="password" name="password" placeholder="Asignar contraseña" required>
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Rol asignado:</label>
            <select name="rol" required>
                <option value="">Seleccionar Rol</option>
                <?php if($_SESSION['rol'] == "Super Administrador"){ ?>
                    <option value="Super Administrador">Super Administrador</option>
                <?php } ?>
                <option value="Administrador">Administrador</option>
                <option value="Empleado">Empleado</option>
            </select>
            <div class="modal-footer">
                <button type="submit" name="guardar" class="btn-guardar">Guardar Usuario</button>
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalNuevo')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR USUARIO -->
<div class="modal" id="modalEditar">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal('modalEditar')">&times;</span>
        <h2>Editar Usuario</h2>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Nombre Completo:</label>
            <input type="text" name="nombre" id="edit_nombre" required>
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Usuario de Acceso:</label>
            <input type="text" name="usuario" id="edit_usuario" required>
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Contraseña:</label>
            <input type="password" name="password" placeholder="Nueva contraseña (dejar en blanco para no cambiar)">
            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#4e73df;">Rol asignado:</label>
            <select name="rol" id="edit_rol" required>
                <?php if($_SESSION['rol'] == "Super Administrador"){ ?>
                    <option value="Super Administrador">Super Administrador</option>
                <?php } ?>
                <option value="Administrador">Administrador</option>
                <option value="Empleado">Empleado</option>
            </select>
            <div class="modal-footer">
                <button type="submit" name="editar" class="btn-guardar" style="background:#db7a00;">Guardar Cambios</button>
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEditar')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CONFIRMACION GLOBAL -->
<div class="modal" id="modalConfirmacion">
    <div class="modal-contenido" style="max-width:400px; text-align:center;">
        <div id="conf-icono" style="font-size:52px; margin-bottom:12px;">🗑️</div>
        <h2 id="conf-titulo" style="margin:0 0 8px;">¿Confirmar acción?</h2>
        <p id="conf-desc" style="color:#64748b; font-size:14px; margin:0 0 24px;"></p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button class="btn-cancelar" onclick="cerrarModal('modalConfirmacion')">Cancelar</button>
            <button id="conf-btn" class="btn-guardar" style="background:#dc3545;" onclick="ejecutarConfirmacion()">Confirmar</button>
        </div>
    </div>
</div>

<div class="footer-panel"><strong>SGI BARAK</strong> — Sistema de Gestión Integral | © 2026</div>

<script>
var _urlConfirmacion = '';

function abrirConfirmacion(url, titulo, descripcion, icono, btnTexto){
    _urlConfirmacion = url;
    document.getElementById('conf-icono').innerText  = icono || '⚠️';
    document.getElementById('conf-titulo').innerText = titulo;
    document.getElementById('conf-desc').innerText   = descripcion;
    document.getElementById('conf-btn').innerText    = btnTexto || 'Confirmar';
    document.getElementById('modalConfirmacion').style.display = 'flex';
}

function ejecutarConfirmacion(){
    window.location.href = _urlConfirmacion;
}

function abrirModalNuevo(){ document.getElementById('modalNuevo').style.display = 'flex'; }

function cerrarModal(id){ document.getElementById(id).style.display = 'none'; }

function abrirModalEditar(id, nombre, usuario, rol){
    document.getElementById('edit_id').value      = id;
    document.getElementById('edit_nombre').value  = nombre;
    document.getElementById('edit_usuario').value = usuario;
    document.getElementById('edit_rol').value     = rol;
    document.getElementById('modalEditar').style.display = 'flex';
}

window.onclick = function(event){
    ['modalNuevo','modalEditar','modalConfirmacion'].forEach(function(id){
        let m = document.getElementById(id);
        if(event.target == m) m.style.display = 'none';
    });
}
</script>
</body>
</html>