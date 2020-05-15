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
<?php
        $query="SELECT * FROM public_signup WHERE id= '$uid'";
        $result= mysqli_query($dbc,$query);
        $detail_row= mysqli_fetch_assoc($result);
        $profile_image_path= $detail_row['profile_image_path'];
        $json_search_array = array();
        $query_search_sponsor= "SELECT * from sponsor_page";
        $query_search_event= "SELECT * from event_page";
        $query_search_event_post= "SELECT * from event_make_post";
        $search_result_sponsor = mysqli_query($dbc, $query_search_sponsor);
        $search_result_event = mysqli_query($dbc, $query_search_event);
        $search_result_make_post = mysqli_query($dbc, $query_search_event_post);
        $search_array = array();
        while($row = mysqli_fetch_assoc($search_result_sponsor)){
            $json_search_array[] = $row;
        }
        while($row2 = mysqli_fetch_assoc($search_result_event)){
            $json_search_array[] = $row2;
        }
        while($row3 = mysqli_fetch_assoc($search_result_make_post)){
            $json_search_array[] = $row3;
        }
        $search_json= json_encode($json_search_array);
?>
<script>
/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {

    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}
function myFunction(uid) {
    document.getElementById("myDropdown").classList.toggle("show");
    $.ajax({
            url: "update-notify.php",
            type: "POST",
            cache : false,
            data: { 'username': uid },                   
            success : function(data){
                        console.log(data);
            }
        });
}
    function openAbout(){
        document.getElementById("aboutBgFocus").style.display="block";
    }
    function closeAbout(){
        document.getElementById("aboutBgFocus").style.display="none";
    }
//    setInterval("UpdateNav()",3000);   
//            function UpdateNav() {
//            var xhttp = new XMLHttpRequest();
//            xhttp.onreadystatechange = function() {
//                if(this.readyState==4 && this.status ==200){
//                    document.getElementById("nav-bar").innerHTML = this.responseText;
//                }
//            };    
//            xhttp.open("GET","nav.php", true);
//            xhttp.send();
//                
//                   
//                
//            }
    
    
    
            //start- ajax for fetching notifications
            $(document).ready(function(){ 
                var limit = 7;
                var start = 0;
                var action = 'inactive';
                
                function load_posts_data(limit,start)
                {
                    $.ajax({
                        url : "fetch-notifications.php",
                        method : "POST",
                        data : {limit:limit,start:start},
                        cache : false,
                        success : function(data)
                        {
                            $('#notify-body').append(data);
                            if(data=='')
                                action = 'active';
                            else
                                action = 'inactive';
                        }   
                    });
                }
                if(action=='inactive'){
                    action = 'active';
                    load_posts_data(limit,start);
                }
                var height_load = ($('#notify-body').height());
                height_load = height_load/2.5;
                $('#notify-body').scroll(function(){
//                    console.log('scrollTop:'+$('#notify-body').scrollTop());
//                    console.log('height_load:'+height_load);
                    if($('#notify-body').scrollTop()>height_load && action=="inactive"){
                        height_load=height_load*1.5;
                        action = 'active';
                        start = start + 7;
                        limit = limit + 7;
                        setTimeout(function(){
                            load_posts_data(limit,start);
                        },100);
                    }
                });
                
            });
    
</script>
<div class="nav" id="nav-bar"> 
            <div class="tm-nav">
                <table>
                    <tr>
                        <td id="drop-arrow">
                            <ul>
                                <li class="dropdown">
                                    <a class="dropbtn"><img src="images/down.gif"></a>
                                    <div class="dropdown-content">
                                        <a href="admin-profile.php?id=<?php echo $uid ?>">Manage pages</a>
                                        <a href="admin-profile.php?id=<?php echo $uid ?>&openup=1">Change Profile Picture</a>
                                        <a href="change-password.php?id=<?php echo $uid ?>">Change Password</a>
                                        <a onclick="openAbout()">Edit About</a>
                                        <a href="how-it-works.php">How It Works</a>
                                        <a href="help.php">Help Center</a>
                                        <a href="help.php">Report a Problem</a>
                                        <a href="privacy-policy.php">Privacy Policy</a>
                                        <a href="terms.php">Terms</a>
                                        <a href="?log_out">Logout</a>
                                    </div>
                                </li>
                            </ul>
                        </td>
                        <td id="firstname">
                        <a href="admin-profile.php?id=<?php echo $uid ?>" title="Profile"><?php echo $first_name; ?></a>
                        </td>
                        <td id="profile-img">
                            <img src="<?php echo $profile_image_path; ?>" onerror='this.src="images/default_profile_pic.png"'>
                        </td>
                    </tr>
                </table>
            </div>
          <div class="icons_tmnav">
                <table>
                    <tr>
                        <td class="three_icons"><a href="home.php"><img src="images/home.png" alt="home_icon"></a>
                        </td>
                        <?php
                        $inbox[0]= null;
                        $from_id = $uid;
                        $message_db_last_id_array = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MAX(id) as MaximumID FROM message"));
                        $message_db_last_id= $message_db_last_id_array['MaximumID'];
                        $i=$message_db_last_id;
                        $j=0;
                        $inbox_present=0; 
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
                        if($inbox[0]!=null){ 
                            $FinalFromId_array= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MAX(id) as MaximumID FROM public_signup"));
                            $FinalFromId_array_msg= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT MAX(id) as MaximumID FROM message"));
                            $StartFromId_array =  mysqli_fetch_assoc(mysqli_query($dbc, "SELECT MIN(id) as MinimumID FROM public_signup")); 
                            $StartFromId = $StartFromId_array['MinimumID'];
                            $FinalFromId = $FinalFromId_array['MaximumID'];
                            $FinalFromId_msg = $FinalFromId_array_msg['MaximumID'];
                            $numMsgNotify=0;
                            $start=0;
                            While($start<=$FinalFromId_msg){
                                $res_msg= mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM message WHERE to_id ='$uid' and from_id='$StartFromId' and opened='0'"));
                                if($res_msg){
                                    $numMsgNotify++;
                                }
                                $StartFromId++;
                                $start++;
                            }
                            if($numMsgNotify!=0){echo'<a href="message.php?id='.$inbox[0].'"><div id="msg_alert">'.$numMsgNotify.'</div></a>';}
                        echo '<td class="three_icons"><a href="message.php?id='.$inbox[0].'"><img src="images/send.png" alt="message_icon"></a>
                        </td>';
                        }
                        else{
                           echo '<td class="three_icons"><a href="message.php"><img src="images/send.png" alt="message_icon"></a>
                        </td>'; 
                        }
                        
                        ?>
                        <td class="three_icons">
                            <ul>
                                <li class="dropdownClick">
                                <a onclick="myFunction(<?php echo $uid; ?>)" class="dropbtnClick"><img src="images/notification.png" alt="notification_icon"></a>
                                    <div id="myDropdown" class="shadow dropdown-contentClick notify-dropdown">
                                        <div id="notify-head"><p>Notification</p></div>
                                        <div id="notify-body">
                                    <?php
                                    global $numNotify;
                                    $numNotify = 0; 
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
                                        $id_following_spon = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM sponsor_page WHERE unique_key='$page_unique_key'"));
                                        $id_following_event = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM event_page WHERE unique_key='$page_unique_key'"));
                                        if($id_following_spon){
                                            $notify_to = $id_following_spon['user_id'];
                                        }
                                        if($id_following_event){
                                            $notify_to = $id_following_event['user_id'];
                                        }
                                        $followedById = $follow_table['follower_id'];
                                        $followedBy = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM public_signup WHERE id='$followedById'"));
                                        if($uid == $notify_to){
                                            $notification=1;
                                            if($follow_table['notify_check']==0){
                                                $numNotify++;
                                            }
                                        }
                                        }
                                        $notify_lastid--;
                                    }
                                            }
                                            if($notification==0){
                                                echo '<div id="no_notify">';
                                                echo '<p>There is no notification.</p>';
                                                echo '</div>';
                                            }
                                     ?>
                                    </div>
                                    </div>
                                </li>
                            </ul>
                        </td>
                        <a onclick="myFunction(<?php echo $uid; ?>)" class="dropbtnClick"><?php if($numNotify!=0){echo'<div id="notify_alert">'.$numNotify.'</div>';}?></a>
                    </tr>    
                </table>        
            </div> 
                
            <ul>
                <li id="center"><a href="home.php"><img src="images/w_logo.gif"></a></li>
            </ul>
            <div class="search_bar">
                <?php
                if(isset($_GET["search_val"])){
                    $search_val=$_GET["search_val"];
                    header('Location:search.php?search='.$search_val.'');
                }
                ?>
            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <table id="search_table">
                    <td class="search-container">
                    <input type="text" id="search-bar-conatiner" name="search_val" placeholder="Search events or sponsors">
                    </td>
                    <td><input type="submit" name="submit" value="">
                    </td>
                </table>
            </form>
            </div>
    <div id="aboutBgFocus">
    <div id="aboutUpdate">
        <div id="aboutHead">Edit About
        <span id="closeUpdate" class="navclose" onclick="closeAbout();">&times;</span></div>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <textarea name="about_user" id="about_user" placeholder="Let others Know about yourself.." required></textarea>
            <input id="submit_about_user" type="submit" value="Submit">
        </form>
    </div>
       </div>
</div>
<?php
if(isset($_POST['about_user'])){
    $about_user =$_POST['about_user'];
    mysqli_query($dbc, "UPDATE public_signup SET bio='$about_user' WHERE id='$uid'");
    header('location:admin-profile.php?id='.$uid);
}
?>