<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

// AJUSTAR STOCK
if(isset($_POST['ajustar'])){
    $id    = $_POST['id'];
    $stock = intval($_POST['stock']);
    mysqli_query($conexion, "UPDATE productos SET stock='$stock' WHERE id='$id'");
    header("Location: inventario.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BAAK - Inventario</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-disponible {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-bajo {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-agotado {
            background: #fee2e2;
            color: #991b1b;
        }
        .btn-ajustar {
            background: #343a40;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-ajustar:hover { background: #23272b; }
        .input-stock {
            width: 70px;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            text-align: center;
        }
    </style>
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <h2>Inventario General</h2>
    </div>
</div>

<div class="content">

    <h1>Inventario General</h1>

    <!-- ACCIONES -->
    <div class="acciones-top">
        <form method="GET" class="buscador">
            <input type="text" name="buscar"
            placeholder="Buscar producto..."
            value="<?php echo $buscar; ?>">
            <button type="submit">Buscar</button>
            <a href="inventario.php" class="btn-limpiar">Limpiar</a>
        </form>
    </div>

    <!-- TABLA -->
    <div class="tabla-reciente">
        <h2>Productos Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio Normal</th>
                    <th>Precio Mayoreo</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Ajustar Stock</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($buscar != ""){
                $sql = "SELECT * FROM productos
                WHERE nombre LIKE '%$buscar%'
                OR categoria LIKE '%$buscar%'
                ORDER BY stock ASC";
            }else{
                $sql = "SELECT * FROM productos ORDER BY stock ASC";
            }

            $resultado = mysqli_query($conexion, $sql);
            while($mostrar = mysqli_fetch_array($resultado)){

                if($mostrar['stock'] <= 0){
                    $estado = "Agotado";
                    $badge  = "badge-agotado";
                }elseif($mostrar['stock'] <= 5){
                    $estado = "Stock Bajo";
                    $badge  = "badge-bajo";
                }else{
                    $estado = "Disponible";
                    $badge  = "badge-disponible";
                }
            ?>
            <tr>
                <td><?php echo $mostrar['nombre']; ?></td>
                <td><?php echo $mostrar['categoria']; ?></td>
                <td>$<?php echo number_format($mostrar['precio_base'], 2); ?></td>
                <td>$<?php echo number_format($mostrar['precio_mayoreo'], 2); ?></td>
                <td><strong><?php echo $mostrar['stock']; ?></strong></td>
                <td><span class="badge <?php echo $badge; ?>"><?php echo $estado; ?></span></td>
                <td>
                    <form method="POST" style="display:flex; gap:6px; align-items:center;">
                        <input type="hidden" name="id" value="<?php echo $mostrar['id']; ?>">
                        <input type="number" name="stock" class="input-stock"
                        value="<?php echo $mostrar['stock']; ?>" min="0">
                        <button type="submit" name="ajustar" class="btn-ajustar">
                            Actualizar
                        </button>
                    </form>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>