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
include_once 'accesscontrol.php';
?>
<script  type="text/javascript">
    
    
</script>


<?php

if(isset($_POST["limit"],$_POST["start"])){
    $limit = $_POST["limit"];
    $start = $_POST["start"];
    $array_id_followtable = array();
    global $numNotify;
    $numNotify = 0; 
    $eventOrSpon=0;
    $notification=0;
    $result= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM follow_table"));
    $result2= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MIN(id) as MinimumID FROM follow_table"));   
    $notify_lastid = $result['MaximumID'];
    $notify_firstid = $result2['MinimumID'];
    if($notify_lastid !=0){
        while($notify_lastid >= $notify_firstid){
            $follow_table = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM follow_table WHERE id='$notify_lastid'"));
            if($follow_table){
                $page_unique_key = $follow_table['follow_key'];
                $id_followtable = $follow_table['id'];
                $id_following_spon = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM sponsor_page WHERE unique_key='$page_unique_key'"));
                $id_following_event = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM event_page WHERE unique_key='$page_unique_key'"));
                if($id_following_spon){
                    $notify_to = $id_following_spon['user_id'];
                }
                if($id_following_event){
                    $notify_to = $id_following_event['user_id'];
                }
                if($uid == $notify_to){
                        array_push($array_id_followtable,$id_followtable);
                        }                    
                }
            
        $notify_lastid--;
    
        }
    }

    
    $array_id_followtable_l = count($array_id_followtable);
    for($i=$start; $i<$limit; $i++){
        if($i>=$array_id_followtable_l)
            break;
    $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM follow_table WHERE id='$array_id_followtable[$i]'"));
    $uk_followtable = $row['follow_key'];    
    $id_following_spon = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM sponsor_page WHERE unique_key='$uk_followtable'"));
    $id_following_event = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM event_page WHERE unique_key='$uk_followtable'"));    
        if($id_following_spon){
            $notify_to = $id_following_spon['user_id'];
            $page_name = $id_following_spon['brand_name'];
            $eventOrSpon=1;
        }
        if($id_following_event){
            $notify_to = $id_following_event['user_id'];
            $page_name = $id_following_event['page_name'];
        }
        $followedById = $row['follower_id'];
        $followedBy = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM public_signup WHERE id='$followedById'"));
        if($uid == $notify_to){
            if($eventOrSpon==1){
                echo '<table id="notify_block"><tr>
                        <td id="profile_img_set" class="display_inline_block"> 
                        <img src="'.$followedBy['profile_image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                        </td>
                        <td><p><a href="admin-profile.php?id='.$followedById.'">'.ucfirst($followedBy['first_name']).' '.ucfirst($followedBy['last_name']).'</a> has followed your page <a href="sponsor-page.php?key='.$uk_followtable.'">'.ucfirst($page_name).'</a>. 
                        </p></td>
                        </tr></table>';
            } else{
                        echo '<table id="notify_block"><tr>
                        <td id="profile_img_set" class="display_inline_block"> 
                        <img src="'.$followedBy['profile_image_path'].'"    onerror=\'this.src="images/default_profile_pic.png"\'>
                        </td>
                        <td><p><a href="admin-profile.php?id='.$followedById.'">'.ucfirst($followedBy['first_name']).' '.ucfirst($followedBy['last_name']).'</a> has followed your page <a href="event-page.php?key='.$uk_followtable.'">'.ucfirst($page_name).'</a>. 
                        </p></td>
                        </tr></table>';
            }
        }
        $eventOrSpon=0;
    }
    
}

?>