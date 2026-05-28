<?php

$host = "localhost";
$usuario = "root";
$password = "";
$bd = "sgi_baak";

$conexion = mysqli_connect($host, $usuario, $password, $bd);

if(!$conexion){
    die("Error de conexión");
}

?>