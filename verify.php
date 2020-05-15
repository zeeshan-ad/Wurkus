<?php
    session_start();
?>
    <?php
    require_once 'logindb.php';
    $email= $_GET['email'];
    $activate = $_GET['activate'];
    
    if(!isset($email) || !isset($activate)){
        header('location:index.php');
    }
    
    $dbc = mysqli_connect($hn,$un,$pd,$db);
    $query = "SELECT activate_key FROM public_signup WHERE email= '{$email}'";
    $result_activate_key = mysqli_query($dbc,$query);
    mysqli_close($dbc);
    $row = mysqli_fetch_assoc($result_activate_key);
    $activate_key = $row['activate_key'];
    
    //checking if SNID cookie has been set then preventing access of reset.php
        if(isset($_COOKIE['SNID'])){
            header('location:home.php');
        }
    ?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Wurkus | Account Verification</title>
        <link href="assets/css/styles.css" rel="stylesheet">
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "http://m.wurkus.com/verify.php?email=<?php echo $email;?>&activate=<?php echo $activate;?>"; 
            }
        </script>
    </head>
    <body>
        <div id="nav">  
            <a href="index.php"><img src="images/wurkus_logo_full.gif"></a>
        </div>
        <div id="activate">
            <?php 
            if(isset($activate) && $activate==$activate_key){
                $dbc = mysqli_connect($hn,$un,$pd,$db);
                $query = "UPDATE public_signup SET activate_check='1' WHERE email= '{$email}'";
                $result_activate_key = mysqli_query($dbc,$query);
                mysqli_close($dbc); 
                echo '<div id="verify">';
                echo '<h1>Your email has been verified successfully.</h1>';
                echo '<p>To start using Wurkus <a href="login-form.php">Login</a></p>';
                echo '</div>';
            }else{
                echo '<div id="verify">';
                echo '<h1>Invalid Link</h1>';
                echo '<p>The link that you used is either invalid or expired.<br><a href="index.php">Go back to home</a></p>';
                echo '</div>';
            }?>
        </div>
    </body>
    <div id="log-form-pad">
    </div>
    <footer>
        <div id="wrapper">
            <p>Wurkus &copy;2017</p>
            <p><a href="terms_and_policy.html">Terms of service &amp; Privacy policy</a></p>
        </div>
    </footer>
</html>