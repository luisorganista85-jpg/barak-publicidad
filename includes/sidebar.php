<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <h2>SGI BAAK</h2>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php">
                <span class="icon">📊</span> Dashboard
            </a>
        </li>
        <li>
            <a href="clientes.php">
                <span class="icon">👥</span> Clientes
            </a>
        </li>
        <li>
            <a href="cotizaciones.php">
                <span class="icon">📋</span> Cotizaciones
            </a>
        </li>
        <li>
            <a href="inventario.php">
                <span class="icon">📦</span> Inventario
            </a>
        </li>
        <li>
            <a href="equipos.php">
                <span class="icon">🖥️</span> Equipos
            </a>
        </li>
        <li>
            <a href="proveedores.php">
                <span class="icon">🚚</span> Proveedores
            </a>
        </li>

        <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === "Super Administrador" || $_SESSION['rol'] === "Administrador")): ?>
            <li class="menu-separator" style="margin: 0; padding: 0; list-style: none;">
                <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 5px 0 10px 0; padding: 0;">
            </li>
            <li>
                <a href="usuarios.php">
                    <span class="icon">🔐</span> Usuarios
                </a>
            </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === "Super Administrador" || $_SESSION['rol'] === "Administrador")): ?>
            <li>
                <a href="configuracion.php">
                    <span class="icon">⚙️</span> Configuración
                </a>
            </li>
            <li>
                <a href="papelera.php?sec=clientes">
                    <span class="icon">🗑️</span> Papelera Global
                </a>
            </li>
        <?php endif; ?>

        <li class="menu-separator" style="margin: 0; padding: 0; list-style: none;">
            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 5px 0 10px 0; padding: 0;">
        </li>
        <li>
            <button id="toggleTema" onclick="cambiarTema()" style="
                background: none;
                border: none;
                color: #cbd5e1;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 15px;
                width: 100%;
                font-size: 15px;
                text-align: left;
            ">
                <span id="iconoTema">🌙</span>
                <span id="textoTema">Modo Oscuro</span>
            </button>
        </li>

        <li class="logout-item">
            <a href="#" onclick="abrirModalLogout(); return false;">
                <span class="icon">🚪</span> Cerrar Sesión
            </a>
        </li>
    </ul>
</div>

<!-- Modal Cerrar Sesión -->
<div id="modalLogout" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-icon">🚪</div>
        <h3 class="modal-title">Cerrar Sesión</h3>
        <p class="modal-message">¿Seguro que quieres salir del sistema?</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="cerrarModalLogout()">Cancelar</button>
            <a href="../logout.php" class="btn btn-danger">Sí, salir</a>
        </div>
    </div>
</div>

<script>
// Aplicar tema guardado INMEDIATAMENTE al cargar
(function() {
    const tema = localStorage.getItem('tema_sgi') || 'claro';
    if (tema === 'oscuro') {
        document.documentElement.setAttribute('data-tema', 'oscuro');
    }
})();

function cambiarTema() {
    const html = document.documentElement;
    const temaActual = html.getAttribute('data-tema') || 'claro';
    const nuevoTema = temaActual === 'claro' ? 'oscuro' : 'claro';

    html.setAttribute('data-tema', nuevoTema);
    localStorage.setItem('tema_sgi', nuevoTema);
    actualizarBoton(nuevoTema);
}

function actualizarBoton(tema) {
    const icono = document.getElementById('iconoTema');
    const texto = document.getElementById('textoTema');
    if (!icono || !texto) return;

    if (tema === 'oscuro') {
        icono.textContent = '☀️';
        texto.textContent = 'Modo Claro';
    } else {
        icono.textContent = '🌙';
        texto.textContent = 'Modo Oscuro';
    }
}

// Al cargar la página, sincronizar el botón con el tema guardado
document.addEventListener('DOMContentLoaded', function() {
    const tema = localStorage.getItem('tema_sgi') || 'claro';
    actualizarBoton(tema);
});

// ── Modal Logout ──────────────────────────────────────────
function abrirModalLogout() {
    const modal = document.getElementById('modalLogout');
    modal.style.display = 'flex';
}

function cerrarModalLogout() {
    const modal = document.getElementById('modalLogout');
    modal.style.display = 'none';
}

// Cerrar al hacer clic en el fondo del overlay
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modalLogout');
    if (e.target === modal) cerrarModalLogout();
});
</script>