<?php
session_start();
include("config/conexion.php");

// Capturar cualquier error de redirección o intento fallido de forma genérica
if(isset($_GET['error'])){
    $error = "Usuario o contraseña incorrectos";
}

if(isset($_POST['ingresar'])){

    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    // Filtramos estrictamente por usuarios activos (activo = 1) basado en tu esquema tinyint
    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND activo = 1";
    $stmt = $conexion->prepare($sql);

    if($stmt){

        $stmt->bind_param("s", $usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if($resultado->num_rows > 0){

            $fila = $resultado->fetch_assoc();

            // Verificación segura del hash de la contraseña
            if(password_verify($password, $fila['password'])){

                $_SESSION['usuario'] = $fila['usuario'];
                $_SESSION['rol']     = $fila['rol'];
                $_SESSION['nombre']  = $fila['nombre'];

                header("Location: panel/dashboard.php");
                exit();

            } else {
                $error = "Usuario o contraseña incorrectos";
            }

        } else {
            // Si el usuario no existe O está inactivo (activo = 0), cae aquí de forma idéntica
            $error = "Usuario o contraseña incorrectos";
        }

        $stmt->close();

    } else {
        $error = "Error al conectar con la base de datos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SGI BARAK</title>
    <link rel="stylesheet" href="panel/css/styles.css">
</head>
<body class="login-page">

<div class="login-wrapper">

    <div class="login-left">
        <div class="icon">🖨️</div>
        <h1>SGI BARAK</h1>
        <p>Sistema de Gestión Integral<br>Bienvenido al panel administrativo</p>
    </div>

    <div class="login-right">

        <h2>Iniciar Sesión</h2>
        <p class="subtitle">Ingresa tus credenciales para continuar</p>

        <?php if(isset($error)){ ?>
            <div class="error-msg" style="background-color: #721c24; color: #f8d7da; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; text-align: center; font-size: 14px; font-weight: bold;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php } ?>

        <form method="POST" style="width:100%">

            <div class="input-group">
                <label>Usuario</label>
                <input type="text" name="usuario" placeholder="Escribe tu usuario" required>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Escribe tu contraseña" required>
            </div>

            <button type="submit" name="ingresar" class="btn-login">
                Ingresar →
            </button>

        </form>

    </div>

</div>

</body>
</html>