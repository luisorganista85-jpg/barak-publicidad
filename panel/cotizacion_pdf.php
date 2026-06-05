<?php

require_once __DIR__ . '/../dompdf/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include("../config/conexion.php");

if(!isset($_GET['id'])){
    die("Cotización no encontrada");
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

$cotizacion = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT * FROM cotizaciones WHERE id='$id'")
);

$detalle = mysqli_query($conexion,
    "SELECT * FROM detalle_cotizacion WHERE cotizacion_id='$id'"
);

/*
|--------------------------------------------------------------------------
| LOGO BASE64
|--------------------------------------------------------------------------
*/

$logo = "C:/xampp/htdocs/BARAK_PUBLICIDAD/img/logo.png";
$logoBase64 = "";

if(file_exists($logo)){
    $tipo = pathinfo($logo, PATHINFO_EXTENSION);
    $data = file_get_contents($logo);
    $logoBase64 = 'data:image/' . $tipo . ';base64,' . base64_encode($data);
}

/*
|--------------------------------------------------------------------------
| HTML PDF
|--------------------------------------------------------------------------
*/

$html = '
<html>
<head>
<style>

*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{
    font-family: Arial, Helvetica, sans-serif;
    color:#1f2937;
    padding:30px;
    background:white;
}

/* HEADER */
.header{
    padding-bottom:15px;
    margin-bottom:20px;
    border-bottom:4px solid #1e3a8a;
}

.header-table{
    width:100%;
}

.logo{
    width:85px;
}

.empresa-nombre{
    font-size:26px;
    font-weight:bold;
    color:#1e3a8a;
    letter-spacing:2px;
}

.empresa-sub{
    font-size:12px;
    color:#6b7280;
    margin-top:4px;
}

.cotizacion-num{
    display:inline-block;
    margin-top:10px;
    background:#1e3a8a;
    color:white;
    padding:6px 16px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    letter-spacing:1px;
}

.info-right{
    text-align:right;
    font-size:13px;
    line-height:1.9;
}

.info-label{
    color:#6b7280;
    font-size:11px;
    text-transform:uppercase;
}

.info-value{
    font-weight:bold;
    color:#111827;
}

/* TABLA PRODUCTOS */
table.productos{
    width:100%;
    border-collapse:collapse;
    margin-top:25px;
}

table.productos th{
    background:#1e3a8a;
    color:white;
    padding:12px;
    font-size:12px;
    text-align:left;
    text-transform:uppercase;
}

table.productos td{
    padding:11px;
    border-bottom:1px solid #e5e7eb;
    font-size:13px;
}

table.productos tr:nth-child(even){
    background:#f9fafb;
}

/* TOTAL */
.total-box{
    margin-top:25px;
    text-align:right;
    padding:18px;
    background:#f0fdf4;
    border:1px solid #bbf7d0;
    border-right:6px solid #16a34a;
    border-radius:8px;
}

.total-label{
    font-size:12px;
    text-transform:uppercase;
    color:#374151;
}

.total-monto{
    font-size:32px;
    font-weight:bold;
    color:#15803d;
    margin-top:5px;
}

/* FOOTER */
.footer{
    margin-top:35px;
    text-align:center;
    font-size:11px;
    color:#9ca3af;
    border-top:1px solid #e5e7eb;
    padding-top:15px;
}

</style>
</head>

<body>

<div class="header">
<table class="header-table">
<tr>

<td width="65%">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="border:none; width: 105px; padding-right: 30px; vertical-align: middle;">
                <img src="'.$logoBase64.'" class="logo" style="width: 85px; display: block;">
            </td>
            <td style="border:none; vertical-align: middle;">
                <div class="empresa-nombre">BARAK PUBLICIDAD</div>
                <div class="empresa-sub">Publicidad & Marketing</div>
                <span class="cotizacion-num">COTIZACIÓN #'.$id.'</span>
            </td>
        </tr>
    </table>
</td>

<td width="35%" class="info-right" style="vertical-align: middle;">
    <span class="info-label">Cliente:</span>
    <span class="info-value">'.$cotizacion['cliente'].'</span>
    <br>
    <span class="info-label">Empresa:</span>
    <span class="info-value">'.$cotizacion['empresa'].'</span>
    <br>
    <span class="info-label">Canal:</span>
    <span class="info-value">'.$cotizacion['canal_venta'].'</span>
    <br>
    <span class="info-label">Fecha:</span>
    <span class="info-value">'.$cotizacion['fecha'].'</span>
    <br>
    <span class="info-label">Vendedor:</span>
    <span class="info-value">'.$cotizacion['usuario'].'</span>
    <br>
    <span class="info-label">Estado:</span>
    <span class="info-value">'.$cotizacion['estado'].'</span>
</td>

</tr>
</table>
</div>

<table class="productos">
<tr>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Tipo</th>
    <th>Precio Unit.</th>
    <th>Subtotal</th>
</tr>
';

while($d = mysqli_fetch_array($detalle)){
    $html .= '
    <tr>
        <td>'.$d['producto'].'</td>
        <td>'.$d['cantidad'].'</td>
        <td>'.$d['tipo_venta'].'</td>
        <td>$'.number_format($d['precio'],2).'</td>
        <td>$'.number_format($d['subtotal'],2).'</td>
    </tr>
    ';
}

$html .= '
</table>

<div class="total-box">
    <div class="total-label">Total a Pagar</div>
    <div class="total-monto">$'.number_format($cotizacion['total'],2).'</div>
</div>

<div class="footer">
    BARAK PUBLICIDAD & MARKETING • Gracias por su preferencia
</div>

</body>
</html>
';

/*
|--------------------------------------------------------------------------
| DOMPDF
|--------------------------------------------------------------------------
*/
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream(
    "Cotizacion_".$id.".pdf",
    array("Attachment" => false)
);

?>