<?php
session_start();
include("../config/conexion.php");

// Si no hay sesión o no viene un ID, mandamos una lista vacía
if(!isset($_SESSION['usuario']) || !isset($_GET['id'])){
    echo json_encode([]);
    exit();
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

// Buscamos los productos específicos que pertenecen a esta cotización
$resultado = mysqli_query($conexion, "SELECT producto, cantidad, tipo_venta FROM detalle_cotizacion WHERE cotizacion_id='$id'");

$detalles = [];
while($row = mysqli_fetch_assoc($resultado)){
    $detalles[] = $row;
}

// Le avisamos al navegador que esto es un objeto JSON que usará JavaScript
header('Content-Type: application/json');
echo json_encode($detalles);
?>