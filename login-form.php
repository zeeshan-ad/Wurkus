<?php
// Start the session
session_start();
?>
<?php
        require_once 'logindb.php';
        if(($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST['log_email']) && isset($_POST['log_password'])){
        $log_email = strtolower($_POST['log_email']);
        $log_password = $_POST['log_password'];
        $dbc = mysqli_connect($hn,$un,$pd,$db);
        $query= "SELECT * FROM public_signup WHERE email = '{$log_email}'";
        $result_mail = mysqli_query($dbc,$query);
        mysqli_close($dbc);
        $row_email = mysqli_num_rows($result_mail);    
        }else {/*this is for first time because we have to set row variable without it , error will show  */
            $row_email = "" ;
        }
        
        if(isset($_POST['log_password'])){ 
            $dbc = mysqli_connect($hn,$un,$pd,$db);
            $query= "SELECT password FROM public_signup WHERE email = '{$log_email}'";
            $result_pass = mysqli_query($dbc,$query);
            mysqli_close($dbc);
            $passRow = mysqli_fetch_assoc($result_pass);
            $passVal = $passRow['password'];
        if(password_verify($log_password , $passVal)){
            $_SESSION['pwd'] = $log_password;
            $_SESSION['email'] = $log_email;
            header('Location:home.php'); //redirect
            $cstrong = True;
            $token = bin2hex(openssl_random_pseudo_bytes(64,$cstrong));
            $dbc = mysqli_connect($hn,$un,$pd,$db);
            $query = "SELECT id FROM public_signup where email='$log_email'";
            $result=mysqli_query($dbc,$query);
            $uid_row= mysqli_fetch_assoc($result);
            $uid= $uid_row['id'];
            mysqli_close($dbc);
            $dbc2 = mysqli_connect($hn,$un,$pd,$db);
            mysqli_query($dbc2,"INSERT INTO login (token, user_id) VALUES (sha1('$token'), '$uid')");
            mysqli_close($dbc2);
            setcookie("SNID",$token,time()+60*60*24*7, '/', NULL, NULL, TRUE);
            }
        } else {
            $passVal="";
        }
        
        if(isset($first_nameErr)){
        $first_nameErr = $_SESSION['email_noExistErr'];
        }
        if(isset($last_nameErr)){
        $last_nameErr = $_SESSION['password_check'];
        }
        //checking if SNID cookie has been set then preventing access of login-form.php
        if(isset($_COOKIE['SNID'])){
            header('location:home.php');
        }
        ?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Sign In | Wurkus</title>
        <link href="assets/css/styles.css" rel="stylesheet">
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "http://m.wurkus.com/login-form.php"; 
            }
        </script>
    </head>
    <body>
        <div id="nav">  
            <a href="index.php"><img src="images/wurkus_logo_full.gif"></a>
        </div>
        <div id="log-form-pad">
        <div id="join-form">
            <ul id="sign-list">
                <li><img src="images/w_logo.gif"></li>
                <li><div id="member"><p>Not a member? <a href="join-us-form.php">Sign up</a></p></div></li>
            </ul>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <ul id="field-style">
                    <li><input type="email" name="log_email" id="log_email" placeholder="E-mail" required></li>
                    <div class="error"><?php 
                        if(($row_email===0) || isset($_SESSION['email_noExistErr'])){
                        $email_noExistErr = 'This email is not signed up with us.';
                        echo $email_noExistErr;}
                        ?>
                    </div>
                    <li><input type="password" name="log_password" id="log_password" placeholder="Password" required></li>
                    <div class="error"><?php 
                        if(isset($_POST['log_password']) && password_verify($log_password , $passVal) != TRUE || isset($_SESSION['password_check'])){
                        $password_check = "The email or password did not match." ;
                        echo $password_check;}
                        ?>
                    </div>
                    <li><div id="log">
                    <input type="submit" value="Login" id="login">
                        </div></li>
                    <li><br><div id="lost-login"><a href="forget-password.php">Lost my password</a></div></li>
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