<?php
$rol = $_SESSION['rol'] ?? 'Empleado';
?>

<!-- BOTON MENU -->
<div class="menu-toggle" onclick="toggleSidebar()">☰</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <h2>SGI BAAK</h2>

    <ul>


        <li>
            <a href="dashboard.php">
                📊 Dashboard
            </a>
        </li>

        <li>
            <a href="clientes.php">
                👥 Clientes
            </a>
        </li>

        <li>
            <a href="cotizaciones.php">
                📋 Cotizaciones
            </a>
        </li>

        <li>
            <a href="inventario.php">
                📦 Inventario
            </a>
        </li>

        <li>
            <a href="productos.php">
                🛍️ Productos
            </a>
        </li>

        <li>
            <a href="equipos.php">
                🖥️ Equipos
            </a>
        </li>

        <li>
            <a href="proveedores.php">
                🚚 Proveedores
            </a>
        </li>

        <?php if($rol == 'Administrador'){ ?>

        <li>
            <a href="usuarios.php">
                👤 Usuarios
            </a>
        </li>

        <li>
            <a href="configuracion.php">
                ⚙️ Configuración
            </a>
        </li>

        <?php } ?>

        <li>
            <a href="/BARAK_PUBLICIDAD/logout.php">
                🚪 Cerrar Sesión
            </a>
        </li>

    </ul>

</div>

<script>
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("active");
}
</script>