<?php
// Start the session
session_start();
?>
<?php
            use PHPMailer\PHPMailer\PHPMailer;
            require_once 'logindb.php';
            $emailNoExistErr = $email = $first_name = $code ="";
            global $email_present;
            $reset_key ="";
        
            if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])){  
                /*this for when page load see it's have form's request or not  */
                $email = $_POST['email'];
                $dbc = mysqli_connect($hn,$un,$pd,$db);
                $query= "SELECT * FROM public_signup WHERE email = '{$email}'";
                $result_mail = mysqli_query($dbc,$query);
                mysqli_close($dbc);
                $row = mysqli_num_rows($result_mail);
                $row_mail = mysqli_fetch_assoc($result_mail);
                $email_present = $row_mail['email'];
        } else {/*this is for first time because we have to set row variable without it , error will show  */
            $row = "";}
        
            //Taking first_name from DB
            $dbc = mysqli_connect($hn,$un,$pd,$db);
                $query= "SELECT first_name FROM public_signup WHERE email = '{$email}'";
                $result_name = mysqli_query($dbc,$query);
                mysqli_close($dbc);
                $row_name = mysqli_fetch_assoc($result_name);
                $first_name = $row_name['first_name'];
        
                    
        //generating temp reset password code
            function randomKey($length) {
            $key ="";    
            $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));

                for($i=0; $i < $length; $i++) {
                    $key .= $pool[mt_rand(0, count($pool) - 1)];
                }
                return $key;
            }
            
            $reset_key = strtoupper(randomKey(6));
            $_SESSION['reset_key'] = $reset_key;
            
               function sendResetCode(){
                        global $email, $reset_key, $first_name;
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
                        $mail->Subject = "$reset_key is your Wurkus account reset password code.";
                        $mail-> Body = "Hi $first_name,
You have requested to reset your Wurkus account password. Your password reset code is:
$reset_key
This email is sent to $email at your own request."; 
                        $mail->send();
            }               
        
        if(($email_present == $email) && isset($_POST['email'])){
            $dbc = mysqli_connect($hn,$un,$pd,$db);
            $query = "UPDATE public_signup SET reset_key='$reset_key' WHERE email='$email'";
            $result = mysqli_query($dbc,$query);
            mysqli_close($dbc);
            sendResetCode();
            header('Location:reset-password.php');
        }
        
        //checking if SNID cookie has been set then preventing access of forget-password.php
        if(isset($_COOKIE['SNID'])){
            header('location:home.php');
        }
?>
<!doctype html>
<html>
    <head>
         <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Forget Password | Wurkus</title>
        <link href="assets/css/styles.css" rel="stylesheet">
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "https://m.wurkus.com/forget-password.php"; 
            }
        </script>
    </head>
    <body>
        <div id="nav">  
            <a href="index.php"><img src="images/wurkus_logo_full.gif"></a>
        </div>
        <div id="log-form-pad">
        <div id="join-form">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <ul id="field-style">
                <li id="text"><p>Enter your email we'll send you a 6-digit<br>code in mail to reset your password.</p></li>
                <li><input type="email" name="email" id="email" placeholder="E-mail" required></li>
                <div class="error"><?php
                    if($row===0){
                        $emailNoExistErr = 'This email is not signed up with us.';
                        echo $emailNoExistErr;}
                    ?>
                </div>
                <li><div id="log">
                <input type="submit" value="submit" id="submit">
                    </div>
                </li>
            </ul>
        </form>
        </div>
        </div>    
    </body>
    <footer>
        <div id="wrapper">
        <p>Wurkus &copy;2017</p>
            <p><a href="terms.php">Terms of service</a> &amp; <a href="privacy-policy.php">Privacy policy</a></p>
            </div>
    </footer>
</html>