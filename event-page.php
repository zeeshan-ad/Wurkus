<?php ob_start();?>
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
        $last_name= ucfirst($lastname_row['last_name']);      
        
        if (isset($_GET['log_out']) && isset($_COOKIE['SNID'])){
                $query= "DELETE FROM login WHERE token= sha1('$b_token')";
                mysqli_query($dbc, $query);
                setcookie('SNID', 1, time()-3600,'/', NULL, NULL, TRUE);
           
            header('location:index.php');
        }
include_once 'accesscontrol.php';
            
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Event Page | Wurkus</title>
        <script src="assets/js/jquery.min.js"></script> 
        <link href="assets/css/stylesheets.css" rel="stylesheet">
        <script>
            
            $('#delete_post_form').click(function(e){
            e.preventDefault();
            var form2 = $(this).parent().serialize();

           $.ajax({
            type: "POST",
            url: form.action,
            data: form2
           });
                 return false;
        });
            
            
            
            
            $(function(){
        // Check the initial Poistion of the Sticky Header
        var stickyHeaderTop = $('#suggestion').offset().top;

        $(window).scroll(function(){
                if( $(window).scrollTop() > (stickyHeaderTop-80)  && $(window).width()>970) {
                        $('#suggestion').css({position: 'fixed', top: '0px'});
                } else {
                        $('#suggestion').css({position: 'static', top: '0px'});
                }
        });
  });  
            //pop-up after clicking show more
             function display_more() {
                document.getElementById('focus_post').style.display = 'block';
                }
             function display_less() {
                document.getElementById('focus_post').style.display = 'none';
             }
            
             //pop-up for closing delete
            function display_less_post() {
                document.getElementById('focus_post_delete').style.display = 'none';
             }
            
            //start- ajax for posts(event page)
            $(document).ready(function(){
                var limit = 3;
                var start = 0;
                var page_key = "<?php echo $_GET['key']; ?>";
                var action = 'inactive';
                function load_posts_events(limit,start,page_key){
                    $.ajax({
                        url : "fetch-event-posts.php",
                        method : "POST",
                        data : {limit:limit,start:start,page_key:page_key},
                        cache : false,
                        success : function(data){
                                $('#show-post').append(data);
                                if(data=='')
                                    action = 'active';
                                else
                                    action = 'inactive';
                            }
                            });
                }
                if(action=='inactive'){
                    action = 'active';
                    load_posts_events(limit,start,page_key);
                }
                $(window).scroll(function(){
                    if($(window).scrollTop()+$(window).height()>$("#show-post").height()&& action=="inactive"){
                        action = 'active';
                        start = start + limit;
                        setTimeout(function(){
                        load_posts_events(limit,start,page_key);
                        },1000);
                    }
                });
            });
            
            </script>
    </head>
    <body>
        <div id="wrapper">
            <?php
            echo '<div id="nav-stick">';
            include 'nav.php';
            echo '</div>';
            $key= $_GET['key'];
            
            $profile_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key='$key'"));
            if($uid != $profile_details['user_id']){
                echo '<div id="space-2"></div>';
            }
            $rows = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM follow_table WHERE follow_key='$key' and follower_id='$uid'"));
            $num_rows = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM follow_table WHERE follow_key='$key'"));
            $follower = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM follow_table WHERE follow_key='$key'"));
            $details=$profile_details['location'];
            $long_details=$profile_details['location'];
            $len=strlen($profile_details['location']);
            if($len>50){
                $details=substr($profile_details['location'],0,45)."...";
            } 
            ?>
            <div id="center_profile">
            <div id="profile">
            <div id="left_main_page" class="shadow">
            <div class="profile-detail" >
                <span id="profile-name-page" >
                <p><?php echo $profile_details['page_name'];?> <span id="location-profile" title="<?php echo $long_details; ?>">(<?php  
                    echo $details;?>)
                </span></p>
                </span>
                <span id="category-profile"><p><?php echo $profile_details['category'];?></p></span>
                <div id="image-box" class="shadow">
                <img src="<?php echo $profile_details['image_path'];?>" onerror="this.src='images/page_default_big.gif'">
                </div>
                        <div id="image-content-gap">
                <?php if($uid == $profile_details['user_id']){
                echo '<span id="followed-by"><button id="open_popup"><span id="num">';
                echo $follower;
                echo '</span>';  
                if($num_rows<=1){
                    echo ' follower';}
                else{ 
                    echo ' followers';}
                echo '</button></span>';
                } ?>
                <span id="profile-website"><a href="http://<?php echo $profile_details['website'];?>" target="_blank"><?php echo $profile_details['website']?></a></span>
                            <span id="about-profile"><p><span class="make_bold">About:</span> <?php
                    $n =strlen($profile_details['description']);
                    if($n<300)
                    echo nl2br($profile_details['description']);
                    else{
                        $short_description = substr($profile_details['description'],0,249).'...';
                        echo nl2br($short_description);
                        echo '<span id="read_more">'; echo '<button onclick="display_more()"> Read more</button>'; echo '</span>';
                        //creating popup on clicking read more
                        echo '<div id="focus_post">';
                            echo '<div id="descrip_post" class="shadow">';
                                     echo '<span id="page_name_title">'; echo '<img class="public_post_image" id="public_post_image_rm" src='.$profile_details['image_path'].' onerror=this.src="images/page_default_f.gif">'; echo '</span>';
                                     echo '<span id="public_post_name_rm"><a href="event-page.php?key='.$key.'">'.$profile_details['page_name'].'</a></span>';
                                     echo '<span class="post_status" id="post_status_rm">'; 
                                     echo '</span>';
                                     echo '<br>';
                                     echo '<div id="data_public_post_rm">';
                                     echo '<span><p>Product category:</p> '.$profile_details['category'].'.</span><br>';
                                     echo '<div id="scroll_rm">';
                                     echo '<span><p>About:</p> '.nl2br($profile_details['description']).'</span>';
                                     echo '</div>';
                                     echo '</div>';
                                     echo '<div id="footer_post_rm">';
                         if($uid != $profile_details['user_id']){
                                    //check if user is following this page
                                    $user_id_contact=$profile_details['user_id'];
                                    if($rows==0){
                                    echo '<a href="event-page.php?key='.$key.'&val=0"><div id="post_follow_button">'; echo '<p>Follow</p>'; echo '</div></a>';
                                 } else{
                                       echo '<a href="event-page.php?key='.$key.'&val=1"><div id="post_follow_button">'; echo '<p>Unfollow</p>'; echo '</div></a>'; 
                                    }
                                    echo '<div id="links_public_post">';
                                    echo '<span id="contact">'; echo '<a href="message.php?id='.$user_id_contact.'">Contact</a>'; echo '</span>';
                                    echo '<span>'; echo '<a href="event-page.php?key='.$key.'">Know more</a>'; echo '</span>';
                                    echo '</div>';
                                    echo '</div>';
                                        }
                                         else{       
                                    echo '<div id="links_public_post">';
                                    echo '<span>'; echo '<a href="event-page.php?key='.$key.'">Know more</a>'; echo '</span>';
                                    echo '<span id="edit-profile-post"><a href="edit-event-page.php?key='.$key.'" title="Edit my page">Edit</a></span>';         
                                    echo '</div>';
                                    echo '</div>';     
                                                }
                                  
                            echo '</div>';
                        echo '<span id="close_popup">'; echo '<button onclick="display_less()">&#10799;</button>';echo'</span>';
                        echo '</div>';
                    }
                    ?></p></span>
                        </div>
                            <?php
                            if($uid != $profile_details['user_id']){
                                $user_id =$profile_details['user_id'];
                                $user_id_contact=$profile_details['user_id'];
                                $user_detail=mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM public_signup WHERE id= '$user_id'"));
                                echo "<div  class='follow-position'>";
                              //check if user is following this page
                                    if($rows==0){
                                    echo '<a href="event-page.php?key='.$key.'&val=0"><div id="post_follow_button">'; echo '<p>Follow</p>'; echo '</div></a></div>';
                                    } else{
                                       echo '<a href="event-page.php?key='.$key.'&val=1"><div id="post_follow_button">'; echo '<p>Unfollow</p>'; echo '</div></a></div>'; 
                                    }
                                 echo "<div  class='contact-position'>";
                              echo '<span id="contact">'; echo '<a href="message.php?id='.$user_id_contact.'">Contact</a>'; echo '</span>'; echo '</div>';
                                echo "<div id='page-owner'>";
                                echo '<p>This page is created under the profile of <a href="admin-profile.php?id='.$user_id.'">'.$user_detail['first_name'].' '.$user_detail['last_name'].'</a>.</p>';
                                echo '</div>';
                            } else{
                                echo '<div id="edit-profile"><a href="edit-event-page.php?key='.$key.'" title="Edit my page">Edit</a></div>';
                                }
                    $page_unique_key =$profile_details['unique_key'];
                if(isset($_GET['val'])){
                    if($_GET['val']==0){
                        $result_notify= mysqli_query($dbc,"INSERT INTO follow_table (followed_id,follow_key,follower_id) VALUES ('$user_id','$page_unique_key','$uid')");
                       header('location:event-page.php?key='.$key);
                    }else{
                        mysqli_query($dbc,"DELETE FROM follow_table WHERE follow_key = '{$page_unique_key}' and follower_id='$uid'");
                        header('location:event-page.php?key='.$key);
                    }
                }
                        ?>
                   <div id="myModal" class="modal">

          <!-- Modal content -->
          <div class="modal-content">
            <span class="close">&times;</span>
            <p id="followPop_head">People who have followed this page</p>
              <?php
              $result= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM follow_table"));
              $result2= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MIN(id) as MinimumID FROM follow_table"));
                $follow_lastid = $result['MaximumID'];
                $follow_firstid = $result2['MinimumID'];
              
              while($follow_firstid<=$follow_lastid){
                    $page_followers= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM follow_table WHERE id='{$follow_firstid}'"));
                  if($page_followers['follow_key']==$page_unique_key){
                    $person_following = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM public_signup WHERE id='{$page_followers['follower_id']}'"));
                    echo '<table class="element-box"><tr>
                    <td id="profile_img_set" class="display_inline_block"> 
                    <img src="'.$person_following['profile_image_path'].'" onerror=\'this.src="images/default_profile_pic.png"\'>
                    </td>
                    <td class="display_inline_block search_element">
                    <p><a href="admin-profile.php?id='.$person_following['id'].'">'.ucfirst($person_following['first_name']).' '.ucfirst($person_following['last_name']).'</a></p>
                    </td>
                    </tr></table>
                            ';  
                  }
                  $follow_firstid++;
              }
              ?>
          </div>
            </div>
            </div>
         </div>
                <?php
                $id_profile = $profile_details['id'];
                if($uid == $profile_details['user_id']){
                echo '<div id="make-post-event" class="shadow">
                <p>Event Coming up? Create a detailed post.</p>
                <div id="create-post"><a href="event-make-post.php?key='.$key.'"><p>Creat a post</p></a></div>
            </div>';
                if(isset($_POST['profile-post'])){
                    $post = $_POST['profile-post'];
                    mysqli_query($dbc,"INSERT INTO sponsor_make_post (post, time, date, user_id) VALUES ('$post', CURTIME(), CURDATE(), '$id_profile')"); 
                }
                }
                
                echo '<div id="show-post">';
                
               if(isset($_POST['delete_rec_id'])){
                   $id= $_POST['delete_rec_id'];
                    
                    echo '<div id="focus_post_delete">';
                        echo '<div id="del_post" class="shadow">';
                            echo '<div id="del_post_head"><p>Delete post</p><span id="close_popup_delete"><button id="butt" onclick="display_less_post()">&#10005</button></span></div>';
                            echo '<div id="del_post_body"><p>If you delete this post you will no longer be able to find it.</p><p id="ays">Are you sure that you want to delete it?</p></div>';
                            echo '<div id ="del_post_options">';
                                echo '<a href="event-page.php?key='.$key.'&del='.$id.'">Yes</a>';
                                echo '<a href="event-page.php?key='.$key.'#delete_post" onclick="display_less_post()">No</a>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                    }
                if(isset($_GET['del'])){
                    $id = $_GET['del'];
                    $del_path=mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_make_post WHERE id='$id'"));
                    $brochure_path = explode("|",$del_path['brochure_path']);
                    $prev_img_path = explode("|",$del_path['prev_img_path']);
                    $brochure_last = sizeof($brochure_path)-2;
                    $prev_img_last = sizeof($prev_img_path)-2;
                    for($i=0; $i<=$brochure_last; $i++){
                        unlink($brochure_path[$i]);
                        }
                    if($prev_img_last!=-1){
                        for($i=0; $i<=$prev_img_last; $i++){
                            unlink($prev_img_path[$i]);
                            }
                    }
                    
                    $res = mysqli_query($dbc,"DELETE FROM event_make_post WHERE id='$id'");
                    if($res){
                    header('location:event-page.php?key='.$key.'#delete_post');
                    }
                }
                
                $post_result = mysqli_query($dbc,"SELECT * FROM event_make_post WHERE user_id = '$id_profile'");
                $post_extract_row = mysqli_num_rows($post_result);
                if($post_extract_row==0){
                        echo '<div id="no-post" class="shadow">
                                <p>No post to display.</p>
                            </div>';
                }
                echo '</div>';
                
                ?>
                </div>
           <div class="suggest-sticky">
                <div id="suggestion">
                <div class="suggested">
                <p>Suggested Sponsor Pages</p>
                <?php
                $result= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM sponsor_page"));
                $result2= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MIN(id) as MinimumID FROM sponsor_page"));
                $lastid = $result['MaximumID'];
                $firstid = $result2['MinimumID'];
                $count=0;
                $temp=0;
                for($i=0;$i<=$lastid;$i++){
                    $random = $random2 = 0;
                    if($random==$random2){
                        $random = rand($firstid,$lastid);
                        $random2 = rand($firstid,$lastid);
                    } 
                    if($random!=$temp){
                    $data_row= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE id='{$random}'"));
                    if(!isset($data_row) || $data_row['user_id']==$uid){
                        continue;
                    } else{
                        echo '<div id="sponsor_suggest">
                        <span id="page_name_title"> 
                        <img id="public_post_image" src='.$data_row['image_path'] .' onerror=this.src="images/page_default_sm.gif">
                        </span>
                        <span id="public_post_name">
                        <a href="sponsor-page.php?key='.$data_row['unique_key'].'">
                        '.$data_row['brand_name'].'</a>
                        <p id="suggest_category">'.$data_row['category'].'</p>
                        </span>
                        </div>';
                        $count++;
                        $temp=$random;
                    }
                    if($count==2){
                        break;
                    }
                }
                }
                ?>
            </div> 
            <div class="suggested-event">
                <p>Suggested Event Pages</p>
                <?php
                $result= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM event_page"));
                $result2= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MIN(id) as MinimumID FROM event_page"));
                $lastid = $result['MaximumID'];
                $firstid = $result2['MinimumID'];
                $count=0;
                $temp=0;
                for($i=0;$i<=$lastid;$i++){
                    $random = $random2 = 0;
                    if($random==$random2){
                        $random = rand($firstid,$lastid);
                        $random2 = rand($firstid,$lastid);
                    } 
                    if($random!=$temp){
                    $data_row= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='{$random}'"));
                    if(!isset($data_row) || $data_row['user_id']==$uid){
                        continue;
                    } else{
                        echo '<div id="sponsor_suggest">
                        <span id="page_name_title"> 
                        <img id="public_post_image" src='.$data_row['image_path'] .' onerror=this.src="images/page_default_sm.gif">
                        </span>
                        <span id="public_post_name">
                        <a href="event-page.php?key='.$data_row['unique_key'].'">
                        '.$data_row['page_name'].'</a>
                        <p id="date-time">'.$data_row['category'].'</p>
                        </span>
                        </div>';
                        $count++;
                        $temp=$random;
                    }
                    if($count==2){
                        break;
                    }
                }
                }
                ?>
            </div>
                    
        </div>
                </div>
        </div>
                </div>
        <script>
            //FOR FOLLOW POP UP
            // Get the modal
            var modal = document.getElementById('myModal');

            // Get the button that opens the modal
            var btn = document.getElementById("open_popup");

            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("close")[0];

            // When the user clicks the button, open the modal 
            btn.onclick = function() {
                modal.style.display = "block";
            }

            // When the user clicks on <span> (x), close the modal
            span.onclick = function() {
                modal.style.display = "none";
            }

            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            //END
            </script>
        <?php mysqli_close($dbc);?>
    </body>
</html>