<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

// ==========================
// BUSCADOR
// ==========================

$buscar = "";

if(isset($_GET['buscar'])){

    $buscar = $_GET['buscar'];

}

// ==========================
// GUARDAR CLIENTE
// ==========================

if(isset($_POST['guardar'])){

    $nombre   = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo   = $_POST['correo'];
    $empresa  = $_POST['empresa'];
    $direccion = $_POST['direccion'];

    $sql = "INSERT INTO clientes
    (nombre, telefono, correo, empresa, direccion)

    VALUES

    ('$nombre','$telefono','$correo','$empresa','$direccion')";

    mysqli_query($conexion, $sql);

    header("Location: clientes.php");
    exit();

}

// ==========================
// ELIMINAR CLIENTE
// ==========================

if(isset($_GET['eliminar'])){

    $id = $_GET['eliminar'];

    mysqli_query(
        $conexion,
        "DELETE FROM clientes WHERE id='$id'"
    );

    header("Location: clientes.php");
    exit();

}

// ==========================
// EDITAR CLIENTE
// ==========================

if(isset($_POST['editar'])){

    $id       = $_POST['id'];
    $nombre   = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo   = $_POST['correo'];
    $empresa  = $_POST['empresa'];
    $direccion = $_POST['direccion'];

    mysqli_query($conexion, "UPDATE clientes SET

        nombre='$nombre',
        telefono='$telefono',
        correo='$correo',
        empresa='$empresa',
        direccion='$direccion'

        WHERE id='$id'

    ");

    header("Location: clientes.php");
    exit();

}

// ==========================
// CARGAR DATOS EDITAR
// ==========================

$cliente_editar = null;

if(isset($_GET['editar'])){

    $id = $_GET['editar'];

    $res = mysqli_query(
        $conexion,
        "SELECT * FROM clientes WHERE id='$id'"
    );

    $cliente_editar = mysqli_fetch_array($res);

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>SGI BAAK - Clientes</title>

    <link rel="stylesheet" href="css/styles.css">

</head>

<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">

    <div class="topbar-left">

        <h2>Módulo de Clientes</h2>

    </div>

</div>

<div class="content">

    <h1>Clientes Registrados</h1>

    <!-- ACCIONES -->

    <div class="acciones-top">

        <!-- BUSCADOR -->

        <form method="GET" class="buscador">

            <input type="text"
            name="buscar"
            placeholder="Buscar cliente..."
            value="<?php echo $buscar; ?>">

            <button type="submit">

                Buscar

            </button>

            <a href="clientes.php"
            class="btn-limpiar">

                Limpiar

            </a>

        </form>

        <!-- BOTON NUEVO -->

        <button class="btn-nuevo"
        onclick="abrirModal()">

            + Nuevo Cliente

        </button>

    </div>

    <!-- TABLA -->

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

                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

            <?php

            if($buscar != ""){

                $sql = "SELECT * FROM clientes

                WHERE nombre LIKE '%$buscar%'
                OR empresa LIKE '%$buscar%'
                OR telefono LIKE '%$buscar%'

                ORDER BY id DESC";

            }else{

                $sql = "SELECT * FROM clientes
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

                <td>

                    <?php echo $mostrar['nombre']; ?>

                </td>

                <td>

                    <?php echo $mostrar['telefono']; ?>

                </td>

                <td>

                    <?php echo $mostrar['correo']; ?>

                </td>

                <td>

                    <?php echo $mostrar['empresa']; ?>

                </td>

                <td>

                    <?php echo $mostrar['direccion']; ?>

                </td>

                <td>

                    <a href="?editar=<?php
                    echo $mostrar['id']; ?>">

                        Editar

                    </a>

                    |

                    <a href="?eliminar=<?php
                    echo $mostrar['id']; ?>"

                    onclick="return confirm(
                    '¿Eliminar cliente?'
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

<!-- MODAL -->

<div class="modal" id="modalCliente">

    <div class="modal-contenido">

        <span class="cerrar"
        onclick="cerrarModal()">

            &times;

        </span>

        <?php if($cliente_editar){ ?>

            <h2>Editar Cliente</h2>

        <?php } else { ?>

            <h2>Nuevo Cliente</h2>

        <?php } ?>

        <?php if($cliente_editar){ ?>

        <form method="POST">

            <input type="hidden"
            name="id"
            value="<?php
            echo $cliente_editar['id'];
            ?>">

            <input type="text"
            name="nombre"
            value="<?php
            echo $cliente_editar['nombre'];
            ?>"
            required>

            <input type="text"
            name="telefono"
            value="<?php
            echo $cliente_editar['telefono'];
            ?>">

            <input type="email"
            name="correo"
            value="<?php
            echo $cliente_editar['correo'];
            ?>">

            <input type="text"
            name="empresa"
            value="<?php
            echo $cliente_editar['empresa'];
            ?>">

            <textarea name="direccion"><?php
            echo $cliente_editar['direccion'];
            ?></textarea>

            <button type="submit"
            name="editar">

                Guardar Cambios

            </button>

        </form>

        <?php } else { ?>

        <form method="POST">

            <input type="text"
            name="nombre"
            placeholder="Nombre del Cliente"
            required>

            <input type="text"
            name="telefono"
            placeholder="Teléfono">

            <input type="email"
            name="correo"
            placeholder="Correo">

            <input type="text"
            name="empresa"
            placeholder="Empresa">

            <textarea name="direccion"
            placeholder="Dirección"></textarea>

            <button type="submit"
            name="guardar">

                Guardar Cliente

            </button>

        </form>

        <?php } ?>

    </div>

</div>

<script>

function abrirModal(){

    document.getElementById(
    "modalCliente"
    ).style.display = "flex";

}

function cerrarModal(){

    document.getElementById(
    "modalCliente"
    ).style.display = "none";

}

window.onclick = function(event){

    let modal =
    document.getElementById(
    "modalCliente"
    );

    if(event.target == modal){

        modal.style.display = "none";

    }

}

</script>

<?php if($cliente_editar){ ?>

<script>

document.getElementById(
"modalCliente"
).style.display = "flex";

</script>

<?php } ?>

</body>

</html>