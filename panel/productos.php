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
// GUARDAR PRODUCTO
// ==========================

if(isset($_POST['guardar'])){

    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $precio = $_POST['precio'];
    $precio_mayoreo = $_POST['precio_mayoreo'];
    $stock = $_POST['stock'];

    $sql = "INSERT INTO productos
    (nombre, categoria, precio_base, precio_mayoreo, stock)

    VALUES

    ('$nombre','$categoria','$precio','$precio_mayoreo','$stock')";

    mysqli_query($conexion, $sql);

    header("Location: productos.php");
    exit();

}

// ==========================
// ELIMINAR PRODUCTO
// ==========================

if(isset($_GET['eliminar'])){

    $id = $_GET['eliminar'];

    mysqli_query(
        $conexion,
        "DELETE FROM productos WHERE id='$id'"
    );

    header("Location: productos.php");
    exit();

}

// ==========================
// EDITAR PRODUCTO
// ==========================

if(isset($_POST['editar'])){

    $id = $_POST['id'];

    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $precio = $_POST['precio'];
    $precio_mayoreo = $_POST['precio_mayoreo'];
    $stock = $_POST['stock'];

    mysqli_query($conexion, "UPDATE productos SET

        nombre='$nombre',
        categoria='$categoria',
        precio_base='$precio',
        precio_mayoreo='$precio_mayoreo',
        stock='$stock'

        WHERE id='$id'

    ");

    header("Location: productos.php");
    exit();

}

// ==========================
// CARGAR DATOS EDITAR
// ==========================

$producto_editar = null;

if(isset($_GET['editar'])){

    $id = $_GET['editar'];

    $res = mysqli_query(
        $conexion,
        "SELECT * FROM productos WHERE id='$id'"
    );

    $producto_editar = mysqli_fetch_array($res);

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>SGI BAAK - Productos</title>

    <link rel="stylesheet" href="css/styles.css">

</head>

<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">

    <div class="topbar-left">

        <h2>Módulo de Productos</h2>

    </div>

</div>

<div class="content">

    <h1>Productos Registrados</h1>

    <!-- ACCIONES -->

    <div class="acciones-top">

        <!-- BUSCADOR -->

        <form method="GET" class="buscador">

            <input type="text"
            name="buscar"
            placeholder="Buscar producto..."
            value="<?php echo $buscar; ?>">

            <button type="submit">

                Buscar

            </button>

            <a href="productos.php"
            class="btn-limpiar">

                Limpiar

            </a>

        </form>

        <!-- BOTON NUEVO -->

        <button class="btn-nuevo"
        onclick="abrirModal()">

            + Nuevo Producto

        </button>

    </div>

    <!-- TABLA -->

    <div class="tabla-reciente">

        <h2>Lista de Productos</h2>

        <table>

            <thead>

                <tr>

                    <th>Nombre</th>

                    <th>Categoría</th>

                    <th>Precio Normal</th>

                    <th>Precio Mayoreo</th>

                    <th>Stock</th>

                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

            <?php

            if($buscar != ""){

                $sql = "SELECT * FROM productos

                WHERE nombre LIKE '%$buscar%'
                OR categoria LIKE '%$buscar%'

                ORDER BY id DESC";

            }else{

                $sql = "SELECT * FROM productos
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

                    <?php echo $mostrar['categoria']; ?>

                </td>

                <td>

                    $<?php echo number_format($mostrar['precio_base'],2); ?>

                </td>

                <td>

                    $<?php echo number_format($mostrar['precio_mayoreo'],2); ?>

                </td>

                <td>

                    <?php echo $mostrar['stock']; ?>

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
                    '¿Eliminar producto?'
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

<div class="modal" id="modalProducto">

    <div class="modal-contenido">

        <span class="cerrar"
        onclick="cerrarModal()">

            &times;

        </span>

        <?php if($producto_editar){ ?>

            <h2>Editar Producto</h2>

        <?php } else { ?>

            <h2>Nuevo Producto</h2>

        <?php } ?>

        <?php if($producto_editar){ ?>

        <form method="POST">

            <input type="hidden"
            name="id"
            value="<?php
            echo $producto_editar['id'];
            ?>">

            <input type="text"
            name="nombre"
            value="<?php
            echo $producto_editar['nombre'];
            ?>"
            required>

            <input type="text"
            name="categoria"
            value="<?php
            echo $producto_editar['categoria'];
            ?>">

            <input type="number"
            step="0.01"
            name="precio"
            value="<?php
            echo $producto_editar['precio_base'];
            ?>">

            <input type="number"
            step="0.01"
            name="precio_mayoreo"
            value="<?php
            echo $producto_editar['precio_mayoreo'];
            ?>">

            <input type="number"
            name="stock"
            value="<?php
            echo $producto_editar['stock'];
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
            placeholder="Nombre del Producto"
            required>

            <input type="text"
            name="categoria"
            placeholder="Categoría">

            <input type="number"
            step="0.01"
            name="precio"
            placeholder="Precio Normal">

            <input type="number"
            step="0.01"
            name="precio_mayoreo"
            placeholder="Precio Mayoreo">

            <input type="number"
            name="stock"
            placeholder="Stock">

            <button type="submit"
            name="guardar">

                Guardar Producto

            </button>

        </form>

        <?php } ?>

    </div>

</div>

<script>

function abrirModal(){

    document.getElementById(
    "modalProducto"
    ).style.display = "flex";

}

function cerrarModal(){

    document.getElementById(
    "modalProducto"
    ).style.display = "none";

}

window.onclick = function(event){

    let modal =
    document.getElementById(
    "modalProducto"
    );

    if(event.target == modal){

        modal.style.display = "none";

    }

}

</script>

<?php if($producto_editar){ ?>

<script>

document.getElementById(
"modalProducto"
).style.display = "flex";

</script>

<?php } ?>

</body>

</html>