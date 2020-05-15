<?php
global $inbox;
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
               include_once 'accesscontrol.php'; 
            //creating event to delete token from login db after a week  
            $query2 = "CREATE EVENT deleteToken$token ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 WEEK DO DELETE FROM login WHERE token = '$token' ";
            mysqli_query($dbc,$query2);
            
            if ($token==NULL){
                setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
                header('location:index.php');
            }


                        $from_id = $uid; 
                        $inbox_present=0;
                        $message_db_last_id_array = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM message"));
                        $message_db_last_id= $message_db_last_id_array['MaximumID'];
                        $i=$message_db_last_id;
                        $j=0;
                        while($i>=0){
                            $message_row= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM message WHERE id='$i'"));
                            $db_to_id = $message_row['to_id'];
                            $db_from_id = $message_row['from_id'];
                            if($from_id==$db_to_id || $from_id==$db_from_id){
                                $inbox_present =1;
                               if($from_id==$db_to_id){
                                   $inbox[$j]= $db_from_id;
                                   $j++;
                               }
                                if($from_id==$db_from_id){
                                    $inbox[$j]= $db_to_id;
                                    $j++;
                                }
                                   
                            }
                            $i--;
                        } 
                        if($inbox!=NULL)
                            $inbox = array_unique($inbox);
                        $i=0;
                        while($i<=$message_db_last_id){
                            if(isset($inbox[$i])){
                                $sender_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$inbox[$i]'"));
                                $query = "SELECT * FROM message WHERE from_id='$inbox[$i]' AND to_id='$uid' ORDER BY id DESC";
                                $result = mysqli_query($dbc,$query);
                                $row = mysqli_fetch_array($result);
                                echo '<div class="inbox_element" onclick="readMessages('.$inbox[$i].');">
                                <a id="links_messages" href="message.php?id='.$inbox[$i].'">
                                <table><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$sender_details['profile_image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>';
                                if($row['opened']==0&&$row['to_id']==$uid){
                                    echo '<td class="display_inline_block_no"  id="not_opened">
                                    <table>
                                    <tr><td>
                                    <p>'.ucfirst($sender_details['first_name']).' '.ucfirst($sender_details['last_name']).'</p></td><td id="new_notify"><p>new message!</p></td></tr></table>';}
                                else{
                                    echo '<td class="display_inline_block_o">
                                    <p>'.ucfirst($sender_details['first_name']).' '.ucfirst($sender_details['last_name']).'</p>';}
                                echo '</td>
                                </tr></table>
                                </a></div>';
                            }
                            $i++;
                        }
                        if($inbox_present==0){
                            echo '<span id="empty_inbox"><p>Your inbox is empty.</p></span>';
                        }
                        
                    ?>