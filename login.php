<?php

session_start();
include("config/conexion.php");

if(isset($_POST['ingresar'])){

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE usuario=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($fila = mysqli_fetch_array($resultado)){

        if(password_verify($password, $fila['password'])){

            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['rol'] = $fila['rol'];
            $_SESSION['nombre'] = $fila['nombre'];

            header("Location: panel/dashboard.php");
            exit();

        }else{
            $error = "Usuario o contraseña incorrectos";
        }

    }else{
        $error = "Usuario o contraseña incorrectos";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SGI BAAK</title>
    <link rel="stylesheet" href="panel/css/styles.css">
</head>
<body class="login-page">

<div class="login-wrapper">

    <div class="login-left">
        <div class="icon">🖨️</div>
        <h1>SGI BAAK</h1>
        <p>Sistema de Gestión Integral<br>Bienvenido al panel administrativo</p>
    </div>

    <div class="login-right">

        <h2>Iniciar Sesión</h2>
        <p class="subtitle">Ingresa tus credenciales para continuar</p>

        <?php if(isset($error)){ ?>
            <div class="error-msg">⚠️ <?php echo $error; ?></div>
        <?php } ?>

        <form method="POST" style="width:100%">

            <div class="input-group">
                <label>Usuario</label>
                <input type="text" name="usuario"
                placeholder="Escribe tu usuario" required>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password"
                placeholder="Escribe tu contraseña" required>
            </div>

            <button type="submit" name="ingresar" class="btn-login">
                Ingresar →
            </button>

        </form>

    </div>

</div>

</body>
</html>