<script type="text/javascript">

           

</script>    
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

if(isset($_POST["limit"],$_POST["start"],$_POST["page_key"])){
    $dbc = mysqli_connect($hn,$un,$pd,$db);
    $key= $_POST["page_key"];
    $page_details= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key='$key'"));
    
    $id_page = $page_details['id'];
    $start = $_POST["start"];
    $limit = $_POST["limit"];
    $query = "SELECT * FROM event_make_post WHERE user_id = '$id_page' ORDER BY id DESC LIMIT $start, $limit";
    $result = mysqli_query($dbc,$query);
    
    while($row = mysqli_fetch_array($result)){
         $cleantime=date("g:i a", strtotime(substr($row['time'],0,5)));
         $cleandate = date('j F, Y',strtotime($row['date']));
        echo '<div id="post-box" class="shadow">
                <span id="page_name_title"> 
                        <img id="public_post_image" src='.$page_details['image_path'] .' onerror=this.src="images/page_default_big.gif">
                </span>
                <span id="public_post_name">
                        <a href="event-page.php?key='.$page_details['unique_key'].'">
                        '.$page_details['page_name'].'</a>
                        <p id="date-time">'.$cleandate.' at '.$cleantime.'</p>
                </span>';
                if($uid == $page_details['user_id']){
                    echo '<div id="delete_post">
                           <form id="delete_post_form" method="post" action="event-page.php?key='.$key.'#delete_post">
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
                    <p><span class="make_bold">Price range: </span>'.$row['budget_min'].' - '.$row['budget_max'].'.</p>     <p><span class="make_bold">Date: </span>'.$row['day'].' '.$month[$row['month']].', '.$row['year'].'.</p> 
                    <p><span class="make_bold">Venue: </span>'.$row['event_venue'].'.</p>
                    <p><span class="make_bold">Summary: </span>'.nl2br($row['event_desc']).'</p>
                    <p><span class="make_bold">About: </span>'.nl2br($row['event_desc_detail']).'</p>
                    <p><span class="make_bold underline_head">Attachments</span></p>';   
                    echo '<table><tr>';
                            for($i=0; $i<=$brochure_last; $i++){
                            echo '<td><div class="imgbox shadow">
                                <a href="'.$brochure_path[$i].'"><img src="'.$brochure_path[$i].'"></a>
                                </div></td>';
                            }
                    echo '</tr></table>';
                    if($prev_img_last!=-1){
                            echo '<p><span class="make_bold underline_head">Photos from previous time this event was held</span></p>            
                            <table><tr>';
                        for($i=0; $i<=$prev_img_last; $i++){
                            echo '<td><div class="imgbox shadow">
                                <a href="'.$prev_img_path[$i].'"><img src="'.$prev_img_path[$i].'"></a>
                                </div></td>';
                            }
                            echo '</tr></table>';
                    }
                echo '</span></div>';
    }
}


?>