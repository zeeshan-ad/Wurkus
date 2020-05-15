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
                
            //creating event to delete token from login db after a week  
            $query2 = "CREATE EVENT deleteToken$token ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 WEEK DO DELETE FROM login WHERE token = '$token' ";
            mysqli_query($dbc,$query2);
            
            if ($token==NULL){
                setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
                header('location:index.php');
            }
include_once 'accesscontrol.php';
?>
<script  type="text/javascript">

// Hide the extra content initially, using JS so that if JS is disabled, no problemo:
$('.read-more-content').addClass('hide');
$('.read-more-show').removeClass('hide');

// Set up the toggle effect:
$('.read-more-show').on('click', function(e) {
  $(this).next('.read-more-content').removeClass('hide');
  $(this).addClass('hide');
  e.preventDefault();
});

//    //ajax for follow unfollow click
//        
//             $(function(){
//        $('.ajax_reload').on('click',function(){
//     $.ajax({
//                 url : 'fetch-trending.php',
//                 type:'POST',
//                 data : {key: $(this).data('key'), val:$(this).data('val')},
//                datType:'json',
//                success:function(data)
//                {
//                   location.reload();
//                }
//            });
//    })
//    })
//         // end
</script>

<?php

if(isset($_POST["limit"],$_POST["start"])){
    $json = file_get_contents('https://geoip-db.com/json');
    $data = json_decode($json);
    $user_latitude = $data->latitude;
    $user_longitude = $data->longitude;
    
    $dbc = mysqli_connect($hn,$un,$pd,$db);
    $start = $_POST["start"];
    $limit = $_POST["limit"];
    //code for about in sponsor page
    $query = "SELECT *,( 6371 * acos( cos( radians($user_latitude) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians($user_longitude) ) + sin( radians($user_latitude) ) * sin(radians( lat )) ) ) AS distance FROM sponsor_page ORDER BY distance LIMIT $start, $limit";
    $result = mysqli_query($dbc,$query);
    while($row = mysqli_fetch_array($result)){
        echo '<div id="public_post" class="shadow">';
                echo '<span id="page_name_title">'; echo '<img id="public_post_image" src="'.$row['image_path'].'" onerror=this.src="images/page_default_sm.gif">'; echo '</span>';
                echo '<span id="public_post_name"><a href="sponsor-page.php?key='.$row['unique_key'].'">'.$row['brand_name'].'</a><span id="trend-det">sponsor page</span></span>'; echo '<br>';
                echo '<div id="data_public_post">';
                echo '<span><p>Product category:</p> '.$row['category'].'.</span><br>';
                        $len = strlen($row['description']);
                        if($len>300){
                        $short_des = substr($row['description'],0,300);
                        $remaining_des = substr($row['description'],300);
                        echo '<span class="teaser"><p>About:</p> '.nl2br($short_des).'</span>';
                        echo '<a class="read-more-show hide" href=""> ...Read more</a>';
                        echo '<span class="read-more-content">'.nl2br($remaining_des).'</span>';
                        echo '<a class="read-more-hide hide" href="">Read less</a>';   
                        }
                        else{
                            echo '<span class="teaser"><p>About:</p> '.nl2br($row['description']).'</span>';
                        }
                echo '</div>';
                echo '<div id="footer_post">';
                    $unique_key = $row['unique_key'];
                    $profile_details= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE unique_key='$unique_key'"));
                    $user_id_contact=$profile_details['user_id'];
                    $rows = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM follow_table WHERE follow_key='$unique_key' and follower_id='$uid'"));
                    if($uid != $profile_details['user_id']){
//                             if($rows==0){
//                            echo '<a href="javascript:void(0);" class="ajax_reload" data-key="'.$unique_key.'" data-val="0"><div id="post_follow_button">'; echo '<p>Follow</p>'; echo '</div></a>';
//                        } else{
//                          echo '<a href="javascript:void(0);" class="ajax_reload" data-key="'.$unique_key.'" data-val="1"><div id="post_follow_button">'; echo '<p>Unfollow</p>'; echo '</a></div>'; 
//                        }
                echo '<div id="links_public_post">';
                echo '<span id="contact">'; echo '<a href="message.php?id='.$user_id_contact.'">Contact</a>'; echo '</span>';
                echo '<span>'; echo '<a href="sponsor-page.php?key='.$unique_key.'">Know more</a>'; echo '</span>';
                echo '</div>';
                echo '</div>';
                    }
                     else{       
                echo '<div id="links_public_post">';
                echo '<span>'; echo '<a href="sponsor-page.php?key='.$unique_key.'">Know more</a>'; echo '</span>';
                echo '<span id="edit-profile-post"><a href="edit-sponsor-page.php?key='.$unique_key.'" title="Edit my page">Edit</a></span>';         
                echo '</div>';
                echo '</div>';     
                            }
                echo '</div>';
    }
    
    //code for posts of event page
    $query = "SELECT *,( 6371 * acos( cos( radians($user_latitude) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians($user_longitude) ) + sin( radians($user_latitude) ) * sin(radians( lat )) ) ) AS distance FROM event_make_post ORDER BY distance LIMIT $start, $limit";
    $result = mysqli_query($dbc,$query);
    while($row = mysqli_fetch_array($result)){
        $cleantime=date("g:i a", strtotime(substr($row['time'],0,5)));
        $cleandate = date('j F, Y',strtotime($row['date']));
        $user_id_ep = $row['user_id'];
        $profile_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id = '$user_id_ep'"));
        
       echo '<div id="post-box-home" class="shadow">
                <span id="page_name_title">
                    <img id="public_post_image" src='.$profile_details['image_path'] .' onerror=this.src="images/page_default_big.gif">
                </span>
                <span id="public_post_name">
                    <a href="event-page.php?key='.$profile_details['unique_key'].'">'.$profile_details['page_name'].'</a>
                    <p id="date-time">'.$cleandate.' at '.$cleantime.'</p>
                </span>';
            if($uid == $profile_details['user_id']){
                        echo '<div id="delete_post">
                            <form id="delete_post_form" method="post" action="event-page.php?key='.$profile_details['unique_key'].'#delete_post">
                                <input type="hidden" id="delete_rec_id" name="delete_rec_id" value="'.$row['id'].'"/> 
                                <input type="submit" name="delete" value="Delete"/>
                            </form>
                        </div>';
                    }
            $month = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
                    $brochure_path = explode("|",$row['brochure_path']);
                    $prev_img_path = explode("|",$row['prev_img_path']);
                    $brochure_last = sizeof($brochure_path)-2;
                    $prev_img_last = sizeof($prev_img_path)-2;
            echo '<span id="post-display">
                               <p><span class="make_bold">Event name: </span>'.$row['event_name'].'.</p>
                               <p><span class="make_bold">Event Category: </span>'.$row['event_category'].'.</p>
                               <p><span class="make_bold">Event Topic: </span>'.$row['event_topic'].'.</p>
                                <p><span class="make_bold">Estimated reach: </span>'.$row['reach_min'].' - ';
                            if($row['reach_max']>20000){echo "More than 20000";}
                            else { echo $row['reach_max'];}
                            echo '.</p>
                                <p><span class="make_bold">Price range: </span>'.$row['budget_min'].' - '.$row['budget_max'].'.</p>
                            <p><span class="make_bold">Date: </span>'.$row['day'].' '.$month[$row['month']].', '.$row['year'].'.</p> 
                            <p><span class="make_bold">Venue: </span>'.$row['event_venue'].'.</p>
                            <p><span class="make_bold">Summary: </span>'.nl2br($row['event_desc']).'</p>
                            <p><span class="make_bold">About: </span>'.nl2br($row['event_desc_detail']).'</p>
                            <p><span class="make_bold underline_head">Attachments</span></p>';
                            echo '<table><tr>';
                            for($i=0; $i<=$brochure_last; $i++){
                            echo '<td><div class="imgbox shadow">
                                <a href="'.$brochure_path[$i].'"><img class="images_posts" src="'.$brochure_path[$i].'"></a>
                                </div></td>';
                            }
                            echo '</tr></table>';
                            if($prev_img_last!=-1){
                            echo '<p><span class="make_bold underline_head">Photos from previous time this event was held</span></p>            <table>
                                    <tr>';
                            for($i=0; $i<=$prev_img_last; $i++){
                            echo '<td><div class="imgbox shadow">
                                <a href="'.$prev_img_path[$i].'"><img class="images_posts" src="'.$prev_img_path[$i].'"></a>
                                </div></td>';
                            }
                        echo '</tr></table>';
                            }
            echo '</span></div>';
        
        
        
        
    }
        //code for posts of sponsors (posts inside sponsor pages - not about)
    $query = "SELECT * FROM sponsor_make_post ORDER BY id DESC LIMIT $start, $limit";
                
    $result = mysqli_query($dbc,$query);
    while($row = mysqli_fetch_array($result)){
        $p_id = $row['user_id'];
        $id_of_post = $row['id'];
        $data_of_post = $row['post'];
        $row_c = mysqli_fetch_array(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE id=$p_id"));
        $img_pth=$row_c['image_path'];
        $uk = $row_c['unique_key'];
        $bn = $row_c['brand_name'];
        $po_id = $row_c['user_id'];
        $post_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM sponsor_make_post WHERE id = '$id_of_post'"));
        $cleantime=date("g:i a", strtotime(substr($post_details['time'],0,5)));
        $cleandate = date('j F, Y',strtotime($post_details['date']));
        echo '<div id="public_post_sp_posts" class="shadow">';
            echo '<span id="page_name_title">'; 
                echo '<img id="public_post_image" src='.$img_pth.' onerror=this.src="images/page_default_sm.gif">';
            echo '</span>';
            echo '<span id="public_post_name"><a href="sponsor-page.php?key='.$uk.'">'.$bn.'</a>
            <p id="date-time">'.$cleandate.' at '.$cleantime.'</p>
            </span>';
            if($uid == $po_id){
                           echo '<span id="delete_post_spp">
                           <form id="delete_post_form" method="post" action="sponsor-page.php?key='.$uk.'#delete_post">
                           <input type="hidden" id="delete_rec_id" name="delete_rec_id" value="'.$post_details['id'].'"/> 
                           <input type="submit" name="delete" value="Delete"/>
                           </form>
                            </span>';
                        }
            echo '<div id="data_public_post" class="data_public_post_spp">';
                    echo nl2br($data_of_post);
            echo '</div>';
            echo '<div id="footer_post">';
            if($uid != $po_id)
                {
                echo '<div id="links_public_post">';
                    echo '<span id="contact">';
                        echo '<a href="message.php?id='.$po_id.'">Contact</a>';
                    echo '</span>';
                    echo '<span>';
                        echo '<a href="sponsor-page.php?key='.$uk.'">Know more</a>';
                    echo '</span>';
                echo '</div>';
                }
            else
                {
                echo '<div id="links_public_post">';
                    echo '<span>';
                        echo '<a href="sponsor-page.php?key='.$uk.'">Know more</a>';
                    echo '</span>';                    
                echo '</div>';
               
                }
            echo '</div>';
        echo '</div>';

    }
    
    
    
    //code for about in event page
    $query = "SELECT *,( 6371 * acos( cos( radians($user_latitude) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians($user_longitude) ) + sin( radians($user_latitude) ) * sin(radians( lat )) ) ) AS distance FROM event_page ORDER BY distance LIMIT $start, $limit";
    $result = mysqli_query($dbc,$query);
    while($row = mysqli_fetch_array($result)){
        echo '<div id="public_post" class="shadow">';
                echo '<span id="page_name_title">'; echo '<img id="public_post_image" src="'.$row['image_path'].'" onerror=this.src="images/page_default_sm.gif">'; echo '</span>';
                echo '<span id="public_post_name"><a href="event-page.php?key='.$row['unique_key'].'">'.$row['page_name'].'</a></span>'; echo '<br>';
                echo '<div id="data_public_post">';
                echo '<span><p>Category:</p> '.$row['category'].'.</span><br>';
                        $len = strlen($row['description']);
                        if($len>300){
                        $short_des = substr($row['description'],0,300);
                        $remaining_des = substr($row['description'],300);
                        echo '<span class="teaser"><p>About:</p> '.nl2br($short_des).'</span>';
                        echo '<a class="read-more-show hide" href=""> ...Read more</a>';
                        echo '<span class="read-more-content">'.nl2br($remaining_des).'</span>';
                        echo '<a class="read-more-hide hide" href="">Read less</a>';   
                        }
                        else{
                            echo '<span class="teaser"><p>About:</p> '.nl2br($row['description']).'</span>';
                        }
                echo '</div>';
                echo '<div id="footer_post">';
                    $unique_key = $row['unique_key'];
                    $profile_details= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key='$unique_key'"));
                    $user_id_contact=$profile_details['user_id'];
                    $rows = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM follow_table WHERE follow_key='$unique_key' and follower_id='$uid'"));
                    if($uid != $profile_details['user_id']){
//                             if($rows==0){
//                            echo '<a href="javascript:void(0);" class="ajax_reload" data-key="'.$unique_key.'" data-val="0"><div id="post_follow_button">'; echo '<p>Follow</p>'; echo '</div></a>';
//                        } else{
//                          echo '<a href="javascript:void(0);" class="ajax_reload" data-key="'.$unique_key.'" data-val="1"><div id="post_follow_button">'; echo '<p>Unfollow</p>'; echo '</a></div>'; 
//                        }
                echo '<div id="links_public_post">';
                echo '<span id="contact">'; echo '<a href="message.php?id='.$user_id_contact.'">Contact</a>'; echo '</span>';
                echo '<span>'; echo '<a href="event-page.php?key='.$unique_key.'">Know more</a>'; echo '</span>';
                echo '</div>';
                echo '</div>';
                    }
                     else{       
                echo '<div id="links_public_post">';
                echo '<span>'; echo '<a href="event-page.php?key='.$unique_key.'">Know more</a>'; echo '</span>';
                echo '<span id="edit-profile-post"><a href="edit-event-page.php?key='.$unique_key.'" title="Edit my page">Edit</a></span>';         
                echo '</div>';
                echo '</div>';     
                            }
                echo '</div>';
    }
    
}
//     if(isset($_POST['key']) && isset($_POST['val'])){
//            $post_unique_key = $_POST['key'];
//            if($_POST['val']==0){
//                mysqli_query($dbc,"INSERT INTO follow_table (follow_key,user_id) VALUES ('$post_unique_key','$uid')");
//                echo json_encode(array('success'=>1));exit();
//            } else{
//                mysqli_query($dbc,"DELETE FROM follow_table WHERE follow_key = '{$post_unique_key}' and user_id='$uid'");
//                echo json_encode(array('success'=>1));exit();
//            }
//        }
?>
