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
                
            //creating event to delete token from login db after a week  
            $query2 = "CREATE EVENT deleteToken$token ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 WEEK DO DELETE FROM login WHERE token = '$token' ";
            mysqli_query($dbc,$query2);
            
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
        <title>Wurkus</title>
        <link href="assets/css/stylesheets.css" rel="stylesheet">
       <script src="assets/js/jquery.min.js"></script>
        <script type="text/javascript">
             $(function(){
        // Check the initial Poistion of the Sticky Header
        var stickyHeaderTop = $('#suggestion').offset().top;

        $(window).scroll(function(){
                if( $(window).scrollTop() > (stickyHeaderTop-80)  && $(window).width()>970 ) {
                        $('#suggestion').css({position: 'fixed', top: '0px'});
                } else {
                        $('#suggestion').css({position: 'static', top: '0px'});
                }
        });
  });  
            </script>
        </head>
    <body>
        <div id="wrapper">
        
        <div id="nav-stick">
            <?php include 'nav.php';?> 
        </div>
            
            <div id="center_profile">
            <div id="profile" class="pad-top">
                <?php
                echo '<div class="show-post">';
                if(isset($_GET['id'])){
                    $id_post = $_GET['id'];
                }
                else{
                    header('location:index.php');
                }
                if(isset($_GET['id'])){
                        $post_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_make_post WHERE id = '$id_post'"));
                        $profile_id = $post_details['user_id'];
                        $profile_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$profile_id'"));
                            $cleantime=date("g:i a", strtotime(substr($post_details['time'],0,5)));
                            $cleandate = date('j F, Y',strtotime($post_details['date']));
                            echo '<div id="post-box" class="shadow">
                            <span id="page_name_title"> 
                        <img id="public_post_image" src='.$profile_details['image_path'] .' onerror=this.src="images/page_default_big.gif">
                        </span>
                        <span id="public_post_name">
                        <a href="event-page.php?key='.$profile_details['unique_key'].'">
                        '.$profile_details['page_name'].'</a>
                        <p id="date-time">'.$cleandate.' at '.$cleantime.'</p>
                       </span>';
                        $month = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
                        $brochure_path = explode("|",$post_details['brochure_path']);
                        $prev_img_path = explode("|",$post_details['prev_img_path']);
                        $brochure_last = sizeof($brochure_path)-2;
                        $prev_img_last = sizeof($prev_img_path)-2;
                            
                        echo '<span id="post-display">
                               <p><span class="make_bold">Event name: </span>'.$post_details['event_name'].'.</p>
                               <p><span class="make_bold">Event Category: </span>'.$post_details['event_category'].'.</p>
                               <p><span class="make_bold">Event Topic: </span>'.$post_details['event_topic'].'.</p>
                                <p><span class="make_bold">Estimated reach: </span>'.$post_details['reach_min'].' - ';
                            if($post_details['reach_max']>20000){echo "More than 20000";}
                            else { echo $post_details['reach_max'];}
                            echo '.</p>
                                <p><span class="make_bold">Price range: </span>'.$post_details['budget_min'].' - '.$post_details['budget_max'].'.</p>
                            <p><span class="make_bold">Date: </span>'.$post_details['day'].' '.$month[$post_details['month']].', '.$post_details['year'].'.</p> 
                            <p><span class="make_bold">Venue: </span>'.$post_details['event_venue'].'.</p>
                            <p><span class="make_bold">Summary: </span>'.nl2br($post_details['event_desc']).'</p>
                            <p><span class="make_bold">About: </span>'.nl2br($post_details['event_desc_detail']).'</p>
                            <p><span class="make_bold underline_head">Attachments</span></p>';
                            echo '<table><tr>';
                            for($i=0; $i<=$brochure_last; $i++){
                            echo '<td><div class="imgbox shadow">
                                <a href="'.$brochure_path[$i].'"><img src="'.$brochure_path[$i].'"></a>
                                </div></td>';
                            }
                            echo '</tr></table>';
                            if($prev_img_last!=-1){
                            echo '<p><span class="make_bold underline_head">Photos from previous time this event was held</span></p>            <table>
                                    <tr>';
                            for($i=0; $i<=$prev_img_last; $i++){
                            echo '<td><div class="imgbox shadow">
                                <a href="'.$prev_img_path[$i].'"><img src="'.$prev_img_path[$i].'"></a>
                                </div></td>';
                            }
                        echo '</tr></table>';
                            }
                            echo '</span></div>';
                        
                        $j--;
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
                    if(!isset($data_row)){
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
                    if(!isset($data_row)){
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
        <?php mysqli_close($dbc);?>
    </body>
</html>    