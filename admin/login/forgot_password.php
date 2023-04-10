<?php
session_start();
ob_start();
include_once '../include/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../lib/vendor/autoload.php';
$mail = new PHPMailer(true);

include_once '../common/header.php';
?>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="../login/index.php"><b>Imob</b>System</a>
  </div>
  <!-- /.login-logo -->

<?php

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if(!empty($dados['recovery'])) {
  //var_dump($dados);

  $query_user = "SELECT id, name, email FROM users WHERE email = :email LIMIT 1";
  $result_user = $conn->prepare($query_user);
  $result_user->bindParam(':email', $dados['email'], PDO::PARAM_STR);
  $result_user->execute();

  if(($result_user) AND ($result_user->rowCount() !=0)) {
    $row_user = $result_user->fetch(PDO::FETCH_ASSOC);
    $recovery_key = password_hash($row_user['id'], PASSWORD_DEFAULT);
    //$data_agora = date('Y-m-d H:i:s');
    //echo $data_agora;
    $timezone = new DateTimeZone('America/Sao_Paulo');
    $data_agora = new DateTime('now', $timezone);
    $data_agora = $data_agora->format('Y-m-d H:i:s');
    
    $query_update_user = "UPDATE users 
                          SET remember_token =:recovery_password,
                          updated_at =:updated_at
                          WHERE id =:id
                          LIMIT 1";
    $result_update_user = $conn->prepare($query_update_user);
    $result_update_user->bindParam(':recovery_password', $recovery_key, PDO::PARAM_STR);
    $result_update_user->bindParam(':id', $row_user['id'], PDO::PARAM_INT);
    $result_update_user->bindParam(':updated_at', $data_agora, PDO::PARAM_STR);

    if ($result_update_user->execute()) {

      $link = "http://localhost/imobsystem.tk/admin/login/update_password.php?key=$recovery_key";

      try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'mail.renatoangelo.com.br';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'send@renatoangelo.com.br';
        $mail->Password   = '@Send1234*';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('send@renatoangelo.com.br', 'Renato Angelo');
        $mail->addAddress($row_user['email'], $row_user['name']);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Recuperação de Senha ImobSystem';
        $mail->Body    = 'Prezado (a) ' . $row_user['name'] . '.<br><Br>
        Você solicitou alteração de senha.<br><br>
        Para continuar o processo de recuperação de senha, clique no link abaixo ou cole o endereço no seu navegador: <br><Br><a href="' . $link . '">' . $link . '</a>';
        $mail->AltBody = 'acesse: ' . $link;

        $mail->send();

        $_SESSION['msg'] = "<p style='color: #32c330;text-align: center;font-weight: bold;'>Abra sua caixa de entrada com as instruções</p>";
        header("Location: index.php");

      }catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
      

    } else {
      $msg_output = "<p style='color: #ff0000;text-align: center;font-weight: bold;'>ERRO: Tente Novamente!</p>"; 
    }

    //*/


    
  } else {
    $msg_output = "<p style='color: #32c330;text-align: center;font-weight: bold;'>Caso o email seja encontrado, você receberá instruções em sua caixa de entrada</p>";
  }

}

?>

  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Insira seu email para recuperar sua senha.</p>

      <?php
        if (isset($msg_output)) {
          echo $msg_output;
        }
      ?>
      <form action="" method="post">
      
      <?php 
      $email = "";
      if(isset($dados['email'])) { 
        $email = $dados['email'];
      }
      ?>
        <div class="input-group mb-3">
          <input type="email" name="email" value="<?php echo $email ?>" class="form-control" placeholder="e-mail">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block" value="recovery" name="recovery">Solicitar Nova Senha</button>
          </div>
          <!-- /.col -->
          <div class="col-12">
            <div class="icheck-primary">
              <label for="remember">
                Lembrou a Senha? <a href="../login/index.php">Faça seu Login</a>
              </label>
            </div>
          </div>
          <!-- /.col -->
        </div>
      </form>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<?php
include("../common/header.php");
?>