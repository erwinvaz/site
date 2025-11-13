<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require($_SERVER["DOCUMENT_ROOT"] . "/lib/PHPMailer/src/Exception.php");
require($_SERVER["DOCUMENT_ROOT"] . "/lib/PHPMailer/src/PHPMailer.php");
require($_SERVER["DOCUMENT_ROOT"] . "/lib/PHPMailer/src/SMTP.php");
include($_SERVER["DOCUMENT_ROOT"] . "/debug/Debug.class.php");
include($_SERVER["DOCUMENT_ROOT"] . "/PDOConnectionFactory/PDOConnectionFactory.class.php");
include($_SERVER["DOCUMENT_ROOT"] . "/lib/Traducao.class.php");
include($_SERVER["DOCUMENT_ROOT"] . "/log/DAO/LogDAO.class.php");
include($_SERVER["DOCUMENT_ROOT"] . "/lib/guestip.php");

$captureIp = new GuestIp();
$ip_user = $captureIp->getIp();

$email = $_POST['email'];
$name = $_POST['name'];
$message = $_POST['message'];
$emailDestino = "e.rom.br@gmail.com";
$nome = "Rommel Vaz";

$subject = 'Comentário na página da Trevo Tecnologia.';


$body = "
	<html>
	<head>
	<title>Trevo Tecnologia - Comentário</title>
	</head>
	<body>
	<p align='center'><img src='https://www.talento.dev.br/images/mail.png' width='200' heigth='220' /></p>
	<table align='center' border='1'>
	 <tr>
	  <th>Nome</th>
	 </tr>
	 <tr>
	  <td>" . $name . "</td>
	 </tr>
	  <tr>
	  <th>Email</th>
	 </tr>
	 <tr>
	  <td>" . $email . "</td>
	 </tr>
	  <tr>
	  <th>Comentário</th>
	 </tr>
	 <tr>
	  <td>" . $message . "</td>
	 </tr>
	</table>
	</body>
	</html>
	";

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 2; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
$mail->Host = 'smtp-relay.brevo.com';                  // Specify main and backup SMTP servers
$mail->Port = 587;                                    // TCP port to connect to
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->SMTPAuth = true;                               // Enable SMTP authentication
//$mail->isMail();   	// Set mailer to use SMTP
$mail->Username = 'admin@talento.dev.br';                 // SMTP username
$mail->Password = '';                           // SMTP password
$mail->CharSet = 'UTF-8';
//$mail->AddEmbeddedImage("photo.jpg", "my-attach", "photo.jpg");
$mail->setFrom('admin@talento.dev.br', 'Admin');
$mail->FromName = 'Pocket Safe'; // Nome de quem envia o email
$mail->AddAddress($emailDestino, $nome); // Email e nome de quem receberá //Responder
$mail->WordWrap = 50; // Definir quebra de linha
$mail->IsHTML = true; // Enviar como HTML
$mail->Subject = $subject; // Assunto
$mail->Body = '<br/>' . $body . '<br/>'; //Corpo da mensagem caso seja HTML
$mail->AltBody = "$body"; //PlainText, para caso quem receber o email não aceite o corpo HTML
$mail->SMTPOptions = array(
	'ssl' => array(
		'verify_peer' => false,
		'verify_peer_name' => false,
		'allow_self_signed' => true
	)
);
if (!$mail->Send()) {

	$mensagem = $mail->ErrorInfo . Traducao::t(' contate o administrador do sistema pelo email admin@trevotecnologia.com');
	echo $mensagem;
	die();
} else {
	echo "OK";
	die();
}
