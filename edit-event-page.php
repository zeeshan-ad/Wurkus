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
            //extracting unique key from event-page.php
            $unique_key = $_REQUEST['key'];
            //Event form code begins here
            if(isset($_POST['page_name']) && (($_FILES['uploadedimage']['tmp_name'])=="") && isset($_POST['description']) && isset($_POST['location']) && isset($_POST['category'])){
               $image_path = "user_upload/event_images/";
               $website = strtolower($_POST['website']);
               $description = addslashes($_POST['description']);
                $category = $_POST['category'];
                $location = $_POST['location'];
                //latitude-longitude part    
                $address = $location; // Google HQ
                $prepAddr = str_replace(' ','+',$address);
                do{
                    $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false&key=AIzaSyBGaGSkMrmWymSfJTdHK79Bq2FgKkltPN0');
                    $output= json_decode($geocode,true);
                } while ($output['status']!="OK" && $output['status']!="ZERO_RESULTS");
                if($output['status']=="OK"){
                        $latitude = $output['results'][0]['geometry']['location']['lat'];
                        $longitude = $output['results'][0]['geometry']['location']['lng'];
                        $query ="UPDATE event_page SET website='$website', location='$location', lat='$latitude', lon='$longitude', description='$description', category='$category' WHERE unique_key='$unique_key'";
                        $result_event = mysqli_query($dbc,$query);
                }
                else{
                    $query ="UPDATE event_page SET website='$website', location='$location', description='$description', category='$category' WHERE unique_key='$unique_key'";
                    $result_event = mysqli_query($dbc,$query);
                }
                if($result_event){
                    header('location:event-page.php?key='.$unique_key.'');
                }
            }
            $del_ep=mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key ='$unique_key'"));
            global $temp_image_name;
            global $temp_image_path;
            global $image_path;
            global $event_flag;
            $event_flag=0;
            if(isset($_POST['page_name']) && isset($_POST['category']) && (($_FILES['uploadedimage']['tmp_name'])!="") && isset($_POST['location']) && isset($_POST['description'])){
                unlink($del_ep['image_path']);
                $category = $_POST['category'];
                $image_path = "user_upload/event_images/";
                $page_name = ucfirst($_POST['page_name']);
                $website = strtolower($_POST['website']);//making website link lowercase
                $description = addslashes($_POST['description']);
                $location = $_POST['location'];
                //longitude latitude part
                $address = $location; // Google HQ
                $prepAddr = str_replace(' ','+',$address);
                do{
                    $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false&key=AIzaSyBGaGSkMrmWymSfJTdHK79Bq2FgKkltPN0');
                    $output= json_decode($geocode,true);
                } while ($output['status']!="OK" && $output['status']!="ZERO_RESULTS");
                if($output['status']=="OK"){
                    $latitude = $output['results'][0]['geometry']['location']['lat'];
                    $longitude = $output['results'][0]['geometry']['location']['lng'];
                    $query_update="UPDATE event_page SET category='$category', location='$location', lat='$latitude', lon='$longitude', website='$website', description='$description', image_path='$image_path' WHERE unique_key='$unique_key'";
                    $result_event = mysqli_query($dbc,$query_update);
                }
                else{
                    $query_update="UPDATE event_page SET category='$category', location='$location', website='$website', description='$description', image_path='$image_path' WHERE unique_key='$unique_key'";
                    $result_event = mysqli_query($dbc,$query_update);
                }
                
                $image_name = $_FILES['uploadedimage']['name'];
                $temp_image_path = "user_upload/temp_image/"; //image path
                $temp_image_name = time().$image_name; //renaming the file here
                setcookie('temp_image_name',$temp_image_name,time()+60*60*24*7, '/', NULL, NULL, TRUE);
                setcookie('unique_key',$unique_key,time()+60*60*24*7, '/', NULL, NULL, TRUE);
                move_uploaded_file($_FILES['uploadedimage']['tmp_name'], $temp_image_path.$temp_image_name);
                $event_flag=1;
            }
            if(isset($_POST['x1']) && isset($_POST['y1']) && isset($_POST['w']) && isset($_POST['h'])){
                    $x1 =$_POST['x1'];
                    $y1 = $_POST['y1'];
                    $w = $_POST['w'];
                    $h = $_POST['h'];
                $temp_image_name= $_COOKIE['temp_image_name'];
                $unique_key= $_COOKIE['unique_key'];    
                $temp_image_path = "user_upload/temp_image/"; //image path
                $image_path = "user_upload/event_images/";
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
                    if(mysqli_query($dbc,"UPDATE event_page set image_path = '$image_path$temp_image_name' WHERE unique_key='$unique_key'")){
                    header('location:event-page.php?key='.$unique_key.'');
                    setcookie('temp_image_name', 1, time()-3600,'/', NULL, NULL, TRUE);
                    setcookie('unique_key', 1, time()-3600,'/', NULL, NULL, TRUE);
                    unlink($temp_image_path.$temp_image_name);
                    }
            }
             $profile_details= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM event_page WHERE unique_key='$unique_key'"));
include_once 'accesscontrol.php';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Edit Event Page | Wurkus</title>
        <link href="assets/css/stylesheets.css" rel="stylesheet">
        <link href="assets/css/jquery.Jcrop.css" rel="stylesheet">
        <script src="assets/js/jquery.min.js"></script> 
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
        <style>
            body{
                padding-top: 100px;
            }
            </style>
    </head>
    <body>
        <div id="wrapper" id="focus">
        <?php
            echo '<div id="nav-stick">';
            include 'nav.php';
            echo '</div>';

                if($event_flag===1){
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
                echo '<form method="post" class="coords" action="create-event-page.php">
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
            ?>
            <div id="page-form" class="shadow">
            <form method="post" enctype="multipart/form-data" action="<?php echo 'edit-event-page.php?key='.$unique_key.'';?>">
                <table>
                    <tr>
                        <td>
                            <p>You are</p>
                <select name="category" id="category" required>
                     <option value="<?php echo $profile_details['category'];?>" style="display:none"><?php echo $profile_details['category'];?></option>
                               
                                <option value="">Select Category</option>
                                <option value="Organisation">Organisation</option>
                                <option value="Institute">Institute</option>
                                <option value="Club">Club</option>    
                                <option value="Individual">Individual</option>
                                <option value="Other">Other</option>    
                            </select>
                        </td>
                        <td id="left-space">
                     <p>Location</p>
                <input type="text" name="location" id="location" value="<?php echo $profile_details['location'];?>" required>
                        </td>
                        </tr>
                    <tr>
                 <td>
                <p>Name of your establishment</p>
                <input maxlength="22" type="text" name="page_name" id="brand_name" value="<?php echo $profile_details['page_name'];?>" required readonly>
                           </td>
                        <td id="left-space">
                            <p>Website (optional)</p>
                <input type="text" name="website" id="website" value="<?php echo $profile_details['website'];?>">
                        </td>
                         </tr>
                </table>
                <p>Update page profile picture</p>
                 <input name="uploadedimage" id="uploadedimage" type="file" accept="image/*" >
                <p>Description</p>
                <textarea maxlength="5000" id="description" name="description" placeholder="About page..." rows="5" cols="55" required><?php echo nl2br($profile_details['description']);?></textarea>
                <input type="submit" value="Update page">
            </form>
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