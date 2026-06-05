<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

if(!in_array($_SESSION['rol'], ["Administrador", "Super Administrador"])){
    header("Location: dashboard.php");
    exit();
}

include("../config/conexion.php");

$mensaje = "";
$config_id = 1;

$check_id = mysqli_query($conexion, "SELECT id FROM configuracion WHERE id = $config_id");
if (mysqli_num_rows($check_id) == 0) {
    mysqli_query($conexion, "INSERT INTO configuracion (id, whatsapp, correo, horario, facebook) 
    VALUES ($config_id, '5657857355', 'barakimpresosymarketing@gmail.com', '10am a 6pm', 'https://www.facebook.com/')");
}

if(isset($_POST['guardar'])){
    $whatsapp = mysqli_real_escape_string($conexion, preg_replace('/[^0-9]/', '', trim($_POST['whatsapp'])));
    $correo   = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $horario  = mysqli_real_escape_string($conexion, trim($_POST['horario']));
    $facebook = mysqli_real_escape_string($conexion, trim(str_replace(['"',"'"], '', $_POST['facebook'])));

    if(mysqli_query($conexion, "UPDATE configuracion SET whatsapp='$whatsapp', correo='$correo', horario='$horario', facebook='$facebook' WHERE id=$config_id")) {
        $mensaje = "exito";
    } else {
        $mensaje = "error";
    }
}

$resultado = mysqli_query($conexion, "SELECT * FROM configuracion WHERE id=$config_id");
$config    = mysqli_fetch_array($resultado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BARAK - Configuración</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <span style="font-size:24px;">⚙️</span>
        <h2>Módulo de Configuración del Sistema</h2>
    </div>
</div>

<div class="content">

    <?php if($mensaje == "exito"){ ?>
        <div class="alert-exito">✅ ¡Configuración guardada exitosamente! Los cambios ya están en vivo.</div>
    <?php } elseif($mensaje == "error"){ ?>
        <div class="alert-error">❌ Error al guardar. Intenta de nuevo.</div>
    <?php } ?>

    <!-- TARJETAS RESUMEN -->
    <div class="cards">
        <div class="card clientes">
            <h3>WhatsApp Activo</h3>
            <p><?php echo htmlspecialchars($config['whatsapp'] ?? 'Sin asignar'); ?></p>
        </div>
        <div class="card ventas">
            <h3>Correo de Contacto</h3>
            <p style="font-size:16px;"><?php echo htmlspecialchars($config['correo'] ?? 'Sin asignar'); ?></p>
        </div>
        <div class="card productos">
            <h3>Horario Configurado</h3>
            <p style="font-size:20px;"><?php echo htmlspecialchars($config['horario'] ?? 'Sin asignar'); ?></p>
        </div>
    </div>

    <!-- FORMULARIO -->
    <div class="formulario" style="max-width:700px; margin-top:30px;">
        <h2>🛠️ Datos de Contacto y Redes</h2>
        <form method="POST" autocomplete="off">

            <div class="config-campo">
                <label class="config-label">📊 WhatsApp</label>
                <input type="text" name="whatsapp"
                    placeholder="Ej: 5565785355 (solo números)"
                    value="<?php echo htmlspecialchars($config['whatsapp'] ?? ''); ?>" required>
            </div>

            <div class="config-campo">
                <label class="config-label">📧 Correo Electrónico</label>
                <input type="email" name="correo"
                    placeholder="Ej: contacto@empresa.com"
                    value="<?php echo htmlspecialchars($config['correo'] ?? ''); ?>" required>
            </div>

            <div class="config-campo">
                <label class="config-label">🕐 Horario de Atención</label>
                <input type="text" name="horario"
                    placeholder="Ej: Lunes a Viernes 9am - 6pm"
                    value="<?php echo htmlspecialchars($config['horario'] ?? ''); ?>" required>
            </div>

            <div class="config-campo">
                <label class="config-label">📘 Facebook</label>
                <input type="text" name="facebook"
                    placeholder="Ej: https://facebook.com/tupagina"
                    value="<?php echo htmlspecialchars($config['facebook'] ?? ''); ?>">
            </div>

            <button type="submit" name="guardar">
                💾 Guardar Configuración
            </button>

        </form>
    </div>

</div>

<div class="footer-panel">
    <p>
        <strong>SGI BARAK</strong> — Sistema de Gestión Integral &nbsp;|&nbsp;
        &copy; <?php echo date('Y'); ?> Barak Publicidad
    </p>
</div>

</body>
</html>