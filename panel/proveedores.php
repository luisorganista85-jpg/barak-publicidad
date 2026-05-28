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
    $telefono    = $_POST['telefono'];
    $correo      = $_POST['correo'];
    $direccion   = $_POST['direccion'];
    $productos   = $_POST['productos'];

    mysqli_query($conexion, "INSERT INTO proveedores
    (nombre, telefono, correo, direccion, productos)

    VALUES

    ('$nombre','$telefono','$correo','$direccion','$productos')");

    header("Location: proveedores.php");
    exit();
}

// ELIMINAR

if(isset($_GET['eliminar'])){

    $id = $_GET['eliminar'];

    mysqli_query(
        $conexion,
        "DELETE FROM proveedores WHERE id='$id'"
    );

    header("Location: proveedores.php");
    exit();
}

// EDITAR

if(isset($_POST['editar'])){

    $id          = $_POST['id'];
    $nombre      = $_POST['nombre'];
    $telefono    = $_POST['telefono'];
    $correo      = $_POST['correo'];
    $direccion   = $_POST['direccion'];
    $productos   = $_POST['productos'];

    mysqli_query($conexion, "UPDATE proveedores SET

        nombre='$nombre',
        telefono='$telefono',
        correo='$correo',
        direccion='$direccion',
        productos='$productos'

        WHERE id='$id'

    ");

    header("Location: proveedores.php");
    exit();
}

// EDITAR DATOS

$proveedor_editar = null;

if(isset($_GET['editar'])){

    $id = $_GET['editar'];

    $res = mysqli_query(
        $conexion,
        "SELECT * FROM proveedores WHERE id='$id'"
    );

    $proveedor_editar = mysqli_fetch_array($res);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>SGI BAAK - Proveedores</title>

<link rel="stylesheet" href="css/styles.css">

</head>

<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">

<div class="topbar-left">

<h2>Módulo de Proveedores</h2>

</div>

</div>

<div class="content">

<h1>Proveedores Registrados</h1>

<div class="acciones-top">

<form method="GET" class="buscador">

<input type="text"
name="buscar"
placeholder="Buscar proveedor..."
value="<?php echo $buscar; ?>">

<button type="submit">
Buscar
</button>

<a href="proveedores.php"
class="btn-limpiar">
Limpiar
</a>

</form>

<button class="btn-nuevo"
onclick="abrirModal()">

+ Nuevo Proveedor

</button>

</div>

<div class="tabla-reciente">

<h2>Lista de Proveedores</h2>

<table>

<thead>

<tr>

<th>Nombre</th>
<th>Teléfono</th>
<th>Correo</th>
<th>Dirección</th>
<th>Productos</th>
<th>Fecha</th>
<th>Acciones</th>

</tr>

</thead>

<tbody>

<?php

if($buscar != ""){

$sql = "SELECT * FROM proveedores

WHERE nombre LIKE '%$buscar%'

ORDER BY id DESC";

}else{

$sql = "SELECT * FROM proveedores
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

<td><?php echo $mostrar['telefono']; ?></td>

<td><?php echo $mostrar['correo']; ?></td>

<td><?php echo $mostrar['direccion']; ?></td>

<td><?php echo $mostrar['productos']; ?></td>

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
'¿Eliminar proveedor?'
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

<div class="modal" id="modalProveedor">

<div class="modal-contenido">

<span class="cerrar"
onclick="cerrarModal()">

&times;

</span>

<?php if($proveedor_editar){ ?>

<h2>Editar Proveedor</h2>

<?php } else { ?>

<h2>Nuevo Proveedor</h2>

<?php } ?>

<?php if($proveedor_editar){ ?>

<form method="POST">

<input type="hidden"
name="id"
value="<?php
echo $proveedor_editar['id'];
?>">

<input type="text"
name="nombre"
value="<?php
echo $proveedor_editar['nombre'];
?>"
required>

<input type="text"
name="telefono"
value="<?php
echo $proveedor_editar['telefono'];
?>">

<input type="email"
name="correo"
value="<?php
echo $proveedor_editar['correo'];
?>">

<input type="text"
name="direccion"
value="<?php
echo $proveedor_editar['direccion'];
?>">

<input type="text"
name="productos"
value="<?php
echo $proveedor_editar['productos'];
?>">

<button type="submit"
name="editar">

Guardar Cambios

</button>

</form>

<?php } else { ?>

<form method="POST">

<input type="text"
name="nombre"
placeholder="Nombre del Proveedor"
required>

<input type="text"
name="telefono"
placeholder="Teléfono">

<input type="email"
name="correo"
placeholder="Correo">

<input type="text"
name="direccion"
placeholder="Dirección">

<input type="text"
name="productos"
placeholder="Vinil, lona, canvas, tintas, marcos..."

>

<button type="submit"
name="guardar">

Guardar Proveedor

</button>

</form>

<?php } ?>

</div>

</div>

<script>

function abrirModal(){

document.getElementById(
"modalProveedor"
).style.display = "flex";

}

function cerrarModal(){

document.getElementById(
"modalProveedor"
).style.display = "none";

}

window.onclick = function(event){

let modal =
document.getElementById(
"modalProveedor"
);

if(event.target == modal){

modal.style.display = "none";

}

}

</script>

<?php if($proveedor_editar){ ?>

<script>

document.getElementById(
"modalProveedor"
).style.display = "flex";

</script>

<?php } ?>

</body>

</html>