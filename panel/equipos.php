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

// GUARDAR

if(isset($_POST['guardar'])){

    $nombre      = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $estado      = $_POST['estado'];

    mysqli_query($conexion, "INSERT INTO equipos
    (nombre, descripcion, estado)

    VALUES

    ('$nombre','$descripcion','$estado')");

    header("Location: equipos.php");
    exit();
}

// ELIMINAR

if(isset($_GET['eliminar'])){

    $id = $_GET['eliminar'];

    mysqli_query(
        $conexion,
        "DELETE FROM equipos WHERE id='$id'"
    );

    header("Location: equipos.php");
    exit();
}

// EDITAR

if(isset($_POST['editar'])){

    $id          = $_POST['id'];
    $nombre      = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $estado      = $_POST['estado'];

    mysqli_query($conexion, "UPDATE equipos SET

        nombre='$nombre',
        descripcion='$descripcion',
        estado='$estado'

        WHERE id='$id'

    ");

    header("Location: equipos.php");
    exit();
}

// EDITAR DATOS

$equipo_editar = null;

if(isset($_GET['editar'])){

    $id = $_GET['editar'];

    $res = mysqli_query(
        $conexion,
        "SELECT * FROM equipos WHERE id='$id'"
    );

    $equipo_editar = mysqli_fetch_array($res);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>SGI BAAK - Equipos</title>

<link rel="stylesheet" href="css/styles.css">

</head>

<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">

<div class="topbar-left">

<h2>Módulo de Equipos</h2>

</div>

</div>

<div class="content">

<h1>Equipos Registrados</h1>

<div class="acciones-top">

<form method="GET" class="buscador">

<input type="text"
name="buscar"
placeholder="Buscar equipo..."
value="<?php echo $buscar; ?>">

<button type="submit">
Buscar
</button>

<a href="equipos.php"
class="btn-limpiar">
Limpiar
</a>

</form>

<button class="btn-nuevo"
onclick="abrirModal()">

+ Nuevo Equipo

</button>

</div>

<div class="tabla-reciente">

<h2>Lista de Equipos</h2>

<table>

<thead>

<tr>

<th>Nombre</th>
<th>Descripción</th>
<th>Estado</th>
<th>Fecha</th>
<th>Acciones</th>

</tr>

</thead>

<tbody>

<?php

if($buscar != ""){

$sql = "SELECT * FROM equipos

WHERE nombre LIKE '%$buscar%'

ORDER BY id DESC";

}else{

$sql = "SELECT * FROM equipos
ORDER BY id DESC";

}

$resultado = mysqli_query(
$conexion,
$sql
);

while($mostrar =
mysqli_fetch_array($resultado)){

?>

<tr>

<td><?php echo $mostrar['nombre']; ?></td>

<td><?php echo $mostrar['descripcion']; ?></td>

<td><?php echo $mostrar['estado']; ?></td>

<td><?php echo $mostrar['fecha']; ?></td>

<td>

<a href="?editar=<?php
echo $mostrar['id']; ?>">

Editar

</a>

|

<a href="?eliminar=<?php
echo $mostrar['id']; ?>"

onclick="return confirm(
'¿Eliminar equipo?'
)">

Eliminar

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

<span class="cerrar"
onclick="cerrarModal()">

&times;

</span>

<?php if($equipo_editar){ ?>

<h2>Editar Equipo</h2>

<?php } else { ?>

<h2>Nuevo Equipo</h2>

<?php } ?>

<?php if($equipo_editar){ ?>

<form method="POST">

<input type="hidden"
name="id"
value="<?php
echo $equipo_editar['id'];
?>">

<input type="text"
name="nombre"
value="<?php
echo $equipo_editar['nombre'];
?>"
required>

<input type="text"
name="descripcion"
value="<?php
echo $equipo_editar['descripcion'];
?>">

<select name="estado">

<option value="Activo">Activo</option>
<option value="Mantenimiento">Mantenimiento</option>
<option value="Inactivo">Inactivo</option>

</select>

<button type="submit"
name="editar">

Guardar Cambios

</button>

</form>

<?php } else { ?>

<form method="POST">

<input type="text"
name="nombre"
placeholder="Nombre del Equipo"
required>

<input type="text"
name="descripcion"
placeholder="Descripción">

<select name="estado">

<option value="Activo">Activo</option>
<option value="Mantenimiento">Mantenimiento</option>
<option value="Inactivo">Inactivo</option>

</select>

<button type="submit"
name="guardar">

Guardar Equipo

</button>

</form>

<?php } ?>

</div>

</div>

<script>

function abrirModal(){

document.getElementById(
"modalEquipo"
).style.display = "flex";

}

function cerrarModal(){

document.getElementById(
"modalEquipo"
).style.display = "none";

}

window.onclick = function(event){

let modal =
document.getElementById(
"modalEquipo"
);

if(event.target == modal){

modal.style.display = "none";

}

}

</script>

<?php if($equipo_editar){ ?>

<script>

document.getElementById(
"modalEquipo"
).style.display = "flex";

</script>

<?php } ?>

</body>

</html>