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
        <title>Search | Wurkus</title>
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
            
            
            //start- ajax for fetching posts
            $(document).ready(function(){ 
                var limit = 8;
                var start = 0;
                var action = 'inactive';
                var url = '?'+"<?php echo $_SERVER["QUERY_STRING"];?>"; 
                function load_posts_data(limit,start)
                {
                    $.ajax({
                        url : "fetch-search.php"+url,
                        method : "POST",
                        data : {limit:limit,start:start},
                        cache : false,
                        success : function(data)
                        {   
                            $('#search-result').append(data);
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
                $(window).scroll(function(){
                    if($(window).scrollTop()+$(window).height()>$("#search-result").height()&& action=="inactive"){
                        action = 'active';
                        start = start + 8;
                        limit = limit + 8;
                        setTimeout(function(){
                            load_posts_data(limit,start);
                        },100);
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
            <?php
            if(isset($_GET['search'])){
                $result_profile =mysqli_query($dbc, "SELECT * FROM public_signup WHERE 1");
                $result_sponsor = mysqli_query($dbc, "SELECT * FROM sponsor_page WHERE 1");
                $result_event = mysqli_query($dbc, "SELECT * FROM event_page WHERE 1");
                $result_event_post = mysqli_query($dbc, "SELECT * FROM event_make_post WHERE 1");
                $array_sponsor = array();
                while ($row = mysqli_fetch_array($result_sponsor, MYSQLI_ASSOC)) {
                    $array_sponsor[] =  $row['brand_name'];
                    $array_sponsorKey[] = $row['unique_key'];
                    $sponsor_imagePath[] = $row['image_path'];
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
                $eventPost_len = sizeof($array_eventPost);
                }
                
            ?>
            <div id="center_search_box">
            <div id="profile">
            <div id="search-result">
                <?php 
                $emptySearch=0;
                if(isset($_GET['search']) && !empty($_GET['search'])){
                    $search_val = strtoupper($_GET['search']);
                    for($i=0;$i<$sponsor_len;$i++){
                        similar_text(strtoupper($array_sponsor[$i]),$search_val,$perc);
                        if($perc>40)
                            $emptySearch=1;
                    }
                    for($i=0;$i<$event_len;$i++){
                        similar_text(strtoupper($array_event[$i]),$search_val,$perc);
                        if($perc>40)
                            $emptySearch=1;
                    }
                    for($i=0;$i<$firstname_len;$i++){
                        similar_text(strtoupper($array_firstname[$i]),$search_val,$perc);
                        if($perc>40)
                            $emptySearch=1;
                    }
                    for($i=0;$i<$eventPost_len;$i++){
                        similar_text(strtoupper($array_event_ost[$i]),$search_val,$perc);
                        if($perc>40)
                            $emptySearch=1;
                    }
                }elseif(isset($_GET['type']) && $_GET['type']=='sponsor'){
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
                        if($perc_loc>20 && $perc_cat>80){
                            $emptySearch=1;
                        }
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
//                    for($i=$minidpage;$i<=$maxidpage;$i++){
//                        $event_page_det = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$i'"));
//                        similar_text(strtoupper($event_page_det['category']),$category,$perc_cat);
//                        if($perc_cat>80)
//                            $emptySearch=1;
//                    }
                    for($i=$minidpost;$i<=$maxidpost;$i++){
                        $event_post_det = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_make_post WHERE id='$i'"));
                        $post_userid=$event_post_det['user_id'];
                        $post_dp = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$post_userid'"));
                        similar_text(strtoupper($event_post_det['event_category']),$category,$perc_cat);
                        similar_text(strtoupper($event_post_det['event_venue']),$loco,$perc_loc);
                        similar_text(strtoupper($event_post_det['event_topic']),$topic,$perc_top);
                        similar_text(strtoupper($event_post_det['tags']),$topic,$perc_tag);
                        if($perc_loc>20 && $perc_tag>20 && $bmin == $event_post_det['budget_min'] && $bmax == $event_post_det['budget_max'] && $rmin == $event_post_det['reach_min'] && $rmax == $event_post_det['reach_max']){
                            $emptySearch=1;
                        }
                    }
                }
                if($emptySearch==0){
                    echo '<div id="search-error"><p>We couldn\'t find anything.
                    <br>
                    <img src="images/sad-smiley.gif"></p>
                    <p>Check if you have entered correct spelling.</p>
                    </div>';
                    
                }
                ?>
               
            </div>
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