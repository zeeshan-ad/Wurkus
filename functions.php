 <?php
 use PHPMailer\PHPMailer\PHPMailer;
  function sendLink(){
      global $email,$activate,$first_name;
      $url ="https://wurkus.com/verify.php?email=$email&activate=$activate";
        require 'vendor/autoload.php';
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'mx1.hostinger.in';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'connect@wurkus.com';
        $mail->Password = 'Wurk@959us';
        $mail->setFrom('connect@wurkus.com', 'Wurkus');
        $mail->addAddress($email, $first_name);
        $mail->Subject = "Wurkus Account Verification Link";
        $mail-> Body = "Hi $first_name,
Please click on this link to verify your wurkus account:
        
$url
            
This message was sent to $email at your request.";
        $mail->send();
}
?>
