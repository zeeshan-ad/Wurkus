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


<script>

</script>

<?php

if(isset($_POST["limit"],$_POST["start"])){
    $limit = $_POST["limit"];
    $start = $_POST["start"];
    
    if(isset($_GET['search'])){
                $result_profile =mysqli_query($dbc, "SELECT * FROM public_signup WHERE 1");
                $result_sponsor = mysqli_query($dbc, "SELECT * FROM sponsor_page WHERE 1");
                $result_event = mysqli_query($dbc, "SELECT * FROM event_page WHERE 1");
                $result_event_post = mysqli_query($dbc, "SELECT * FROM event_make_post WHERE 1");
                $array_sponsor = array();
                while ($row = mysqli_fetch_array($result_sponsor, MYSQLI_ASSOC)) {
                    $array_sponsor[] =  $row['brand_name'];
                    $array_sponsorKey[] = $row['unique_key'];
//                    $sponsor_imagePath[] = $row['image_path'];
                }
                $array_profile = array();
                while ($row = mysqli_fetch_array($result_profile, MYSQLI_ASSOC)) {
                    $array_lastname[] =  $row['last_name'];
                    $array_firstname[] =  $row['first_name'];
                    $array_profileKey[] = $row['id'];
                    $profile_imagePath[] = $row['profile_image_path'];
                }
                $array_event = array();
                while ($row = mysqli_fetch_array($result_event, MYSQLI_ASSOC)) {
                    $array_event[] =  $row['page_name'];
                    $array_eventKey[] = $row['unique_key'];
                    $event_imagePath[] = $row['image_path'];
                }
                $array_event_post = array();
                while ($row = mysqli_fetch_array($result_event_post, MYSQLI_ASSOC)) {
                    $array_event_post[] =  $row['event_name']; 
                    $array_eventPostKey[] = $row['id'];
                    $post_underId[] = $row['user_id'];
                }
                $sponsor_len = sizeof($array_sponsor);
                $event_len = sizeof($array_event);
                $firstname_len = sizeof($array_firstname);
                $lastname_len = sizeof($array_lastname);
                $eventPost_len = sizeof($array_event_post);
        }

    if(isset($_GET['search']) && !empty($_GET['search'])){
    $array_uk_spons = array();    
    $array_id_prof = array();
    $array_uk_events = array();
    $array_id_eventpost = array();    
    $search_val = strtoupper($_GET['search']);
        
                    //profiles
                    for($i=0;$i<$firstname_len;$i++){
                        similar_text(strtoupper($array_firstname[$i]),$search_val,$perc);
                        if($perc>40)
                            array_push($array_id_prof,$array_profileKey[$i]);
                    }
                    $array_id_prof_l = count($array_id_prof);
                    for($i=$start; $i<$limit; $i++){
                        if($i>=$array_id_prof_l)
                            break;
                    $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$array_id_prof[$i]'"));  
                         echo'<table class="element-box"><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$row['profile_image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>
                                <td class="display_inline_block search_element">
                                <p><a href="admin-profile.php?id='.$array_id_prof[$i].'">'.$row['first_name'].' '.$row['last_name'].'</a></p>
                                </td>
                                <td class="search_details"><p>User profile</p></td>
                                </tr></table>';
                    }
                    //sponsor pages
                    for($i=0;$i<$sponsor_len;$i++){
                        similar_text(strtoupper($array_sponsor[$i]),$search_val,$perc);
                        if($perc>40)
                            array_push($array_uk_spons,$array_sponsorKey[$i]);
                    }
                    $array_uk_spons_l = count($array_uk_spons);
                    for($i=$start; $i<$limit; $i++){
                        if($i>=$array_uk_spons_l)
                            break;
                    $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE unique_key='$array_uk_spons[$i]'"));
                        echo'<table class="element-box"><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$row['image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>
                                <td class="display_inline_block search_element">
                                <p><a href="sponsor-page.php?key='.$array_uk_spons[$i].'">'.$row['brand_name'].'</a></p>
                                </td>
                                <td class="search_details"><p>Sponsor page</p></td>
                                </tr></table>'; 
                    }
                    //event pages
                    for($i=0;$i<$event_len;$i++){
                        similar_text(strtoupper($array_event[$i]),$search_val,$perc);
                        if($perc>40)
                            array_push($array_uk_events,$array_eventKey[$i]);
                    }
                    $array_uk_events_l = count($array_uk_events);
                    for($i=$start; $i<$limit; $i++){
                        if($i>=$array_uk_events_l)
                            break;
                    $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key='$array_uk_events[$i]'"));
                    echo'<table class="element-box"><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$row['image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>
                                <td class="display_inline_block search_element">
                                <p><a href="event-page.php?key='.$array_uk_events[$i].'">'.$row['page_name'].'</a></p>
                                </td>
                                <td class="search_details"><p>Event page</p></td>
                                </tr></table>';       
                    }
                    //event posts
                    for($i=0;$i<$eventPost_len;$i++){
                        similar_text(strtoupper($array_event_post[$i]),$search_val,$perc);
                        if($perc>40)
                            array_push($array_id_eventpost,$array_eventPostKey[$i]);
                    }
                    $array_id_eventpost_l = count($array_id_eventpost);
                    for($i=$start; $i<$limit; $i++){
                        if($i>=$array_id_eventpost_l)
                            break;
                    $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM event_make_post WHERE id='$array_id_eventpost[$i]'")); 
                    $event_id = $row['user_id'];    
                    $path= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM event_page WHERE id='$event_id'"));
                            echo'<table class="element-box"><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$path['image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>
                                <td class="display_inline_block search_element">
                                <p><a href="event-post.php?id='.$array_id_eventpost[$i].'">'.$row['event_name'].'</a></p>
                                </td>
                                <td class="search_details"><p>Upcoming event</p></td>
                                </tr></table>';   
                }       
    } elseif(isset($_GET['type']) && $_GET['type']=='sponsor'){
        $array_uk_spons_f = array();
        
                    $loco = strtoupper($_GET['loco']);
                    $category = strtoupper($_GET['category']);
                    $type = $_GET['type'];
                    $maxid = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MAX(id) as MAXIMUMID FROM sponsor_page"));
                    $maxidpage = $maxid['MAXIMUMID'];
                    $minid = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MIN(id) as MINIMUMID FROM sponsor_page"));
                    $minidpage = $minid['MINIMUMID'];
                    for($i=$minidpage;$i<=$maxidpage;$i++){
                        $sponsor_page_det = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE id='$i'"));
                        similar_text(strtoupper($sponsor_page_det['location']),$loco,$perc_loc);
                        similar_text(strtoupper($sponsor_page_det['category']),$category,$perc_cat);
                        if($perc_loc>20 && $perc_cat>80)
                            array_push($array_uk_spons_f,$sponsor_page_det['unique_key']);
                    }
                     $array_uk_spons_f_l = count($array_uk_spons_f);
                    for($i=$start; $i<$limit; $i++){
                        if($i>=$array_uk_spons_f_l)    
                            break;
                    $row =  $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE unique_key='$array_uk_spons_f[$i]'"));
                        echo'<table class="element-box"><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$row['image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>
                                <td class="display_inline_block search_element">
                                <p><a href="sponsor-page.php?key='.$array_uk_spons_f[$i].'">'.$row['brand_name'].'</a></p>
                                </td>
                                <td class="search_details"><p>Sponsor Page</p></td>
                                </tr></table>';
                            }       
            }else if(isset($_GET['type']) && $_GET['type']=='event'){
                $loco = strtoupper($_GET['loco']);
                $category = strtoupper($_GET['category']);
                $type = $_GET['type'];
                $topic = strtoupper($_GET['topic']);
                $bmin = $_GET['bmin'];
                $bmax = $_GET['bmax'];
                $rmin = $_GET['rmin'];
                $rmax = $_GET['rmax'];
                $tag = strtoupper($_GET['tag']);
                $maxid = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MAX(id) as MAXIMUMID FROM event_make_post"));
                $minid = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MIN(id) as MINIMUMID FROM event_make_post"));
                $maxidpost = $maxid['MAXIMUMID'];
                $minidpost = $minid['MINIMUMID'];
                $maxid2 = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MAX(id) as MAXIMUMID FROM event_page"));
                $minid2 = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MIN(id) as MINIMUMID FROM event_page"));
                $maxidpage = $maxid2['MAXIMUMID'];
                $minidpage = $minid2['MINIMUMID'];
                $array_uk_events_f = array();
                $array_id_eventpost_f = array();
                for($i=$minidpage;$i<=$maxidpage;$i++){
                        $event_page_det = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$i'"));
                        similar_text(strtoupper($event_page_det['category']),$category,$perc_cat);
                        if($perc_cat>80)
                          array_push($array_uk_events_f,$event_page_det['unique_key']);  
                }
                $array_uk_events_f_l = count($array_uk_events_f);
                for($i=$start; $i<$limit; $i++){
                    if($i>=$array_uk_events_f_l)
                        break;
                $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key='$array_uk_events_f[$i]'"));
                echo'<table class="element-box"><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$row['image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>
                                <td class="display_inline_block search_element">
                                <p><a href="event-page.php?key='.$row['unique_key'].'">'.$row['page_name'].'</a></p>
                                </td>
                                <td class="search_details"><p>Event Page</p></td>
                                </tr></table>';        
                }
                for($i=$minidpost;$i<=$maxidpost;$i++){
                        $event_post_det = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_make_post WHERE id='$i'"));
                        similar_text(strtoupper($event_post_det['event_venue']),$loco,$perc_loc);
                        similar_text(strtoupper($event_post_det['event_topic']),$topic,$perc_cat);
                        similar_text(strtoupper($event_post_det['tags']),$topic,$perc_tag);
                        if($perc_loc>20 && $perc_tag>20 && $bmin == $event_post_det['budget_min'] && $bmax == $event_post_det['budget_max'] && $rmin == $event_post_det['reach_min'] && $rmax == $event_post_det['reach_max']){
                           array_push($array_id_eventpost_f,$event_post_det['id']);
                        }
                    }
                $array_id_eventpost_f_l = count($array_id_eventpost_f);
                for($i=$start; $i<$limit; $i++){
                    if($i>=$array_id_eventpost_f_l)
                        break;
                $row = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM event_make_post WHERE id='$array_id_eventpost_f[$i]'"));
                $post_userid=$row['user_id'];
                $post_dp = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$post_userid'"));    
                   echo'<table class="element-box"><tr>
                                <td id="profile_img_set" class="display_inline_block"> 
                                <img src="'.$row['image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                                </td>
                                <td class="display_inline_block search_element">
                                <p><a href="event-post.php?id='.$row['id'].'">'.$row['event_name'].'</a></p>
                                </td>
                                <td class="search_details"><p>Upcoming Event</p></td>
                                </tr></table>'; 
                }    
            }        
}   

?>