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
        <script type="text/javascript">
        $(function() {
        $('#crop').Jcrop({ 
            bgColor: 'white',
            bgOpacity:   .4,
            boxWidth: 450, 
            boxHeight: 400, 
            aspectRatio: 1,
            setSelect: [0,0,1000,1000],
            onChange: showCoords,
            onSelect: showCoords
            });
   
        });
            function showCoords(c)
			{
				jQuery('#x1').val(c.x);
				jQuery('#y1').val(c.y);
				jQuery('#w').val(c.w);
				jQuery('#h').val(c.h);
			};
        </script>
        <script>
        //function to close pop-up for delete page 
        function display_less_delpage() {
                document.getElementById('focus_post_delete').style.display = 'none';
             }
            
        //function to open pop-up for update dp    
            function openup() {
                document.getElementById('focus_update_pp').style.display = 'block';
            }
        //function to close pop-up for update dp
            function display_less_update_pp() {
                document.getElementById('focus_update_pp').style.display = 'none';
             }
            
                $(function(){
        // Check the initial Poistion of the Sticky Header
        var stickyHeaderTop = $('#suggestion').offset().top;
                  
            $(window).scroll(function(){
            
                if( $(window).scrollTop() > (stickyHeaderTop-110) && $(window).width()>970) {
                        $('#suggestion').css({position: 'fixed',top: '0px'});
                } else {
                        $('#suggestion').css({position: 'static', top: '0px'});
                }
                });                 
  }); 
           <?php if(isset($_GET['openup'])==1){
            echo 'window.onload = function() {openup();};';}
            ?>
            
            
            //pop-up after clicking show more
             function display_more() {
                document.getElementById('focus_post').style.display = 'block';
                }
             function display_less() {
                document.getElementById('focus_post').style.display = 'none';
             }
        </script>    
    </head>
    <body>
        <div id="wrapper">
            <?php
            //finding the id and deails of the admin which is to be shown in the page
            $admin_id = $_GET['id'];
            $admin_details= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$admin_id'"));
            $first_name_admin = $admin_details['first_name'];
            $last_name_admin = $admin_details['last_name'];
            $bio= $admin_details['bio'];
            $del_dp=$admin_details['profile_image_path'];
            
            //finding number of sponsor pages
            $num_sp_pgs = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE user_id='$admin_id'"));
            
            //finding number of event pages
            $num_ev_pgs = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM event_page WHERE user_id='$admin_id'"));
            
            //finding total number of pages of the user
            $total_pgs = $num_sp_pgs + $num_ev_pgs;
            ?>
            <div id="nav-stick">
                <?php include 'nav.php';?> 
            </div>
            <div class="admin-width">
            <div class="suggest-sticky" id="push_bottom">
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
            <div id="center_profile">
                <div id="profile">
                    <div id="left_main_page" class="shadow">
                        <div class="profile-detail-mp">
                            <?php
                                if($uid==$admin_id){
                                    
                        echo '<div id="image-box-mp" class="shadow">  
                                <div class="refTable">
                                    <div class="refRow">
                                    <div class="refCell">    
                                    <img id="profile_pic_mp" class="fadePic scale" src="'.$admin_details['profile_image_path'].'"
                                    onerror=this.src="images/default_profile_pic_big.png">    
                                    <a onclick="openup()" class="info">Update profile picture</a>    
                                    </div>    
                                    </div>
                                </div>    
                            </div>';
                                }
                                else{
                                    echo '<div id="image-box-mp" class="shadow">
                                    <div class="refTable">
                                    <div class="refRow">
                                    <div class="refCell">
                                    <img id="profile_pic_mp" src="'.$admin_details['profile_image_path'].'"
                                    onerror=this.src="images/default_profile_pic_big.png">
                                    </div>    
                                    </div>
                                </div>    
                                    </div>';
                                }
                                ?>    
                            <div id="image-content-gap-mp"><?php
                                echo '<a id="name-mp" href="admin-profile.php?id='.$admin_id.'">'; echo $first_name_admin; echo ' '.$last_name_admin.'';  echo '</a>';?>
                                <p id="no_of_pgs">Pages held : <?php echo $total_pgs; ?></p>
                                <?php
                                if(!empty($bio)){
                                    echo '<span id="about-profile" class="bio-mp"><p><span class="make_bold">About: </span>';
                                    $length_ab = strlen($bio);
                                    if($length_ab<300)
                                        echo nl2br($bio).'</p></span>';
                                    else{
                                                                
                        $short_description = substr($bio,0,249).'...';
                        echo nl2br($short_description);
                        echo '<span id="read_more">'; echo '<button onclick="display_more()"> Read more</button>'; echo '</span>';
                        //creating popup on clicking read more
                        echo '<div id="focus_post">';
                            echo '<div id="descrip_post" class="shadow">';
                                     echo '<span id="page_name_title">'; echo '<img class="public_post_image" id="public_post_image_rm" src='.$admin_details['profile_image_path'].' onerror=this.src="images/page_default_f.gif">'; echo '</span>';
                                     echo '<span id="public_post_name_rm"><a href="admin-profile.php?id='.$admin_id.'">'.$first_name_admin.' '.$last_name_admin.'</a></span>';
                                     echo '<span class="post_status" id="post_status_rm">'; 
                                    echo '</span>';
                                     echo '<br>';
                                     echo '<div id="data_public_post_rm">';
                                     echo '<span><p>Pages held:</p> '.$total_pgs.'.</span><br>';
                                     echo '<div id="scroll_rm">';
                                      echo '<span><p>About:</p> '.nl2br($bio).'</span>';
                                     echo '</div>';
                                     echo '</div>';
                                     echo '<div id="footer_post_rm">';
                                    if($uid != $admin_id){
                                    
                                    $user_id_contact=$admin_id;
                                    echo '<div id="links_public_post">';
                                    echo '<span id="contact">'; echo '<a href="message.php?id='.$user_id_contact.'">Contact</a>'; echo '</span>';
                                    echo '<span>'; echo '<a href="admin-profile.php?id='.$admin_id.'">Know more</a>'; echo '</span>';
                                    echo '</div>';
                                    echo '</div>';
                                        }
                                         else{       
                                    echo '<div id="links_public_post">';
                                    echo '<span>'; echo '<a href="admin-profile.php?id='.$admin_id.'">Know more</a>'; echo '</span>';
                                    echo '<span id="edit-profile-post" class="dl-oa"><a onclick="display_less(); openAbout();">Edit About</a></span>';         
                                    echo '</div>';
                                    echo '</div>';     
                                                }
                                  
                            echo '</div>';
                        echo '<span id="close_popup">'; echo '<button onclick="display_less()">&#10799;</button>';echo'</span>';
                        echo '</div>';
                                            
                                        
                                    }
                                }
                                ?>
                                
                            </div>
                            <?php
                                if($uid!=$admin_id){
                                echo '<span id="contact-mp"><a href="message.php?id='.$admin_id.'">Contact</a></span>';}
                            ?>    
                        </div>
                            <?php
                            if($uid==$admin_id){
                                     echo '<div id="edit-profile-ap"><a onclick="openAbout()" title="Edit my page">Edit About</a></div>';
                                }
                            ?>
                    </div>
                    <div class="shadow" id="pages-mp">
                        <div class="page-title">
                            <p>My pages</p>
                        </div>
                        <div id="scroll-mp">
                           <?php
                            //storing total row in sponsor_page db by this user   
                            $sponsor_row = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE user_id='$admin_id'"));
                            //storing total row in event_page db by this user
                            $event_row = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM event_page WHERE user_id='$admin_id'"));
                            //storing total row in sponsor_page db   
                            $sponsor_page_row_count = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM sponsor_page"));
                            //storing total row in event_page db   
                            $event_page_row_count = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM event_page"));  
                            $i="1";
                            $j="1";
                            if($sponsor_row == 0 && $event_row == 0){
                                echo '<div id="sorry">';
                                echo '<p>You haven’t created any Event page or Sponsor page.</p>';
                                echo '</div>';
                                echo '<img id="sad-smiley-mp" src="images/sad-smiley.gif">';
                                echo '<div id="sorry-two" class="sorry-two-mp">';
                                echo '<p>Creating a page helps you contact others and them find you.</p>';
                                echo '</div>';
                            }
                            else{
                            if($sponsor_row!=0){
                                $result= mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM sponsor_page");
                                $lastid_row = mysqli_fetch_assoc($result);
                                $lastid = $lastid_row['MaximumID'];
                                echo '<div id="sponsor-heading">';
                                if($sponsor_row==1){
                                    echo '<p>Sponsor page:</p>';
                                } else{
                                    echo '<p>Sponsor pages:</p>';
                                }
                                echo '</div>';
                                echo '<div id="display-pages-mp">';
                                    while($i<=$lastid){
                                        $result = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE id='$i'"));
                                        if($result['user_id']== $admin_id){
                                            $unique_key = $result['unique_key'];
                                            $brandname = $result['brand_name'];
                                            $brand_image = $result['image_path'];
                                            echo '<div id="pgs-box-mp" class="shadow">';
                                            echo '<table id="pgs-mp">';
                                            echo '<tr>';
                                            echo '<td id="profile_img_set_mp">';
                                            echo "<img src='".$brand_image."' onerror=this.src='images/page_default_sm.gif'>";
                                            echo '</td>';
                                            echo '<td id="names-mp">';
                                            echo '<a href="sponsor-page.php?key='.$unique_key.'">'.$brandname.'</a><br>';
                                            if($uid==$admin_id){
                                                echo '<span id="edit-profile-post-mp"><a href="edit-sponsor-page.php?key='.$unique_key.'" title="Edit my page">Edit page</a></span>';
                                                echo '<span id="delete-profile-post-mp">
                                                     <form id="delete_page_form" method="post" action="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'#delete-profile-post-mp">
                                                     <input type="hidden" id="delete_page_id" name="delete_page_id" value="'.$result['id'].'"/>
                                                     <input id="delete_page_sub" type="submit" name="delete" title="Delete my page" value="Delete page"/>
                                                     </form>
                                                </span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            
                                            echo '</table>';
                                            if($uid==$admin_id){
                                            echo '<table id="change_admin_mp">';
                                                echo '<tr>';
                                                    echo '<td id="pg-role-mp">';
                                                        echo 'Change admin:';
                                                    echo '</td>';
                                                    echo '<td>';
                                                         echo '<form method="post" action="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'">';
                                                                echo '<input  id="change_admin_ip" name="change_admin_ip" type="text" placeholder="Enter email" required>';
                                                    echo '</td>';
                                                    echo '<td>';
                                                            echo '<input id="make_admin_button_submit" type="submit" value="Change admin"/>';
                                                    echo '</td>';
                                                        echo '</form>';
                                                echo '</tr>';
                                       
                                            echo '</table>';
                                            }
                                            echo '</div>';
                                            }
                                        $i++;
                                    }
                                echo '</div>';
                            }
                                if($event_row!=0){
                                $result= mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM event_page");
                                $lastid_row = mysqli_fetch_assoc($result);
                                $lastid = $lastid_row['MaximumID'];
                                echo '<div id="sponsor-heading">';
                                if($event_row==1){
                                    echo '<p>Event page:</p>';
                                } else{
                                    echo '<p>Event pages:</p>';
                                }
                                echo '</div>';
                                echo '<div id="display-pages-mp">';
                                    while($j<=$lastid){
                                        $result = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$j'"));
                                        if($result['user_id']== $admin_id){
                                            $unique_key = $result['unique_key'];
                                            $pagename = $result['page_name'];
                                            $brand_image = $result['image_path'];
                                            echo '<div id="pgs-box-mp" class="shadow">';
                                            echo '<table id="pgs-mp">';
                                            echo '<tr>';
                                            echo '<td id="profile_img_set_mp">';
                                            echo "<img src='".$brand_image."' onerror=this.src='images/page_default_sm.gif'>";
                                            echo '</td>';
                                            echo '<td id="names-mp">';
                                            echo '<a href="event-page.php?key='.$unique_key.'">'.$pagename.'</a><br>';
                                            if($uid==$admin_id){
                                                echo '<span id="edit-profile-post-mp"><a href="edit-event-page.php?key='.$unique_key.'" title="Edit my page">Edit page</a></span>';
                                                echo '<span id="delete-profile-post-mp">
                                                 <form id="delete_page_form" method="post" action="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'#delete-profile-post-mp">
                                                 <input type="hidden" id="delete_page_id_ep" name="delete_page_id_ep" value="'.$result['id'].'"/>
                                                 <input id="delete_page_sub" type="submit" name="delete" title="Delete my page" value="Delete page"/>
                                                 </form>
                                                
                                                
                                                
                                                </span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            
                                            echo '</table>';
                                            if($uid==$admin_id){
                                            echo '<table id="change_admin_mp">';
                                                echo '<tr>';
                                                    echo '<td id="pg-role-mp">';
                                                        echo 'Change admin:';
                                                    echo '</td>';
                                                    echo '<td>';
                                                         echo '<form method="post" action="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'">';
                                                                echo '<input  id="change_admin_ip_ep" name="change_admin_ip_ep" type="text" placeholder="Enter email">';
                                                    echo '</td>';
                                                    echo '<td>';
                                                            echo '<input id="make_admin_button_submit" type="submit" value="Change admin"/>';
                                                    echo '</td>';
                                                        echo '</form>';
                                                echo '</tr>';
                                       
                                            echo '</table>';
                                            }
                                            echo '</div>';
                                            }
                                        $j++;
                                    }
                                echo '</div>';
                            }
                            }
                            
                             if(isset($_POST['delete_page_id'])){
                                $id= $_POST['delete_page_id'];
                                 echo '<div id="focus_post_delete">';
                                    echo '<div id="del_post_mp" class="shadow">';
                                    echo '<div id="del_post_head"><p>Delete page</p><span id="close_popup_delete"><button id="butt" onclick="display_less_delpage()">&times;</button></span></div>';
                                    echo '<div id="del_post_body"><p>If you delete this page then you will no longer be able to find it. Deleting a page makes you lose all the followers and posts.</p><p id="ays">Are you sure you want to delete this page?</p></div>';
                                    echo '<div id ="del_post_options">';
                                        echo '<a href="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'&del='.$id.'">Yes</a>';
                                        echo '<a href="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'#delete-profile-post-mp" onclick="display_less_delpage()">No</a>';
                                    echo '</div>';
                                    echo '</div>';
                                 echo '</div>';   
                             }
                            
                             if(isset($_GET['del'])){
                                $id = $_GET['del'];
                                $result_for_follow = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE id='$id'"));
                                unlink($result_for_follow['image_path']);
                                $u_key = $result_for_follow['unique_key'];
                                $res1 = mysqli_query($dbc,"DELETE FROM follow_table WHERE follow_key='$u_key'"); 
                                $res2 = mysqli_query($dbc,"DELETE FROM sponsor_make_post WHERE user_id='$id'");
                                $res3 = mysqli_query($dbc,"DELETE FROM sponsor_page WHERE id='$id'"); 
                                if($res1 && $res2 && $res3){
                                header('location:admin-profile.php?id='.$admin_id.'');
                                }
                             }
                            
                            if(isset($_POST['delete_page_id_ep'])){
                                $id= $_POST['delete_page_id_ep'];
                                 echo '<div id="focus_post_delete">';
                                    echo '<div id="del_post_mp" class="shadow">';
                                    echo '<div id="del_post_head"><p>Delete page</p><span id="close_popup_delete"><button id="butt" onclick="display_less_delpage()">&#10005</button></span></div>';
                                    echo '<div id="del_post_body"><p>If you delete this page then you (or any other admin(s) of this page) will no longer be able to find it. Deleting a page makes you lose all the followers and posts. </p><p id="ays">Are you sure that you want to delete it?</p></div>';
                                    echo '<div id ="del_post_options">';
                                        echo '<a href="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'&del_ep='.$id.'">Yes</a>';
                                        echo '<a href="admin-profile.php?id='.$admin_id.'&key='.$unique_key.'#delete-profile-post-mp" onclick="display_less_delpage()">No</a>';
                                    echo '</div>';
                                    echo '</div>';
                                 echo '</div>';   
                             }
                
                            if(isset($_GET['del_ep'])){
                                $id = $_GET['del_ep'];
                                $result_for_follow = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$id'"));
                                unlink($result_for_follow['image_path']);
                                $u_key = $result_for_follow['unique_key'];
                                $res1 = mysqli_query($dbc,"DELETE FROM follow_table WHERE follow_key='$u_key'");
                                $res2 = mysqli_query($dbc,"DELETE FROM event_make_post WHERE user_id='$id'");
                                $res3 = mysqli_query($dbc,"DELETE FROM event_page WHERE id='$id'");
                                if($res1 && $res2 && $res3){
                                header('location:admin-profile.php?id='.$admin_id.'');
                                }
                             }
                            if(isset($_POST['change_admin_ip'])){
                                $email_id = $_POST['change_admin_ip'];
                                $u_key = $_GET['key'];
                                $flag=0;
                                echo '<div id="focus_post_delete">';
                                    echo '<div id="admin_post_mp" class="shadow">';
                                    echo '<div id="del_post_head"><p>Change admin</p><span id="close_popup_delete"><button id="butt" onclick="display_less_delpage()">&#10005</button></span></div>';
                                    //checking if email-id given by the user is in the db or not
                                    $result= mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM public_signup");
                                    $lastid_row = mysqli_fetch_assoc($result);
                                    $lastid = $lastid_row['MaximumID'];
                                    $j=$lastid;
                                    $i=1;
                                    global $new_id;
                                    global $new_admin_name;
                                    while($i<=$j){
                                        $result = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$i'"));
                                        if($result['id']== $i){
                                        $email = $result['email'];
                                            if($email_id==$email){  //'$email' is the email is from db
                                                $flag=1;            //'$email_id' is the email given by user in admin-profile.php
                                                $new_id= $result['id']; //storing the id and name of the new admin
                                                $new_admin_name =$result['first_name'];
                                            }    
                                    
                                        }
                                        $i++;
                                    }
                                    if($flag==0){
                                        echo '<div id="admin_post_body" style="border:none;"><p>The email '.$email_id.' is not registered with us. Please enter a valid email id and try again.</p></div>';
                                    }
                                    else{
                                        echo '<div id="admin_post_body"><p>If you make <a href="admin-profile.php?id='.$new_id.'">'.$new_admin_name.'</a> the admin of this page then you will lose the right to control it.</p><p>Are you sure that you want to pass this authority?</p></div>';
                                        echo '<div id ="del_post_options">';
                                            echo '<a href="admin-profile.php?id='.$admin_id.'&unique_key='.$u_key.'&new_id='.$new_id.'">Yes</a>';
                                            echo '<a href="" onclick="display_less_delpage()">No</a>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                echo '</div>';
                                
                            }
                            
                            if(isset($_GET['new_id'])){
                                $new_id = $_GET['new_id'];
                                $u_key = $_GET['unique_key'];
                                $query = "UPDATE sponsor_page SET user_id='$new_id' WHERE unique_key='$u_key'";
                                $result_change = mysqli_query($dbc,$query);
                                if($result_change){
                                    header('location:admin-profile.php?id='.$admin_id.'');
                                }  
                            }
                            
                            if(isset($_POST['change_admin_ip_ep'])){
                                $email_id = $_POST['change_admin_ip_ep'];
                                $u_key = $_GET['key'];
                                $flag=0;
                                echo '<div id="focus_post_delete">';
                                    echo '<div id="admin_post_mp" class="shadow">';
                                    echo '<div id="del_post_head"><p>Change admin</p><span id="close_popup_delete"><button id="butt" onclick="display_less_delpage()">&#10005</button></span></div>';
                                    //checking if email-id given by the user is in the db or not
                                    $result= mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM public_signup");
                                    $lastid_row = mysqli_fetch_assoc($result);
                                    $lastid = $lastid_row['MaximumID'];
                                    $j=$lastid;
                                    $i=1;
                                    global $new_id;
                                    global $new_admin_name;
                                    while($i<=$j){
                                        $result = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$i'"));
                                        if($result['id']== $i){
                                        $email = $result['email'];
                                            if($email_id==$email){  //'$email' is the email is from db
                                                $flag=1;            //'$email_id' is the email given by user in admin-profile.php
                                                $new_id= $result['id']; //storing the id and name of the new admin
                                                $new_admin_name =$result['first_name'];
                                            }    
                                    
                                        }
                                        $i++;
                                    }
                                    if($flag==0){
                                        echo '<div id="admin_post_body"><p>The email '.$email_id.' is not registered with us. Please enter a valid email id and try again.</p></div>';
                                    }
                                    else{
                                        echo '<div id="admin_post_body"><p>If you make <a href="admin-profile.php?id='.$new_id.'">'.$new_admin_name.'</a> the admin of this page then you will lose the right to control it.</p><p>Are you sure that you want to pass this authority?</p></div>';
                                        echo '<div id ="del_post_options">';
                                            echo '<a href="admin-profile.php?id='.$admin_id.'&unique_key='.$u_key.'&new_id_ep='.$new_id.'">Yes</a>';
                                            echo '<a href="" onclick="display_less_delpage()">No</a>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                echo '</div>';
                                
                            }
                            
                            if(isset($_GET['new_id_ep'])){
                                $new_id = $_GET['new_id_ep'];
                                $u_key = $_GET['unique_key'];
                                $query = "UPDATE event_page SET user_id='$new_id' WHERE unique_key='$u_key'";
                                $result_change = mysqli_query($dbc,$query);
                                if($result_change){
                                    header('location:admin-profile.php?id='.$admin_id.'');
                                }  
                            }
                            //pop up for updating profile picture
                            echo '<div id="focus_update_pp">';
                                echo '<div id="admin_post_mp" class="shadow">';
                                echo '<div id="del_post_head"><p>Update profile picture</p><span id="close_popup_delete"><button id="butt" onclick="display_less_update_pp()">&#10005</button></span></div>';
                                echo '<form id="admin-form" method="post" enctype="multipart/form-data" action="admin-profile.php?del_dp='.$del_dp.'">
                                <p id="cap">Choose a picture</p>
                                <input name="profileimage" id="profileimage" type="file" accept="image/*" > 
                                <input id="submit_pp"type="submit" value="Upload">
                                </form>';
                                echo '</div>';    
                            echo '</div>';
                            if(isset($_FILES['profileimage']) && isset($_GET['del_dp'])){
                                unlink($_GET['del_dp']);
                                $image_path = "user_upload/profile_images/"; 
                                $query ="UPDATE public_signup set profile_image_path = '$image_path' WHERE id='$uid'";
                                $result_event = mysqli_query($dbc,$query);
                                $image_name = $_FILES['profileimage']['name'];
                                $temp_image_path = "user_upload/temp_image/"; //image path
                                $temp_image_name = time().$image_name; //renaming the file here
                                setcookie('temp_image_name',$temp_image_name,time()+60*60*24*7, '/', NULL, NULL, TRUE);
                                setcookie('id',$uid,time()+60*60*24*7, '/', NULL, NULL, TRUE);
                                move_uploaded_file($_FILES['profileimage']['tmp_name'], $temp_image_path.$temp_image_name);
                                echo '<div id="focus">';
                                echo '<div id="img_crop" class="shadow">';
                                echo '<div id="crop-head">';
                                echo '<p>Selection crop</p>';
                                echo '</div>';
                                echo '<div id="image_inside">';
                                echo "<img src='$temp_image_path$temp_image_name' id='crop'/>";
                                echo '</div>';
                                echo '<div id="border_crop">';
                //              This is the form that our event handler fills
                                echo '<form method="post" class="coords" action="admin-profile.php">
                                    <input type="text" id="x1" name="x1" />
                                    <input type="text" id="y1" name="y1" />
                                    <input type="text" id="w" name="w" />
                                    <input type="text" id="h" name="h" />';
                                echo '<input type="submit" value="Save">';
                                echo '</form>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            if(isset($_POST['x1']) && isset($_POST['y1']) && isset($_POST['w']) && isset($_POST['h'])){
                                    $x1 =$_POST['x1'];
                                    $y1 = $_POST['y1'];
                                    $w = $_POST['w'];
                                    $h = $_POST['h'];
                                $temp_image_name= $_COOKIE['temp_image_name'];
                                $unique_key= $_COOKIE['unique_key'];    
                                $temp_image_path = "user_upload/temp_image/"; //image path
                                $image_path = "user_upload/profile_images/";
                                $targ_w = $targ_h = 200;
                                $newfilename = $image_path.$temp_image_name;    
                                $src = $temp_image_path.$temp_image_name;
                                $ext=pathinfo($src, PATHINFO_EXTENSION); 
                                if (($ext == 'jpg') || ($ext=='jpeg')) {
                                    $image_p = imagecreatetruecolor($targ_w, $targ_h);
                                    $image = imagecreatefromjpeg($src);
                                    imagecopyresampled($image_p, $image,0,0, $x1, $y1,$targ_w,$targ_h, $w,$h);
                                    imagejpeg($image_p, $newfilename);
                                } elseif ($ext == 'gif') {
                                    $image_p = imagecreatetruecolor($targ_w, $targ_h);
                                    $image = imagecreatefromgif($src);
                                    imagecopyresampled($image_p, $image, 0, 0, $x1,$y1,
                                                    $targ_w,$targ_h,$w,$h);
                                    imagegif($image_p, $newfilename);
                                } elseif ($ext == 'png') {
                                    $image_p = imagecreatetruecolor($targ_w, $targ_h);
                                    $image = imagecreatefrompng($src);
                                    imagecopyresampled($image_p, $image, 0, 0, $x1,$y1,
                                                    $targ_w,$targ_h,$w,$h);
                                    imagepng($image_p, $newfilename);    
                                }   
                                    if(mysqli_query($dbc,"UPDATE public_signup set profile_image_path = '$image_path$temp_image_name' WHERE id='$uid'")){
                                    header('location:admin-profile.php?id='.$uid.'');
                                    setcookie('temp_image_name', 1, time()-3600,'/', NULL, NULL, TRUE);
                                    setcookie('id', 1, time()-3600,'/', NULL, NULL, TRUE);
                                    unlink($temp_image_path.$temp_image_name);
                                    }
                            }
                            ?>
                        </div>    
                    </div>
                </div>
            </div>
            </div>
    </div> 
    </body>
</html>