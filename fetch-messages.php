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
                
            //creating event to delete token from login db after a week  
            $query2 = "CREATE EVENT deleteToken$token ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 WEEK DO DELETE FROM login WHERE token = '$token' ";
            mysqli_query($dbc,$query2);
            
            if ($token==NULL){
                setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
                header('location:index.php');
            }
include_once 'accesscontrol.php';


if(isset($_GET["limit"],$_GET["start"],$_GET["to_id"])){
    $dbc = mysqli_connect($hn,$un,$pd,$db);
    $start = $_GET["start"];
    $limit = $_GET["limit"];
    $to_id=$_GET["to_id"];
    $from_id = $uid;
    $to_id_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$to_id'"));
    $message_sent_number=mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM message WHERE to_id='$to_id' and from_id='$from_id'"));
    $message_recieved_number=mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM message WHERE to_id='$from_id' and from_id='$to_id'"));
    if($message_sent_number!=0 || $message_recieved_number!=0){
    $query = "SELECT * FROM message ORDER BY id DESC LIMIT $start, $limit";
    $result = mysqli_query($dbc,$query);
    while($row = mysqli_fetch_array($result)){
             $db_to_id = $row['to_id']; 
             $db_from_id = $row['from_id'];
             if($to_id==$db_to_id && $from_id==$db_from_id){
                                    echo '<div id="new_line"><div class="individual_message_reciever">
                                    <p><span id="message_name">'.ucfirst($first_name)." ".ucfirst($last_name).':</span>
                                    '.nl2br($row['message']).'</p>
                                    </div></div>';
             } else if($from_id==$db_to_id && $to_id==$db_from_id){
                                    echo '<div id="new_line"><div class="individual_message_sender">
                                    <p><span id="message_name">'.ucfirst($to_id_details['first_name'])." ".ucfirst($to_id_details['last_name']).':</span>
                                    '.nl2br($row['message']).'</p>
                                    </div></div>';
             }   
         }
        
    }
}

?>