<?php
// Start the session
session_start();
?>
<?php
            require_once 'logindb.php';
            $reset_password = $confirm_password ="";
            if(isset($_SESSION['reset_key'])){
                $reset_key = $_SESSION['reset_key'];
                $dbc = mysqli_connect($hn,$un,$pd,$db);
                $query = "SELECT email FROM public_signup WHERE reset_key = '$reset_key'";
                $result = mysqli_query($dbc, $query);
                mysqli_close($dbc);
                $row_mail = mysqli_fetch_assoc($result);
                $email = $row_mail['email'];
            } else{
                header('location:index.php');
            }
            
            $code="";
            if(isset($_POST['code']) && isset($_POST['reset_password']) && isset($_POST['confirm_password'])){
                $code = $_POST['code'];
                $reset_password = $_POST['reset_password'];
                $confirm_password = $_POST['confirm_password'];
            }
               $hash = password_hash($confirm_password, PASSWORD_BCRYPT);
            $confirm_password_len = strlen($confirm_password);
            $reset_password_len = strlen($reset_password);
        
        if(($code === $reset_key) && ($reset_password === $confirm_password) && ($reset_password_len >= 8) && ($confirm_password_len >= 8)){
            $dbc_update = mysqli_connect($hn,$un,$pd,$db);
            $query_update = "UPDATE public_signup SET password = '$hash' WHERE email = '$email'";
            $result_update = mysqli_query($dbc_update, $query_update);
            mysqli_close($dbc_update);
        }
        
        $flag="";
        if(isset($result_update) && $result_update){
            $flag=1;
            $_SESSION['flag'] = $flag;
            header('location:index.php');
        }
        
        //checking if SNID cookie has been set then preventing access of reset-password.php
        if(isset($_COOKIE['SNID'])){
            header('location:home.php');
        }
        ?>
<!doctype html>
<html>
    <head>
         <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Reset Password | Wurkus</title>
        <link href="assets/css/styles.css" rel="stylesheet">
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "https://m.wurkus.com/reset-password.php"; 
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
                <li id="text"><p>Enter the code below.</p></li>
                <li><input type="text" name="code" id="code" placeholder="######" required></li>
                <div class="error">    
                <?php
                    if(!empty($reset_key) && !empty($code)){
                        if(($reset_key != $code)){
                            echo "Invalid code";}
                    }
                    ?>
                </div>
                <li id="text"><p>Enter your new password.</p></li>
                <li><input type="password" name="reset_password" id="reset_password" placeholder="New Password" required></li>
                <li><input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required></li>
                <div class="error">
                    <?php
                    if(isset($reset_password) && isset($confirm_password)){
                        if($reset_password != $confirm_password){
                            echo "Entered passwords did not match.";}
                    }
                    if(!empty($reset_password_len) && !empty($confirm_password_len)){
                    if(($reset_password_len < 8) || ($confirm_password_len < 8)){
                        echo "Minimum length should be of 8 characters.";
                    }
                    }
                    ?>
                </div>
                <li><div id="log">
                <input type="submit" value="Reset" id="Reset">
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