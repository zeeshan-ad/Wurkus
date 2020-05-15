<?php
// Start the session
session_start();
?>
<?php
        require_once 'logindb.php';
        require_once 'functions.php';
        $first_name = $last_name = $email = $password ="";
        $email_existErr = $passwordErr = $emailErr = $last_nameErr = $first_nameErr ="";
        if(isset($_SESSION['first_nameErr'])){
        $first_nameErr = $_SESSION['first_nameErr'];
        }
        if(isset($_SESSION['last_nameErr'])){
        $last_nameErr = $_SESSION['last_nameErr'];
        }
        if(isset($_SESSION['emailErr'])){
        $emailErr = $_SESSION['emailErr'];
        }
        if(isset($_SESSION['email_existErr'])){
        $email_existErr = $_SESSION['email_existErr'];
        }
        if(isset($_SESSION['passwordErr'])){
        $passwordErr = $_SESSION['passwordErr'];
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
        $pdlen = strlen($password);
        
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
        
        //checking if SNID cookie has been set then preventing access of join-us-form.php
        if(isset($_COOKIE['SNID'])){
            header('location:home.php');
        }
        
        ?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Sign Up | Wurkus</title>
        <link href="assets/css/styles.css" rel="stylesheet">
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "http://m.wurkus.com/join-us-form.php"; 
            }
        </script>
    </head>
    <body>
        <div id="nav">  
            <a href="index.php"><img src="images/wurkus_logo_full.gif"></a>
        </div>
        <div id="join-form-pad">
        <div id="join-form">
            <h2>Join now - it's free</h2>
            <ul id="sign-list">
                <li><img src="images/w_logo.gif"></li>
                <li><div id="member"><p>Already a member? <a href="login-form.php"->Log in</a></p></div></li>
            </ul>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <ul id="field-style">
                    <li><input type="text" name="firstname" id="firstname" placeholder="First name" required></li>
                    <div class="error"><?php 
                        if (!preg_match("/^[a-zA-Z]*$/",$first_name) || isset($_SESSION['first_nameErr'])) {
                        $first_nameErr = "Only letters allowed.";
                        echo $first_nameErr;}?>
                    </div>
                    <li><input type="text" name="lastname" id="lastname" placeholder="Last name" required></li>
                    <div class="error"><?php 
                        if (!preg_match("/^[a-zA-Z]*$/",$last_name) || isset($_SESSION['last_nameErr'])) {
                        $last_nameErr = "Only letters allowed.";
                        echo $last_nameErr;}?>
                    </div>
                    <li><input type="email" name="email" id="email" placeholder="E-mail" required></li>
                    <div class="error"><?php 
                        if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) == false || isset($_SESSION['emailErr'])){
                            $emailErr = "Invalid email.";
                            echo $emailErr;}
                        if(mysqli_num_rows($result_check)!=0 || isset($_SESSION['email_existErr'])){
                            $email_existErr = 'This email already exists with us.';
                            echo $email_existErr;
                        }?>
                    </div>
                    <li><input type="password" name="password" id="password" placeholder="Password" required></li>
                    <div class="error"><?php 
                        if(!empty($pdlen) && $pdlen<8 || isset($_SESSION['passwordErr'])){
                            $passwordErr = 'Minimum length should be of 8 characters.';
                            echo $passwordErr;
                        }?>
                    </div>
                <li><div id="join">
                    <input type="submit" value="Join now" id="signup">
                    </div></li>
                </ul>
                </form>
                <p>By clicking on join now you agree to our<br/><span id="style-terms"><a href="terms.php">Terms of service</a> &amp; <a href="privacy-policy.php">Privacy policy</a></span></p>
        
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