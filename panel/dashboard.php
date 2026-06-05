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

// VENTAS POR CANAL
$canales = ['WhatsApp', 'Facebook', 'Empresa', 'Recomendación'];
$ventas_canal = [];
foreach($canales as $canal){
    $res = mysqli_fetch_array(
        mysqli_query($conexion, "SELECT SUM(total) as suma FROM cotizaciones WHERE canal_venta='$canal'")
    );
    $ventas_canal[$canal] = floatval($res['suma'] ?? 0);
}

// COTIZACIONES POR ESTADO
$pendientes = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT COUNT(*) as total FROM cotizaciones WHERE estado='Pendiente'")
)['total'] ?? 0;

$cerradas = mysqli_fetch_array(
    mysqli_query($conexion, "SELECT COUNT(*) as total FROM cotizaciones WHERE estado='Cerrada'")
)['total'] ?? 0;

// VENTAS ULTIMOS 6 MESES
$ventas_meses = [];
$labels_meses = [];
for($i = 5; $i >= 0; $i--){
    $mes   = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $res_mes = mysqli_fetch_array(
        mysqli_query($conexion,
            "SELECT SUM(total) as suma FROM cotizaciones
             WHERE DATE_FORMAT(fecha, '%Y-%m') = '$mes' AND estado='Cerrada'")
    );
    $ventas_meses[] = floatval($res_mes['suma'] ?? 0);
    $labels_meses[] = $label;
}

// VARIACION MES ACTUAL VS MES ANTERIOR
$mes_actual   = floatval(end($ventas_meses));
$mes_anterior = floatval($ventas_meses[count($ventas_meses) - 2]);
$variacion_positiva = true;
$variacion_texto = "Sin datos previos";
if($mes_anterior > 0){
    $variacion = (($mes_actual - $mes_anterior) / $mes_anterior) * 100;
    $variacion_positiva = $variacion >= 0;
    $variacion_texto = ($variacion_positiva ? "▲ +" : "▼ ") . number_format(abs($variacion), 1) . "% vs mes anterior";
}

// TOP 3 PRODUCTOS MAS VENDIDOS
$top_productos = [];
$res_top = mysqli_query($conexion,
    "SELECT producto, SUM(cantidad) as total_vendido
     FROM detalle_cotizacion
     GROUP BY producto
     ORDER BY total_vendido DESC
     LIMIT 3");
while($tp = mysqli_fetch_array($res_top)){
    $top_productos[] = $tp;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">📊</span>
        <h2>Dashboard Principal</h2>
    </div>
</div>

<div class="content">

    <!-- KPI CARDS -->
    <div class="kpi-grid">
        <div class="kpi-card kpi-ventas">
            <div class="kpi-icon">💰</div>
            <div class="kpi-info">
                <h3>Ventas Totales</h3>
                <p>$<?php echo number_format($total_ventas['total_ventas'] ?? 0, 2); ?></p>
                <small class="<?php echo $variacion_positiva ? 'var-positiva' : 'var-negativa'; ?>">
                    <?php echo $variacion_texto; ?>
                </small>
            </div>
        </div>
        <div class="kpi-card kpi-clientes">
            <div class="kpi-icon">👥</div>
            <div class="kpi-info">
                <h3>Total Clientes</h3>
                <p><?php echo $total_clientes; ?></p>
                <small>Registrados en el sistema</small>
            </div>
        </div>
        <div class="kpi-card kpi-productos">
            <div class="kpi-icon">📦</div>
            <div class="kpi-info">
                <h3>Total Productos</h3>
                <p><?php echo $total_productos; ?></p>
                <small>En catálogo activo</small>
            </div>
        </div>
        <div class="kpi-card kpi-cots">
            <div class="kpi-icon">📄</div>
            <div class="kpi-info">
                <h3>Cotizaciones</h3>
                <p><?php echo $total_cotizaciones; ?></p>
                <small><?php echo $pendientes; ?> pendientes · <?php echo $cerradas; ?> cerradas</small>
            </div>
        </div>
    </div>

    <!-- GRAFICA LINEA: VENTAS 6 MESES -->
    <div class="chart-full">
        <h2>📈 Tendencia de Ventas — Últimos 6 Meses</h2>
        <canvas id="graficaMeses" height="80"></canvas>
    </div>

    <!-- FILA 3 GRAFICAS -->
    <div class="chart-row">

        <div class="chart-box">
            <h2>📊 Ventas por Canal</h2>
            <canvas id="graficaCanal" height="200"></canvas>
        </div>

        <div class="chart-box">
            <h2>🔵 Estado de Cotizaciones</h2>
            <canvas id="graficaEstado" height="200"></canvas>
        </div>

        <div class="chart-box">
            <h2>🏆 Top 3 Productos Más Vendidos</h2>
            <?php if(count($top_productos) > 0): ?>
                <?php foreach($top_productos as $idx => $tp): ?>
                <div class="top-prod-item">
                    <div class="top-prod-rank rank-<?php echo $idx+1; ?>"><?php echo $idx+1; ?></div>
                    <div class="top-prod-nombre"><?php echo htmlspecialchars($tp['producto']); ?></div>
                    <div class="top-prod-cant"><?php echo $tp['total_vendido']; ?> uds.</div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="sin-datos">Sin datos de ventas aún.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- TABLA ULTIMAS 5 COTIZACIONES -->
    <div class="tabla-reciente">
        <div class="tabla-header">
            <h2>⏱️ Últimas 5 Cotizaciones Generadas</h2>
        </div>
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
            $resultado = mysqli_query($conexion, "SELECT * FROM cotizaciones ORDER BY id DESC LIMIT 5");
            while($mostrar = mysqli_fetch_array($resultado)){
                $det = mysqli_query($conexion,
                    "SELECT producto FROM detalle_cotizacion WHERE cotizacion_id='".$mostrar['id']."' LIMIT 1");
                $d = mysqli_fetch_array($det);
                $nombre_producto = $d ? $d['producto'] : '—';
            ?>
            <tr>
                <td><strong><?php echo $mostrar['cliente']; ?></strong></td>
                <td><?php echo $mostrar['empresa'] ?: 'Particular'; ?></td>
                <td><?php echo $nombre_producto; ?></td>
                <td>$<?php echo number_format($mostrar['total'], 2); ?></td>
                <td>
                    <span class="badge <?php echo ($mostrar['estado'] == 'Cerrada') ? 'badge-disponible' : 'badge-bajo'; ?>">
                        <?php echo $mostrar['estado']; ?>
                    </span>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- TABLA PENDIENTES -->
    <div class="tabla-reciente">
        <div class="tabla-header clear-orange">
            <h2>⚠️ 5 Cotizaciones Pendientes de Seguimiento</h2>
        </div>
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
            $resultado_pendientes = mysqli_query($conexion,
                "SELECT * FROM cotizaciones WHERE estado='Pendiente' ORDER BY id DESC LIMIT 5");
            if(mysqli_num_rows($resultado_pendientes) == 0){
                echo "<tr><td colspan='5' class='sin-datos'>No hay cotizaciones pendientes de atención.</td></tr>";
            }
            while($mostrar_p = mysqli_fetch_array($resultado_pendientes)){
                $det_p = mysqli_query($conexion,
                    "SELECT producto FROM detalle_cotizacion WHERE cotizacion_id='".$mostrar_p['id']."' LIMIT 1");
                $d_p = mysqli_fetch_array($det_p);
                $nombre_producto_p = $d_p ? $d_p['producto'] : '—';
            ?>
            <tr>
                <td><strong><?php echo $mostrar_p['cliente']; ?></strong></td>
                <td><?php echo $mostrar_p['empresa'] ?: 'Particular'; ?></td>
                <td><?php echo $nombre_producto_p; ?></td>
                <td>$<?php echo number_format($mostrar_p['total'], 2); ?></td>
                <td><span class="badge badge-bajo"><?php echo $mostrar_p['estado']; ?></span></td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

</div><!-- /.content -->

<div class="footer-panel">
    <p>
        <strong>SGI BARAK</strong> — Sistema de Gestión Integral &nbsp;|&nbsp;
        Versión 1.0 &nbsp;|&nbsp;
        &copy; <?php echo date('Y'); ?> Barak Publicidad & Marketing
    </p>
</div>

<script>
// GRAFICA LINEA: VENTAS 6 MESES
var ctxMeses = document.getElementById('graficaMeses').getContext('2d');
new Chart(ctxMeses, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels_meses); ?>,
        datasets: [{
            label: 'Ventas Cerradas ($)',
            data: <?php echo json_encode($ventas_meses); ?>,
            borderColor: '#6f42c1',
            backgroundColor: 'rgba(111,66,193,.12)',
            borderWidth: 3,
            pointBackgroundColor: '#6f42c1',
            pointRadius: 6,
            pointHoverRadius: 9,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx){
                        return ' $' + ctx.raw.toLocaleString('es-MX', {minimumFractionDigits:2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: function(val){ return '$' + val.toLocaleString('es-MX'); } },
                grid: { color: 'rgba(128,128,128,.1)' }
            },
            x: { grid: { color: 'rgba(128,128,128,.1)' } }
        }
    }
});

// GRAFICA BARRAS: VENTAS POR CANAL
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
                        return ' $' + ctx.raw.toLocaleString('es-MX', {minimumFractionDigits:2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: function(val){ return '$' + val.toLocaleString('es-MX'); } },
                grid: { color: 'rgba(128,128,128,.1)' }
            },
            x: { grid: { display: false } }
        }
    }
});

// GRAFICA DONA: ESTADO COTIZACIONES
var ctxEstado = document.getElementById('graficaEstado').getContext('2d');
new Chart(ctxEstado, {
    type: 'doughnut',
    data: {
        labels: ['Pendientes', 'Cerradas'],
        datasets: [{
            data: [<?php echo $pendientes; ?>, <?php echo $cerradas; ?>],
            backgroundColor: ['#ffc107','#20c997'],
            borderWidth: 3,
            borderColor: 'rgba(0,0,0,.1)'
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, font: { size: 13 } } }
        }
    }
});
</script>

</body>
</html>