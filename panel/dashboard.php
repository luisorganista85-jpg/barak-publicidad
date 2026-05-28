<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

include("../config/conexion.php");

// TOTAL CLIENTES
$query_clientes = mysqli_query($conexion, "SELECT * FROM clientes");
$total_clientes = $query_clientes ? mysqli_num_rows($query_clientes) : 0;

// TOTAL PRODUCTOS
$query_productos = mysqli_query($conexion, "SELECT * FROM productos");
$total_productos = $query_productos ? mysqli_num_rows($query_productos) : 0;

// TOTAL COTIZACIONES
$query_cotizaciones = mysqli_query($conexion, "SELECT * FROM cotizaciones");
$total_cotizaciones = $query_cotizaciones ? mysqli_num_rows($query_cotizaciones) : 0;

// TOTAL VENTAS
$ventas = mysqli_query($conexion, "SELECT SUM(total) AS total_ventas FROM cotizaciones");
$total_ventas = mysqli_fetch_array($ventas);

// VENTAS POR CANAL (REALES)
$canales = ['WhatsApp', 'Facebook', 'Empresa', 'Recomendación'];
$ventas_canal = [];
$max_canal = 1;

foreach($canales as $canal){
    $res = mysqli_fetch_array(
        mysqli_query($conexion,
            "SELECT SUM(total) as suma FROM cotizaciones WHERE canal_venta='$canal'")
    );
    $suma = floatval($res['suma'] ?? 0);
    $ventas_canal[$canal] = $suma;
    if($suma > $max_canal) $max_canal = $suma;
}

// COTIZACIONES POR ESTADO
$pendientes = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT COUNT(*) as total FROM cotizaciones WHERE estado='Pendiente'")
)['total'] ?? 0;

$cerradas = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT COUNT(*) as total FROM cotizaciones WHERE estado='Cerrada'")
)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BAAK - Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <h2>Panel Administrativo - SGI BAAK</h2>
    </div>
    <div class="topbar-right">
        <span>Bienvenido: <?php echo $_SESSION['usuario']; ?></span>
        <a href="../logout.php">Cerrar Sesión</a>
    </div>
</div>

<div class="content">

    <h1>Dashboard Principal</h1>

    <!-- CARDS -->
    <div class="cards">
        <div class="card ventas">
            <h3>Ventas del Mes</h3>
            <p>$<?php echo number_format($total_ventas['total_ventas'] ?? 0, 2); ?></p>
        </div>
        <div class="card clientes">
            <h3>Clientes</h3>
            <p><?php echo $total_clientes; ?></p>
        </div>
        <div class="card productos">
            <h3>Productos</h3>
            <p><?php echo $total_productos; ?></p>
        </div>
        <div class="card cotizaciones">
            <h3>Cotizaciones</h3>
            <p><?php echo $total_cotizaciones; ?></p>
        </div>
    </div>

    <!-- ULTIMAS COTIZACIONES -->
    <div class="tabla-reciente">
        <h2>Últimas Cotizaciones</h2>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Empresa</th>
                    <th>Producto</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $resultado = mysqli_query($conexion,
                "SELECT * FROM cotizaciones ORDER BY id DESC LIMIT 5");
            while($mostrar = mysqli_fetch_array($resultado)){
                $det = mysqli_query($conexion,
                    "SELECT producto FROM detalle_cotizacion
                    WHERE cotizacion_id='".$mostrar['id']."' LIMIT 1");
                $d = mysqli_fetch_array($det);
                $nombre_producto = $d ? $d['producto'] : '—';
            ?>
            <tr>
                <td><?php echo $mostrar['cliente']; ?></td>
                <td><?php echo $mostrar['empresa']; ?></td>
                <td><?php echo $nombre_producto; ?></td>
                <td>$<?php echo number_format($mostrar['total'], 2); ?></td>
                <td><?php echo $mostrar['estado']; ?></td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- GRAFICAS -->
    <div style="display:flex; gap:20px; margin-top:30px; flex-wrap:wrap;">

        <!-- GRAFICA VENTAS POR CANAL -->
        <div class="grafica-container" style="flex:2; min-width:300px;">
            <h2>Ventas por Canal</h2>
            <canvas id="graficaCanal" height="120"></canvas>
        </div>

        <!-- GRAFICA ESTADO COTIZACIONES -->
        <div class="grafica-container" style="flex:1; min-width:250px;">
            <h2>Estado Cotizaciones</h2>
            <canvas id="graficaEstado" height="120"></canvas>
        </div>

    </div>

</div>

<script>
// GRAFICA BARRAS - VENTAS POR CANAL
var ctxCanal = document.getElementById('graficaCanal').getContext('2d');
new Chart(ctxCanal, {
    type: 'bar',
    data: {
        labels: ['WhatsApp', 'Facebook', 'Empresa', 'Recomendación'],
        datasets: [{
            label: 'Ventas ($)',
            data: [
                <?php echo $ventas_canal['WhatsApp']; ?>,
                <?php echo $ventas_canal['Facebook']; ?>,
                <?php echo $ventas_canal['Empresa']; ?>,
                <?php echo $ventas_canal['Recomendación']; ?>
            ],
            backgroundColor: ['#25D366','#1877F2','#6f42c1','#fd7e14'],
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx){
                        return '$' + ctx.raw.toLocaleString('es-MX', {minimumFractionDigits:2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(val){
                        return '$' + val.toLocaleString('es-MX');
                    }
                }
            }
        }
    }
});

// GRAFICA DONA - ESTADO COTIZACIONES
var ctxEstado = document.getElementById('graficaEstado').getContext('2d');
new Chart(ctxEstado, {
    type: 'doughnut',
    data: {
        labels: ['Pendientes', 'Cerradas'],
        datasets: [{
            data: [<?php echo $pendientes; ?>, <?php echo $cerradas; ?>],
            backgroundColor: ['#ffc107','#28a745'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>