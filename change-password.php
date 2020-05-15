<?php ob_start(); ?>
<?php
require_once 'logindb.php';
            //checking if SNID cookie has been set then preventing access of login-form.php
            if(!isset($_COOKIE['SNID'])){
                header('location:index.php');
            }

            //extracting token for the user
            $b_token = $_COOKIE['SNID'];
            $dbc = mysqli_connect($hn,$un,$pd,$db);
            $query = "SELECT * FROM login WHERE token = sha1('$b_token')";
            $result = mysqli_query($dbc,$query);
            $token_row = mysqli_fetch_assoc($result);
            $token = $token_row['token'];
            //checking if the server token and cookie token are same if now redirecting the page to index.html
            if(sha1($b_token)!==$token){
                header('location:index.php');
            }

            //Extracting id for the user
            $query = "SELECT user_id FROM login WHERE token = sha1('$b_token')";
            $result = mysqli_query($dbc,$query);
            $uid_row = mysqli_fetch_assoc($result);
            $uid= $uid_row['user_id'];

            //extracting first name of the user
            $query="SELECT first_name FROM public_signup WHERE id= '$uid'";
            $result= mysqli_query($dbc,$query);
            $firstname_row= mysqli_fetch_assoc($result);
            $first_name= $firstname_row['first_name'];
            
            //extracting last name of the user
            $query="SELECT last_name FROM public_signup WHERE id= '$uid'";
            $result= mysqli_query($dbc,$query);
            $lastname_row= mysqli_fetch_assoc($result);
            $last_name= $lastname_row['last_name'];

            if (isset($_GET['log_out']) && isset($_COOKIE['SNID'])){
                    $query= "DELETE FROM login WHERE token= sha1('$b_token')";
                    mysqli_query($dbc, $query);
                    setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
                    header('location:index.php');
            }

             if ($token==NULL){
                    setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
                    header('location:index.php');
                }
                //This page code begins from here
                global $hash;
                $no_error=0;
                $get_publicSignup= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM public_signup WHERE id='$uid'"));
                $db_currPass = $get_publicSignup['password'];
                if(isset($_POST['curr_pass']) && isset($_POST['new_pass']) && isset($_POST['confirm_pass'])){
                    $curr_pass=$_POST['curr_pass'];
                    $new_pass=$_POST['new_pass'];
                    $confirm_pass=$_POST['curr_pass'];
                    $new_pass_len=strlen($new_pass);
                    $hash = password_hash($new_pass, PASSWORD_BCRYPT);
                }
include_once 'accesscontrol.php';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Admin Panel | Wurkus</title>
        <link href="assets/css/stylesheets.css" rel="stylesheet">
        <script src="assets/js/jquery.min.js"></script>
        <link href="assets/css/jquery.Jcrop.css" rel="stylesheet">
        <script src="assets/js/jquery.Jcrop.js"></script>
        <style>
            body{
                padding-top: 10%;
            }
            </style>
    </head>
    <body>
        <div id="wrapper">
        <div id="nav-stick">
                <?php include 'nav.php';?> 
            </div>
            <div id="change_form" class="shadow">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <p>Enter your current password</p>
                    <input name="curr_pass" id="curr_pass" type="password" placeholder="Current Password" required>
                    <?php
                        if(empty($_POST['curr_pass']) && isset($_POST['curr_pass'])){
                            echo '<div class="alert_msg">
                            All fields are required.
                            </div>';
                        }else if(isset($curr_pass) && !password_verify($curr_pass,$db_currPass)){
                            echo '<div class="alert_msg">
                            Password did not match with current password
                            </div>';
                        }
                    ?>
                    <p>Enter your new password</p>
                    <input name="new_pass" id="new_pass" type="password" placeholder="New Password" required autocomplete="off">
                    <input name="confirm_pass" id="confirm_pass" type="password" placeholder="Confirm Password" required>
                    <?php
                        if((empty($_POST['new_pass']) || empty($_POST['confirm_pass'])) && (isset($_POST['new_pass']) || isset($_POST['confirm_pass']))){
                            echo '<div class="alert_msg">
                            All fields are required.
                            </div>';
                        }else if(isset($_POST['new_pass']) && isset($_POST['confirm_pass']) && ($_POST['new_pass'] !=$_POST['confirm_pass'])){
                            echo '<div class="alert_msg">
                            New password and confirm password should be same.
                            </div>';
                        }else if(!empty($new_pass_len) && $new_pass_len<8){
                            echo '<div class="alert_msg">
                            Password should atleast be of 8 characters.
                            </div>';
                        }
                    if(isset($_POST['curr_pass']) && isset($_POST['new_pass']) && isset($_POST['confirm_pass']) && password_verify($curr_pass,$db_currPass) && ($_POST['new_pass'] ==$_POST['confirm_pass']) && $new_pass_len>7 ){
                    $res_public=mysqli_query($dbc, "UPDATE public_signup SET password='$hash' WHERE id='$uid'");
                    if($res_public){
                    $no_error=1;
                    }
                }
                    if($no_error==1){
                        echo '<div id="success_msg">Password changed successfully! Go back to <a href="home.php">home</a>.</div>';
                    }
                    ?>
                    <input type="submit" value="Change Password">
                </form>
            </div>
        </div>
    </body>
</html>