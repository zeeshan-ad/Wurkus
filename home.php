<?php ob_start();
?>
<?php
        require_once 'functions.php';
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
         $query="SELECT * FROM public_signup WHERE id= '$uid'";
         $result= mysqli_query($dbc,$query);
         $firstname_row= mysqli_fetch_assoc($result);
         $first_name= $firstname_row['first_name'];
         $activate = $firstname_row['activate_key'];
         $email = $firstname_row['email'];
        
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
    if(isset($_GET['send_link'])){
        sendLink();
    }
        include_once 'accesscontrol.php';
        ?>
<!doctype html>
<html lang="en">
    <head>
        <meta name="description" content="Find sponsors and Event. Wurkus is a platform that allows sponsors and events find their correct match. Publish your event.">
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Wurkus</title>
        <link href="assets/css/stylesheets.css" rel="stylesheet">
       <script src="assets/js/jquery.min.js"></script>
        <script>
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                window.location = "http://m.wurkus.com/home.php"; 
            }
        </script>
        <script type="text/javascript">
            function displayForm(c){
                if(c.value== "event"){
                    document.getElementById("events-form").style.display = 'block';
                    document.getElementById("filter-form").style.height = '660px';
                    document.getElementById("category").style.display = 'none';
                }
                else{
                    document.getElementById("events-form").style.display = 'none';
                    document.getElementById("filter-form").style.height = '305px';
                    document.getElementById("category").style.display = 'block';
                }
            
            }
            function filterForm(c){
                if(c.value=="1"){
                    document.getElementById("filter-form").style.display="none";
                    document.getElementById("suggestion").style.display="block";
                    c.value=0;
                }else{
                   document.getElementById("filter-form").style.display="block";
                    document.getElementById("suggestion").style.display="none";
                    c.value=1; 
                }
            }
         $(function(){
        // Check the initial Poistion of the Sticky Header
        var stickyHeaderTop = $('#suggestion').offset().top;
                  
            $(window).scroll(function(){
            
                if( $(window).scrollTop() > (stickyHeaderTop-110) && $(window).width()>970) {
                        $('#suggestion').css({position: 'fixed',top: '0px', right: '0px'});
                } else {
                        $('#suggestion').css({position: 'static', top: '0px', right: '0px'});
                }
                });                 
  });       
            function openPost(postName,elmnt) {
              var i, tabcontent, tablinks;
              tabcontent = document.getElementsByClassName("tabcontent");
              for(i=0; i < tabcontent.length; i++){
                  tabcontent[i].style.display = "none";
              }
              tablinks = document.getElementsByClassName("tablink");
              for(i=0; i < tablinks.length; i++){
                  tablinks[i].style.backgroundColor= "";
              }
              document.getElementById(postName).style.display= "block";
          }  
          
            function borderTabsT(){
                document.getElementById("tabs_li_trending").style.borderBottom = "4px solid #ffc107";
                 document.getElementById("tabs_li_followed").style.borderBottom = "none";
            }
            
            function borderTabsF(){
                 document.getElementById("tabs_li_followed").style.borderBottom = "4px solid #ffc107";
                 document.getElementById("tabs_li_trending").style.borderBottom = "none";
            }
                
       
          $(".underlinetoggle").click(function () {
                $(this).siblings().removeClass('underline');
                $(this).toggleClass('underline');
             });
            // Get the element with id="defaultOpen" and click on it
            jQuery(function(){
                jQuery('#defaultOpen').click();
                });
            
            //going to the top of the page on click of trendingand followed anchor tags
            $("a[href='#top']").click(function() {
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
                });
            //code for read more problem in home page
            var val = 0;
            function changeValFollowed(){
                val = 1;
            }
            function changeValTrending(){
                val = 0;
            }
            
            
            //start- ajax for posts(trending)
            $(document).ready(function(){ 
                var limit = 2;
                var start = 0;
                var action = 'inactive';
                
                function load_posts_data(limit,start)
                {
                    $.ajax({
                        url : "fetch-trending.php",
                        method : "POST",
                        data : {limit:limit,start:start},
                        cache : false,
                        success : function(data)
                        {
                            $('#home-post-trending').append(data);
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
                    if($(window).scrollTop()+$(window).height()>$("#home-post-trending").height()&& action=="inactive"){
                        action = 'active';
                        start = start + limit;
                        setTimeout(function(){
                            load_posts_data(limit,start);
                        },100);
                    }
                });
                
            });
            
            //end- ajax for posts(trending)
            
            //start- ajax for posts(followed)
            $(document).ready(function(){
                var limit_c = 3;
                var start_c = 0;
                var action_c = 'inactive';
                
                function load_posts_data_c(limit_c,start_c)
                {
                    $.ajax({
                        url : "fetch-followed.php",
                        method : "POST",
                        data : {limit_c:limit_c,start_c:start_c},
                        cache : false,
                        success : function(data)
                        {
                            $('#home-post-followed').append(data);
                            if(data=='')
                                action_c = 'active';
                            else
                                action_c = 'inactive';
                        }   
                    });
                }
                if(action_c=='inactive'){
                    action_c = 'active';
                    load_posts_data_c(limit_c,start_c);
                }
                $(window).scroll(function(){
                    if(($(window).scrollTop()+$(window).height()>$("#home-post-followed").height()&& action_c=="inactive")&&val==1){
                        action_c = 'active';
                        start_c = start_c + 3;
                        limit_c = limit_c + 3;
                        setTimeout(function(){
                            load_posts_data_c(limit_c,start_c);
                        },100);
                    }
                });
                
            });
             
        </script>
        <style>
            .sticky{
                position:fixed;
                top: 110px;
            }
            
            #suggestion{
                margin-right: 13px;
                margin-top: 130px;
            }
            </style>
    </head>
    <body>
        <div id="wrapper">
            <div id="nav-stick">
                <?php include 'nav.php';?>
        <div class="nav-two">
                <div id="filter" value="1" onclick="filterForm(this)">
                    <a href="#">
                        <ul>
                        <li>Filter</li>
                        <li><img src="images/filter.gif"></li>
                        </ul>
                    </a>
                </div>
                <div id="feed-menu">
                    <ul>
                    <li id="tabs_li_trending"><a class="tablink underlinetoggle" href="#top" onclick="openPost('home-post-trending',this); borderTabsT(); changeValTrending();" id="defaultOpen">Trending</a></li>
                    <li> <span id="space"></span></li>
                    <li id="tabs_li_followed"><a class="tablink underlinetoggle" href="#top" onclick="openPost('home-post-followed',this); borderTabsF(); changeValFollowed();">Followed</a></li>
                    </ul>
                </div>
        </div>
                </div>
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
                        <img id="public_post_image" src="'.$data_row['image_path'] .'" onerror=this.src="images/page_default_sm.gif">
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
                        <img id="public_post_image" src="'.$data_row['image_path'] .'" onerror=this.src="images/page_default_sm.gif">
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
            <div id="filter-form" class="shadow">
                <div class="page-title">
                    <p>Filter</p>
                </div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <p>Looking for:</p>
                    <input type="radio" value="sponsor" name="spon-event" checked="true" onClick="displayForm(this)"> Sponsors
                    <input type="radio" value="event" name="spon-event"  onClick="displayForm(this)"> Events<br>
                    <div  id=category>
                    <p>Select Category:</p>
                            <select name="scategory">
                                <option value="">Select Category</option>
                                <option value="Music and Festival">Music &amp; Festival</option>
                                <option value="Business and Professional">Business &amp; Professional</option>
                                <option value="Food and Drinks">Food &amp; Drinks</option>
                                <option value="Media and Entertainment">Media &amp; Entertainment</option>
                                <option value="Sports and Fitness">Sports &amp; Fitness</option>
                                <option value="Health and Wellness">Health &amp; Wellness</option>
                                <option value="Science and Technology">Science &amp; Technology</option>
                                <option value="Home and Lifestyle">Home &amp; lifestyle</option>
                                <option value="Charity and Causes">Charity &amp; Causes</option>
                                <option value="Family and Education">Family &amp; Education</option>
                                <option value="Community and Culture">Community &amp; Culture</option>
                                <option value="Religion and Spirituality">Religion &amp; Spirituality</option>
                                <option value="Government and Politics">Government &amp; Politics</option>
                                <option value="Fashion and Beauty">Fashion &amp; Beauty</option>
                                <option value="Travel and Outdoor">Travel &amp; Outdoor</option>
                                <option value="Web and Internet">Web &amp; Internet</option>
                                <option value="Seasonal and Holiday">Seasonal &amp; Holiday</option>
                                <option value="Auto, Boat and Air">Auto, Boat &amp; Air</option>
                                <option value="other">Other</option>
                            </select>
                    </div>
                    <div id="events-form">
                            <div id="catagory-topic">
                                <p>Select Category:</p>
                            <select name="category">
                                <option value="">Select Category</option>
                                <option value="Youtube Videos">Youtube videos</option>
                                <option value="Blogs">Blogs</option>
                                <option value="Instagram Videos">Instagram videos</option>
                                <option value="Conference">Conference</option>
                                <option value="Class, Training or Workshop">Class, Training or Workshop</option>
                                <option value="Festival and Fair">Festival &amp; Fair</option>
                                <option value="Meetings">Meetings</option>
                                <option value="Camp, Trip or Retreat">Camp, Trip or Retreat</option>
                                <option value="Attraction">Attraction</option>
                                <option value="Theatre">Theatre</option>
                                <option value="Tournament">Tournament</option>
                                <option value="Meeting or Networking Event">Meeting or Networking Event</option>
                                <option value="Party">Party</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Rally">Rally</option>
                                <option value="Screening">Screening</option>
                                <option value="Stand-up shows">Stand-up shows</option>
                                <option value="Seminar or talk">Seminar or talk</option>
                                <option value="Convention">Conventions</option>
                                <option value="concerts">Concerts</option>
                                <option value="Exhibition">Exhibition</option>
                                <option value="Games or competition">Game or Competition</option>
                                <option value="Billboards">Billboards</option>
                                <option value="Screening">Screening</option>
                                <option value="Tour">Tour</option>
                                <option value="Tradeshow, Consumer Show or Expo">Tradeshow, Consumer Show or Expo</option>
                                <option value="Tournament">Tournament</option>
                                <option value="Race or Endurance">Race or Endurance</option>
                                <option value="Others">Others</option>
                            </select>
                            <p>Select topic:</p>
                            <select name="event-topic">
                                <option value="">Select Topic</option>
                                <option value="Music and Festival">Music &amp; Festival</option>
                                <option value="Business and Professional">Business &amp; Professional</option>
                                <option value="Food and Drinks">Food &amp; Drinks</option>
                                <option value="Media and Entertainment">Media &amp; Entertainment</option>
                                <option value="Sports and Fitness">Sports &amp; Fitness</option>
                                <option value="Health and Wellness">Health &amp; Wellness</option>
                                <option value="Science and Technology">Science &amp; Technology</option>
                                <option value="Home and Lifestyle">Home &amp; lifestyle</option>
                                <option value="Charity and Causes">Charity &amp; Causes</option>
                                <option value="Family and Education">Family &amp; Education</option>
                                <option value="Community and Culture">Community &amp; Culture</option>
                                <option value="Religion and Spirituality">Religion &amp; Spirituality</option>
                                <option value="Government and Politics">Government &amp; Politics</option>
                                <option value="Fashion and Beauty">Fashion &amp; Beauty</option>
                                <option value="Travel and Outdoor">Travel &amp; Outdoor</option>
                                <option value="Web and Internet">Web &amp; Internet</option>
                                <option value="Seasonal and Holiday">Seasonal &amp; Holiday</option>
                                <option value="Auto, Boat and Air">Auto, Boat &amp; Air</option>
                                <option value="other">Other</option>
                            </select>
                            </div>
                            <p>Budget:</p>
                            <div class="budget">
                                <p><span id="from">From</span><span id="to">To</span></p>
                            <select id="budget-min" name="budget-min">
                                <option value="0">₹0</option>
                                <option value="5000">₹5000</option>
                                <option value="10000">₹10000</option>
                                <option value="25000">₹25000</option>
                                <option value="50000">₹50000</option>
                                <option value="100000">₹1 Lakh</option>
                                <option value="150000">₹1.5 Lakh</option>
                                <option value="200000">₹2 Lakh</option>
                                <option value="500000">₹5 Lakh</option>
                            </select>
                            <select id="budget-max" name="budget-max">
                                <option value="5000">₹5000</option>
                                <option value="10000">₹10000</option>
                                <option value="25000">₹25000</option>
                                <option value="50000">₹50000</option>
                                <option value="100000">₹1 Lakh</option>
                                <option value="150000">₹1.5 Lakh</option>
                                <option value="200000">₹2 Lakh</option>
                                <option value="500000">₹5 Lakh</option>
                            </select>
                            </div>
                            <p>Required Reach:</p>
                            <div class="reach">
                                <p><span id="min">Min</span><span id="max">Max</span></p>
                            <select id="reach-min" name="reach-min">
                                <option value="0">0</option>
                                <option value="250">250</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                                <option value="2500">2500</option>
                                <option value="5000">5000</option>
                                <option value="10000">10000</option>
                                <option value="20000">20000</option>
                                <option value="20001">Greater than 20000</option>
                            </select>
                            <select id="reach-max" name="reach-max">
                                <option value="250">250</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                                <option value="2500">2500</option>
                                <option value="5000">5000</option>
                                <option value="10000">10000</option>
                                <option value="20000">20000</option>
                                <option value="20001">Greater than 20000</option>
                            </select>
                            </div>
                        <p>Enter keywords that define event:</p>
                            <input type="text" name="tags" placeholder="eg: music, dance, food...">
                    </div>
                    <p>Location:</p>
                            <input type="text" name="location" id="location" placeholder="Enter city name.">
                    <input type="submit" value="Apply" id="apply" name="apply">
                </form>
            </div>
           <?php
            if(($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['apply'])){
                if($_POST['spon-event'] == 'sponsor'){
                    $loco= $_POST['location'];
                    $scategory = $_POST['scategory'];
                    header('location:search.php?type='.$_POST['spon-event'].'&loco='.$loco.'&category='.$scategory);
                }else{
                    $loco = $_POST['location'];
                    $category = $_POST['category'];
                    $topic = $_POST['event-topic'];
                    $bmin = $_POST['budget-min'];
                    $bmax = $_POST['budget-max'];
                    $rmin = $_POST['reach-min'];
                    $rmax= $_POST['reach-max'];
                    $tag = $_POST['tags'];
                    header('location:search.php?type='.$_POST['spon-event'].'&loco='.$loco.'&category='.$category.'&topic='.$topic.'&bmin='.$bmin.'&bmax='.$bmax.'&rmin='.$rmin.'&rmax='.$rmax.'&tag='.$tag);
                }
            }
            ?>
            
            
            <div id="home-post-trending" class="tabcontent">
            <?php
            $activate_check = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$uid'"));
                if($activate_check['activate_check'] == null){
                    echo '<div class="alert">';
                    echo "Your email has not been verified. To verify check your mail.  
                    <a href='?send_link'>Resend verification link?</a>";
                    echo '</div>';
                }
                ?>
            </div>
            <div id="marker"></div>
            <div id="home-post-followed" class="tabcontent">
             <?php
            $activate_check = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$uid'"));
                if($activate_check['activate_check'] == null){
                    echo '<div class="alert">';
                    echo "Your email has not been verified. To verify check your mail.  
                    <a href='?send_link'>Resend verification link?</a>";
                    echo '</div>';
                }
                ?>
            </div>
                        
           <div  class="sticky">
            <div id="post" class="shadow">
                <div id="create">
                    <span id="create-p">
                    <p>Create Page</p>
                    </span>
                    <span id="create-img">
                    <img src="images/pencil.gif">
                    </span>
                </div>
               <div id="sp-ev">
                   <p><span id="spon"><a href="create-sponsor-page.php" title="I am sponsor">Sponsor</a></span>
                       <span id="divisor"></span>
                       <span id="event"><a href="create-event-page.php" title="List event">Event Organiser</a></span></p>
                </div>
            </div>
                    
           <div id="pages" class="shadow">
                <div class="page-title">
                <p>My pages</p>
                </div>
                <div id="scroll">
                <?php
                    //storing total row in sponsor_page db by this user   
                    $sponsor_row = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE user_id='$uid'"));
                    //storing total row in event_page db by this user
                    $event_row = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM event_page WHERE user_id='$uid'"));
                    //storing total row in sponsor_page db   
                    $sponsor_page_row_count = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM sponsor_page"));
                    //storing total row in event_page db   
                    $event_page_row_count = mysqli_num_rows(mysqli_query($dbc,"SELECT * FROM event_page"));
                $i="1";
                $j="1";
                    if($sponsor_row == 0 && $event_row == 0){
                        echo '<div id="sorry">';
                        echo '<p>You haven’t created any Event page<br>or Sponsor page.</p>';
                        echo '</div>';
                        echo '<img src="images/sad-smiley.gif">';
                        echo '<div id="sorry-two">';
                        echo '<p>Creating a page helps you contact<br>others and them find you.</p>';
                        echo '</div>';
                    }else{
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
                        echo '<div id="display-pages">';
                        while($i<=$lastid){
                            $result = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM sponsor_page WHERE id='$i'"));
                            if($result['user_id']== $uid){
                                $unique_key = $result['unique_key'];
                                $brandname = $result['brand_name'];
                                $brand_image = $result['image_path'];
                                echo '<table>';
                                echo '<tr>';
                                echo '<td id="profile_img_set">';
                                echo "<img src='".$brand_image."' onerror=this.src='images/page_default_sm.gif'>";
                                echo '</td>';
                                echo '<td id="page_name_title">';
                                echo '<a href="sponsor-page.php?key='.$unique_key.'">'.$brandname.'</a><br>';
                                echo '</td>';
                                echo '</tr>';
                                echo '</table>';
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
                        echo '<div id="display-pages">';
                        while($j<=$lastid){
                            $result = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE id='$j'"));
                            if($result['user_id']== $uid){
                                $unique_key = $result['unique_key'];
                                $pagename = $result['page_name'];
                                $page_image = $result['image_path'];
                                echo '<table>';
                                echo '<tr>';
                                echo '<td id="profile_img_set">';
                                echo "<img src='".$page_image."'  onerror=this.src='images/page_default_sm.gif'>";
                                echo '</td>';
                                echo '<td id="page_name_title">';
                                echo '<a href="event-page.php?key='.$unique_key.'">'.$pagename.'</a><br>';
                                echo '</td>';
                                echo '</tr>';
                                echo '</table>';
                            }
                            $j++;
                        }
                        echo '</div>';
                        }
                    }
                ?>
            </div>
            </div>
            </div>
        </div>
        
        <script>
            function activatePlacesSearch(){
                var input = document.getElementById('location');
                var autocomplete = new google.maps.places.Autocomplete(input);
            } 
        </script>        
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBGaGSkMrmWymSfJTdHK79Bq2FgKkltPN0&libraries=places&callback=activatePlacesSearch"></script>
        <?php mysqli_close($dbc);?>
    </body>
</html>