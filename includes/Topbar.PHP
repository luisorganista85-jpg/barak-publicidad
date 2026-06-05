<div class="topbar">

    <div class="topbar-left">
        <h2><?php echo $titulo; ?></h2>
    </div>

    <div class="topbar-right">

        <span>
            👤
            <strong>
                <?php echo $_SESSION['usuario']; ?>
            </strong>
        </span>

        <a href="/BARAK_PUBLICIDAD/logout.php">
            Cerrar Sesión
        </a>

    </div>

</div>