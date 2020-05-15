<?php ob_start(); ?>
<?php
        require_once 'functions.php';
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
        $query="SELECT * FROM public_signup WHERE id= '$uid'";
        $result= mysqli_query($dbc,$query);
        $firstname_row= mysqli_fetch_assoc($result);
        $first_name= $firstname_row['first_name'];
        $activate = $firstname_row['activate_key'];
        $email = $firstname_row['email'];
        
        if (isset($_GET['log_out']) && isset($_COOKIE['SNID'])){
                $query= "DELETE FROM login WHERE token= sha1('$b_token')";
                mysqli_query($dbc, $query);
                setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
                header('location:index.php');
        }
                
            //creating event to delete token from login db after a week  
            $query2 = "CREATE EVENT deleteToken$token ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 WEEK DO DELETE FROM login WHERE token = '$token' ";
            mysqli_query($dbc,$query2);
            
            if ($token==NULL){
                setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
                header('location:index.php');
            }
    if(isset($_GET['send_link'])){
        sendLink();
    }
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
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "http://m.wurkus.com/help.php"; 
            }
        </script>
        <style>
            body{
                padding: 0;
                margin: 0;
            }
            </style>
    </head>
    <body>
        <div id="wrapper">
        <div id="nav-stick">
                <?php include 'nav.php';?> 
            </div>
            <div id="help_center">
                <div id="help_head">
                    <table><tr>
                        <td><img src="images/w_logo.gif" width="30px" height="30px"></td>
                        <td><p>Help Center</p></td></tr>
                    </table>
                </div>
                <div id="help_body"  class="shadow">
                    <p id="help_title">Questions You May Have</p>
                    <div class="help_q">
                        <p class="ques"><span class="ques_ans">Question</span>Who can see the pages that I have under my account?</p>
                        <p class="ans"><span class="ques_ans">Answer</span>Pages held under any account is by default public- meaning it can be seen by anyone. This is to build trust within our community by viewing one's activity.</p>
                    </div>
                    <div class="help_q">
                        <p class="ques"><span class="ques_ans">Question</span>Who can see posts made under my page?</p>
                        <p class="ans"><span class="ques_ans">Answer</span>Posts made under sponsor page or event page can be viewed by anyone who follows your page, who views your page or when your post makes it to our trending tab.</p>
                    </div>
                    <div class="help_q">
                        <p class="ques"><span class="ques_ans">Question</span>How do I change my password when I dont remember my current password?</p>
                        <p class="ans"><span class="ques_ans">Answer</span>Log out of your account and click on forget password from sign in page.</p>
                    </div>
                    <div class="help_q">
                        <p class="ques"><span class="ques_ans">Question</span>How do I delete my page?</p>
                        <p class="ans"><span class="ques_ans">Answer</span>Go to admin profile and click on delete.<br>NOTE: Page once deleted cannot be recovered.</p>
                    </div>
                    <div class="help_q">
                        <p class="ques"><span class="ques_ans">Question</span>How do I make someone else admin of my page?</p>
                        <p class="ans"><span class="ques_ans">Answer</span>Go to admin profile and enter the email and click on change admin.<br>NOTE: Email enetered should belong to someone who is already signed up with Wurkus.</p>
                    </div>
                    <div class="help_q">
                        <p class="ques"><span class="ques_ans">Question</span>How do I make someone admin of my page while I remain admin too?</p>
                        <p class="ans"><span class="ques_ans">Answer</span>Currently Wurkus does not allow this, only one admin is allowed for each page.</p>
                    </div>
                </div>
            </div>
            <div id="report" class="shadow">
                <p id="report_title">Report a Problem</p>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <textarea id="prob" name="prob" placeholder="Briefly explain what happened"></textarea>
                    <input type="submit" value="submit">
                </form>
                <?php
                global $success;
                if(isset($_POST['prob'])){
                    $prob = $_POST['prob'];
                    $success=mysqli_query($dbc,"INSERT INTO report (email,problem) VALUES ('$email','$prob')");
                }
                ?>
                <div id="report_success">We can't respond to every report, but many submissions help us improve Wurkus for everyone.</div>
            </div>
        </div>
        <?php mysqli_close($dbc);?>
    </body>
</html>    