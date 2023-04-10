<?php
if (!isset($_SESSION['id']) AND (!isset($_SESSION['name']))) {
    $_SESSION['msg'] = "<p style='color: #ff0000;text-align: center;font-weight: bold;'>Para acessar o sistema você precisa estar Logado!</p>";
    header("Location: ../login/index.php");
}
?>