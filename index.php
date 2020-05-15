<?php
    session_start();
?>
<?php
        require_once 'logindb.php';
        require_once 'functions.php';
        $first_name = $last_name = $email = $password = $log_password= $log_email="";
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
        
        if(($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['password'])){
        $first_name = validate($_POST['firstname']);
        $last_name = validate($_POST['lastname']);        
        $email = strtolower($_POST['email']);
        $password = $_POST['password'];
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        function randomKey($length) {
            $key ="";    
            $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));

                for($i=0; $i < $length; $i++) {
                    $key .= $pool[mt_rand(0, count($pool) - 1)];
                }
                return $key;
            }
        $activate = randomKey(32);
        
        $dbc = mysqli_connect($hn,$un,$pd,$db);
        $query= "SELECT * FROM public_signup WHERE email = '{$email}'";
        $result_check = mysqli_query($dbc,$query);
        mysqli_close($dbc);
        
        $pdlen = strlen($password);
        function validate($data){
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                return $data;
            }
        
        if (!preg_match("/^[a-zA-Z]*$/",$first_name)) {
                $first_nameErr = "Only letters allowed.";
                header('Location:join-us-form.php');}
        
        if (!preg_match("/^[a-zA-Z]*$/",$last_name)) {
                $last_nameErr = "Only letters allowed.";
                header('Location:join-us-form.php');}
        
        if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) == false){
                    $emailErr = "Invalid email.";
                    header('Location:join-us-form.php');}
        
        if(!empty($pdlen) && $pdlen<8){
                    $passwordErr = 'Minimum length should be of 8 characters.';
                    header('Location:join-us-form.php');}
        
        if(mysqli_num_rows($result_check)!=0){
                    $email_existErr = 'This email already exists with us.';
                    header('Location:join-us-form.php');}
        
        $result_activate ="";
        
        if(isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['password']) &&(preg_match("/^[a-zA-Z]*$/",$first_name)&&preg_match("/^[a-zA-Z]*$/",$last_name))&&
              (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) && ($pdlen >= 8) && (mysqli_num_rows($result_check)==0)){
            
        $dbc= mysqli_connect ($hn,$un,$pd,$db);
        $query= "INSERT INTO public_signup (first_name, last_name, email, password, activate_key) VALUES ('$first_name','$last_name','$email','$hash','$activate')";
        $result_activate = mysqli_query($dbc,$query);
        mysqli_close($dbc);
        } 
            
        
        if($result_activate){
            $_SESSION['pwd'] = $password;
            $_SESSION['email'] = $email;
            header('Location:home.php');
            sendLink();
            $cstrong = True;
            $token = bin2hex(openssl_random_pseudo_bytes(64,$cstrong));
            $dbc = mysqli_connect($hn,$un,$pd,$db);
            $query = "SELECT id FROM public_signup where email='$email'";
            $result=mysqli_query($dbc,$query);
            $uid_row= mysqli_fetch_assoc($result);
            $uid= $uid_row['id'];
            mysqli_close($dbc);
            $dbc2 = mysqli_connect($hn,$un,$pd,$db);
            mysqli_query($dbc2,"INSERT INTO login (token, user_id) VALUES (sha1('$token'), '$uid')");
            mysqli_close($dbc2);
            setcookie("SNID",$token,time()+60*60*24*7, '/', NULL, NULL, TRUE);
        }
        
        //login-form
        
        if($row_email===0){
           $email_noExistErr = 'This email is not signed up with us.';
           header('Location:login-form.php');}
        
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
        
        if(isset($_POST['log_password']) && password_verify($log_password , $passVal) != TRUE){
            $password_check = "The email or password did not match." ;
            header('Location:login-form.php');}
        
        //sessions
        if(isset($first_nameErr) || isset($last_nameErr) || isset($emailErr) || isset($email_existErr) || isset($passwordErr)){
            $_SESSION['first_nameErr'] = $first_nameErr;
            $_SESSION['last_nameErr'] = $last_nameErr;
            $_SESSION['emailErr'] = $emailErr;
            $_SESSION['email_existErr'] = $email_existErr;
            $_SESSION['passwordErr'] = $passwordErr;
        }
        
        if(isset($email_noExistErr) || isset($password_check)){
            $_SESSION['email_noExistErr'] = $email_noExistErr;
            $_SESSION['password_check'] = $password_check;
        }
        if(isset($_SESSION['flag'])){
        $flag = $_SESSION['flag'];
        } else {
            $flag ="";
        }
        
        //checking if SNID cookie has been set then preventing access of index.php
        if(isset($_COOKIE['SNID'])){
            header('location:home.php');
        }
         ?>
<!doctype html>
<html lang="en">
    <head>
        <meta name="description" content="Find Sponsors and Events. Wurkus is a platform that allows sponsors and events find their correct match. Publish your event.">
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Wurkus | Advertise or Sponsor</title>
        <link href="assets/css/styles.css" rel="stylesheet">
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "http://m.wurkus.com"; 
            }
        </script>
    </head>
    <body>
        <div id="wrapper">
                <?php
                if($flag == 1){
                    echo '<div class="success">';
                    echo "Your password has been changed successfully.";
                    echo '</div>';
                }
                ?>
        <div id="nav">
            <ul>  
                <li><a href="index.php"><img src="images/wurkus_logo_full.gif"></a></li>
                <div id="float-right-nav">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <li><input type="email" name="log_email" id="log_email" placeholder="E-mail" required></li>
                    <li><input type="password" name="log_password" id="log_password" placeholder="Password" required></li>
                    <div id="login-background">
                        <li><input type="submit" value="Log in" id="login"></li>
                    </div>
                </form>
                <div id="lost"><a href="forget-password.php">Lost my password</a></div>
                </div>
            </ul>
        </div>
        <div id="signup-section">
            <h2>Join now - it's free</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <ul id="field-style">
                    <li><input type="text" name="firstname" id="firstname" placeholder="First name" required></li>
                    <li><input type="text" name="lastname" id="lastname" placeholder="Last name" required></li>
                    <li><input type="email" name="email" id="email" placeholder="E-mail" required></li>
                    <li><input type="password" name="password" id="password" placeholder="Password" required></li>
                <li><div id="join">
                    <input type="submit" value="Join now" id="signup">
                    </div></li>
                </ul>
                </form>
                <p>By clicking on join now you agree to our<br/><span id="style-terms"><a href="terms.php">Terms of service</a> &amp; <a href="privacy-policy.php">Privacy policy</a></span></p>
        </div>
        <div class="centre-body">
            <div id="big-info">
                <p><span class="highlight">Advertise</span> your product</p>
                <p>or <span class="highlight">find sponsors</span> for an event</p>
            </div>
            <div id="info">
                <div id="find">
                    <img class="info-space" src="images/find.gif">
                    <p>Find</p>
                </div>
                <div id="connect">
                    <img class="info-space" src="images/connect.gif">
                    <p>Connect</p>
                </div>
                <div id="grow">
                    <img class="info-space" src="images/grow.gif">
                    <p>Grow</p>
                </div>
            </div>
            <div id="moto">
                <p><span class="highlight">We believe that the right product should meet the right audience.</span></p>
            </div>
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
        