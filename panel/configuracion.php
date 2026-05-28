<?php

session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: /BARAK_PUBLICIDAD/login.php");
    exit();
}

if($_SESSION['rol'] != "Administrador"){
    header("Location: dashboard.php");
    exit();
}

include("../config/conexion.php");

$mensaje = "";

// GUARDAR
if(isset($_POST['guardar'])){
    $whatsapp = $_POST['whatsapp'];
    $correo   = $_POST['correo'];
    $horario  = $_POST['horario'];
    $facebook = $_POST['facebook'];

    $result = mysqli_query($conexion, "UPDATE configuracion SET
        whatsapp='$whatsapp',
        correo='$correo',
        horario='$horario',
        facebook='$facebook'
        WHERE id='1'");

    if($result) $mensaje = "exito";
    else $mensaje = "error";
}

// CARGAR DATOS
$resultado = mysqli_query($conexion, "SELECT * FROM configuracion WHERE id='1'");
$config    = mysqli_fetch_array($resultado);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGI BAAK - Configuración</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include("../includes/sidebar.php"); ?>

<div class="topbar">
    <div class="topbar-left">
        <h2>⚙️ Configuración del Sistema</h2>
    </div>
</div>

<div class="content">

    <h1>⚙️ Configuración General</h1>

    <?php if($mensaje == "exito"){ ?>
    <div class="alert-exito">✅ Configuración guardada correctamente</div>
    <?php } elseif($mensaje == "error"){ ?>
    <div class="alert-error">❌ Error al guardar. Intenta de nuevo.</div>
    <?php } ?>

    <!-- FORMULARIO -->
    <div class="formulario config-form">
        <h2>Datos de Contacto</h2>
        <form method="POST">

            <div class="config-campo">
                <label class="config-label">📱 WhatsApp</label>
                <input type="text" name="whatsapp"
                placeholder="Ej: 5512345678"
                value="<?php echo $config['whatsapp']; ?>">
            </div>

            <div class="config-campo">
                <label class="config-label">📧 Correo Electrónico</label>
                <input type="email" name="correo"
                placeholder="Ej: contacto@empresa.com"
                value="<?php echo $config['correo']; ?>">
            </div>

            <div class="config-campo">
                <label class="config-label">🕐 Horario de Atención</label>
                <input type="text" name="horario"
                placeholder="Ej: Lunes a Viernes 9am - 6pm"
                value="<?php echo $config['horario']; ?>">
            </div>

            <div class="config-campo">
                <label class="config-label">📘 Facebook</label>
                <input type="text" name="facebook"
                placeholder="Ej: https://facebook.com/tupagina"
                value="<?php echo $config['facebook']; ?>">
            </div>

            <button type="submit" name="guardar">
                💾 Guardar Configuración
            </button>

        </form>
    </div>

    <!-- VISTA PREVIA -->
    <div class="tabla-reciente config-preview">
        <h2>Vista Previa</h2>
        <table>
            <tbody>
                <tr>
                    <td><strong>📱 WhatsApp</strong></td>
                    <td><?php echo $config['whatsapp'] ?: '—'; ?></td>
                </tr>
                <tr>
                    <td><strong>📧 Correo</strong></td>
                    <td><?php echo $config['correo'] ?: '—'; ?></td>
                </tr>
                <tr>
                    <td><strong>🕐 Horario</strong></td>
                    <td><?php echo $config['horario'] ?: '—'; ?></td>
                </tr>
                <tr>
                    <td><strong>📘 Facebook</strong></td>
                    <td>
                        <?php if($config['facebook']){ ?>
                        <a href="<?php echo $config['facebook']; ?>" target="_blank">
                            <?php echo $config['facebook']; ?>
                        </a>
                        <?php } else { echo '—'; } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>