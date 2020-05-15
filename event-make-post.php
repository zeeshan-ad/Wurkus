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
            
         $key= $_GET['key'];
            $user_id_row= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key='$key'"));
            $user_id= $user_id_row["id"];
            //Form php to store data
            if(isset($_POST['event_name']) && isset($_POST['category']) && isset($_POST['event-topic']) && isset($_POST['tags']) && isset($_POST['budget-min']) && isset($_POST['budget-max']) && isset($_POST['reach-min']) && isset($_POST['reach-max']) && isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year']) && isset($_POST['event_venue']) && isset($_POST['event_desc']) && isset($_POST['event_desc_detail'])&& (($_FILES['brochure']['tmp_name'])!="") && (preg_match("/[0-9]/",$_POST['day']) && $_POST['day']<'32') && (preg_match("/[0-9]/",$_POST['month']) && $_POST['month']<'13') && (preg_match("/[0-9]/",$_POST['year']) && $_POST['day']<'2050') && count($_FILES['brochure']['tmp_name'])<'4' && count($_FILES['prev_img']['tmp_name'])<'4'){
                //compress image quality function
                function compress($source, $destination, $quality) {

                $info = getimagesize($source);

                if ($info['mime'] == 'image/jpeg') 
                    $image = imagecreatefromjpeg($source);

                elseif ($info['mime'] == 'image/gif') 
                    $image = imagecreatefromgif($source);

                elseif ($info['mime'] == 'image/png') 
                    $image = imagecreatefrompng($source);

                imagejpeg($image, $destination, $quality);

                return $destination;
            }
                //function ends here!!
                
                global $collective_name_brochure;
               for($i=0;$i<count($_FILES['brochure']['tmp_name']);$i++){
                   $file_tmp = $_FILES['brochure']['tmp_name'][$i];
                   $file_name = time().$_FILES['brochure']['name'][$i];
                   $file_path = "user_upload/event_make_post/".$file_name;
                   compress($file_tmp,$file_path,50);
                   $collective_name_brochure = $file_path."|".$collective_name_brochure;
               }
                global $collective_name_prev;
                if($_FILES['prev_img']['size'][0]!=0){
                    for($i=0;$i<count($_FILES['prev_img']['tmp_name']);$i++){
                   $file_tmp2 = $_FILES['prev_img']['tmp_name'][$i];
                   $file_name2 = time().$_FILES['prev_img']['name'][$i];
                   $file_path2 = "user_upload/event_make_post/".$file_name2;
                   
                   compress($file_tmp2,$file_path2,50);
                   $collective_name_prev = $file_path2."|".$collective_name_prev;
               }
                    }else{
                    $collective_name_prev="";
                }
                $event_name = $_POST['event_name'];
                $event_topic = $_POST['event-topic'];
                $event_category = $_POST['category'];
                $tags = $_POST['tags'];
                $budget_min =$_POST['budget-min'];
                $budget_max =$_POST['budget-max'];
                $reach_min =$_POST['reach-min'];
                $reach_max=$_POST['reach-max'];
                $day=$_POST['day'];
                $month=$_POST['month'];
                $year=$_POST['year'];
                $event_venue=$_POST['event_venue'];
                $event_desc=addslashes($_POST['event_desc']);
                $event_desc_detail=addslashes($_POST['event_desc_detail']);
                //longitude latitude part
                $address = $event_venue; // Google HQ
                $prepAddr = str_replace(' ','+',$address);
                do{
                    $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false&key=AIzaSyBGaGSkMrmWymSfJTdHK79Bq2FgKkltPN0');
                    $output= json_decode($geocode,true);
                } while ($output['status']!="OK" && $output['status']!="ZERO_RESULTS");
                if($output['status']=="OK"){
                    $latitude = $output['results'][0]['geometry']['location']['lat'];
                    $longitude = $output['results'][0]['geometry']['location']['lng'];
                    $event_post_form_result=mysqli_query($dbc, "INSERT INTO event_make_post (event_name, event_category,event_topic, tags, budget_min, budget_max, reach_min, reach_max, day, month, year, event_venue, lat, lon, event_desc, event_desc_detail,brochure_path,prev_img_path, date, time,user_id) VALUES('$event_name','$event_category','$event_topic','$tags', '$budget_min','$budget_max', '$reach_min','$reach_max','$day','$month','$year','$event_venue','$latitude','$longitude','$event_desc','$event_desc_detail','$collective_name_brochure','$collective_name_prev',CURDATE(), CURTIME(), '$user_id')");
                } else {
                $event_post_form_result=mysqli_query($dbc, "INSERT INTO event_make_post (event_name,event_category,event_topic, tags, budget_min, budget_max, reach_min, reach_max, day, month, year, event_venue, event_desc, event_desc_detail,brochure_path,prev_img_path, date, time,user_id) VALUES('$event_name','$event_category','$event_topic','$tags', '$budget_min','$budget_max', '$reach_min','$reach_max','$day','$month','$year','$event_venue','$event_desc','$event_desc_detail','$collective_name_brochure','$collective_name_prev',CURDATE(), CURTIME(), '$user_id')");
                }
                 header('location:event-page.php?key='.$key.'');
            }

            //end
include_once 'accesscontrol.php';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Create Post | Wurkus</title>
        <link href="assets/css/stylesheets.css" rel="stylesheet">
        <link href="assets/css/jquery.Jcrop.css" rel="stylesheet">
        <script src="assets/js/jquery.min.js"></script> 
        <script src="assets/js/jquery.Jcrop.js"></script>
        <link href="assets/css/tags.css" rel="stylesheet">
        <script>
            // tags script
            $(function() {
          $('#tags').tagEditor({
          delimiter: ', ',
              placeholder: 'Add tags..'
          });
            });
	
             //tags script end

            function moveToNext(selector, nextSelector) {
                  $(selector).on('input', function () {    
                    if (this.value.length >= 2) {
                      // Date has been entered, move
                      $(nextSelector).focus();
                    }
                  });
                }


            $(function () {
                  moveToNext('#day', '#month');
                  moveToNext('#month', '#year');
                });
            </script>
        <style>
            body{
                padding-top: 100px;
            }
            </style>
    </head>
    <body>
        <div id="wrapper">
        <?php
            echo '<div id="nav-stick">';
            include 'nav.php';
            echo '</div>';
        ?>
            <div id="event-post-form">
                <form method="post" enctype="multipart/form-data" action="event-make-post.php?key=<?php echo $key;?>">
                    <p>Event name</p>
                    <input maxlength="22" type="text" name="event_name" id="event_name" required>
                    <p>Event Category:</p>
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
                    <p>Event topic:</p>
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
                    <p>Enter tags that defines your event</p>
                    <input type="text" id="tags" name="tags" required>
                    <p>Amount range:</p>
                            <div class="budget">
                                <p><span id="post_from">From</span><span id="post_to">To</span></p>
                            <select id="budget-min" name="budget-min" required>
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
                            <select id="budget-max" name="budget-max" required>
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
                    <p>Event reach:</p>
                            <div class="reach" required>
                                <p><span id="post_min">Min</span><span id="post_max">Max</span></p>
                            <select id="reach-min" name="reach-min" required>
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
                            <select id="reach-max" name="reach-max" required>
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
                    <p>Event date</p>
                    <div class="date-input-container">
                      <input type="text" maxlength="2" placeholder="DD" id="day" name="day" required/>
                      <span class="separator">/</span>
                      <input type="text" maxlength="2" placeholder="MM" id="month" name="month" required/>
                      <span class="separator">/</span>
                      <input type="text" maxlength="4" placeholder="YYYY" id="year" name="year" required/>
                    </div>
                    <?php
                    if(isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year'])){
                           if(!preg_match("/[0-9]/",$_POST['day']) || ($_POST['day']>'31') || !preg_match("/[0-9]/",$_POST['month']) || $_POST['month']>'12' || !preg_match("/[0-9]/",$_POST['year']) || $_POST['day']>'2050'){
                        echo '<div class="error">
                            <p>Enter a valid date</p>
                                </div>';
                           }
                    }
                    ?>
                    <p>Event venue</p>
                    <input type="text" id="event_venue" name="event_venue" required>
                    <p>Brief description</p>
                    <textarea rows="5" cols="56"  maxlength="140" id="event_desc" name="event_desc" placeholder="Maximum 140 characters" required></textarea>
                    <p>About event</p>
                    <textarea rows="5" cols="56"  id="event_desc_detail" name="event_desc_detail" placeholder="Detailed description" required></textarea>
                    <div id="file_upload">
                    <p>Upload brochure (eg: Sponsor rate chart, etc)<br>[Max uploads: 3 images]</p>
                    <input type="file" id="brochure" name="brochure[]" accept="image/*" multiple required>
                    <?php
                        if(isset($_FILES['brochure']['tmp_name'])){
                        if(!(count($_FILES['brochure']['tmp_name'])<'4')){
                        echo '<div class="error">
                            <p>Only 3 images allowed</p>
                                </div>';
                        }
                        }
                        ?>    
                    <p>Attach photos from previous time this event was held (optional)<br>[Max uploads: 3 images]</p>
                    <input type="file" id="prev_img" name="prev_img[]" accept="image/*" multiple>
                        <?php
                        if(isset($_FILES['prev_img']['tmp_name'])){
                            if(!(count($_FILES['prev_img']['tmp_name'])<'4')){
                            echo '<div class="error">
                                <p>Only 3 images allowed</p>
                                    </div>';
                            }
                        }
                        ?>    
                    </div>
                    <input type="submit" value="Create post">
                </form>
            </div>
        </div>
        <script>
            function activatePlacesSearch(){
                var input = document.getElementById('event_venue');
                var autocomplete = new google.maps.places.Autocomplete(input);
            } 
        </script>
        <script src="assets/js/caret.js"></script>
        <script src="assets/js/tags.js"></script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBGaGSkMrmWymSfJTdHK79Bq2FgKkltPN0&libraries=places&callback=activatePlacesSearch"></script>
        <?php mysqli_close($dbc);?>
    </body>
</html>