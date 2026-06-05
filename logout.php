<?php
session_start();
session_unset();
session_destroy();

header("Location: /BARAK_PUBLICIDAD/login.php");
exit();
?>
