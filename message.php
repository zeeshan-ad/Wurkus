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
if(isset($_GET['id'])){
    $to_id=$_GET['id'];
}
include_once 'accesscontrol.php';
?>
<!doctype html>
<html lang="en" id="whole_html">
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.gif" />
        <title>Inbox | Wurkus</title>
        <link href="assets/css/stylesheets.css" rel="stylesheet">
       <script src="assets/js/jquery.min.js"></script>
        <script type="text/javascript">
       
        //ajax for fetching messages
//        $(document).ready(function(){
//            var limit = 25;
//            var start = 0;
//            var action = 'inactive';
 //          var to_id = "<php echo $_GET['id']; ?>";
//            
//                function load_messages(limit,start,to_id)
//                {
//                    $.ajax({
//                        url : "fetch-messages.php",
//                        method : "POST",
//                        data : {limit:limit,start:start,to_id:to_id},
//                        cache : false,
//                        success : 
//                        function(data)
//                        {
//                            $('#message_body').append(data);
//                            if(data=='')
//                                action = 'active';
//                            else
//                                action = 'inactive';
//                        }
//                                    });
//                        }
//            
//            if(action == 'inactive'){
//                action = 'active';
//                load_messages(limit,start,to_id);
//            }
//            var height_load = ($('#message_body').height());
//           $('#message_body').scroll(function(){
//                    if(($('#message_body').scrollTop())>height_load && (action=="inactive")){
//                        height_load=height_load*1.5;
//                        action = 'active';
//                        start = start + limit;
//                        setTimeout(function(){
//                            load_messages(limit,start,to_id)
//                            
//                        },100);
//                    }
//                });
//        });    
            $(document).ready(function(){
                var limit = 25;
                var start = 0;
                var to_id = '<?php echo $to_id; ?>';
            function ajaxMessage(){
               
                
                var params ='limit='+limit+'&start='+start+'&to_id='+to_id;
                var req = new XMLHttpRequest();
                req.onreadystatechange = function(){
                    if(req.readyState == 4 && req.status==200){
                        document.getElementById('message_body').innerHTML =req.responseText;
                    }
                }
                req.open('GET', 'fetch-messages.php?'+params ,true);
                req.send();
            }
                setInterval(function(){
                        ajaxMessage()
                        },1000);
            var height_load = ($('#message_body').height());
           $('#message_body').scroll(function(){
//                    console.log('scrollTop:'+$('#message_body').scrollTop());
//                    console.log('height_load:'+height_load)
                    if(($('#message_body').scrollTop())>height_load){
                        height_load=height_load*1.5;
                        limit = limit * 2;
                        setTimeout(function(){
                            ajaxMessage();
                            
                        },100);
                    }
                });
            });
            
            
            
        function readMessages(id_other){
            $.ajax({
                url : "message-opened.php",
                method : "POST",
                data : {id_other:id_other},
                cache : false,
                success : function(data){
                        console.log(data);
                }
            });
        }  
            
            $(document).ready(function(){
            function loadInbox(){
                var req = new XMLHttpRequest();
                req.onreadystatechange = function(){
                    if(req.readyState == 4 && req.status==200){
                        document.getElementById('inbox_scroll').innerHTML =req.responseText;
                    }
                }
                req.open('GET', 'inbox-load.php' ,true);
                req.send();
            }
                setInterval(function(){
                        loadInbox()
                        },1000);
            });
    
//        setInterval("UpdateMessages()",3000);   
//            function UpdateMessages() {
//            var msg_id = "echo $_GET['id']; ?>";
//            var xhttp = new XMLHttpRequest();
//            xhttp.onreadystatechange = function() {
//                if(this.readyState==4 && this.status ==200){
//                    document.getElementById("whole_html").innerHTML = this.responseText;
//                }
//            };    
//            xhttp.open("GET","message.php?id="+msg_id, true);
//            xhttp.send();    
//            }
                    
        </script>
           
        <style>
            body{
                overflow-y: hidden;
            }
        </style>
        </head>
    <body>
        <div id="wrapper">
        <div id="nav-stick">
            <?php include 'nav.php';?> 
        </div>
            <div id="message_box">
                  <div id="inbox">
                    <span id="inbox_head"><p>Inbox</p></span>
                      <div id="inbox_scroll">
                    
                      </div>
                </div> 
                <div id="message">
                    <div id="message_head">
                        
                        <?php
                         if(isset($_GET['id'])){
                            $to_id=$_GET['id'];
                            $from_id = $uid;
                            $to_id_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM public_signup WHERE id='$to_id'"));
                            echo "<p>".ucfirst($to_id_details['first_name'])." ".$to_id_details['last_name']."</p>";
                            } 
                        if(empty($_GET['id']) || !isset($_GET['id'])){
                             echo "<p>Select a recipient to send message.</p>";
                        }
                        ?>
                    </div>
                    <div id="message_body">
                    <?php
                        if(empty($_GET['id']) || !isset($_GET['id'])){
                             echo '<p class="error error_message" id="please_select"><span class="big_i">&#9432;</span> Please select a recipient to send messages. 
                          To select a recipient click on contact in profile page or in posts.</p>';
                        }
                        ?>
                        
                
                    </div>
                
                    <div id="message_textbox">
                       <?php
                         if(!isset($_GET['id'])){
                             $to_id="";
                         }
                        echo '<form method="post" class="message_submit" action="message.php?id='.$to_id.'">
                            <textarea id="message_private" name="message_private" placeholder="Type your message..."></textarea>
                            <input type="submit" value="Send">
                        </form>';
                        if(isset($_POST['message_private']) && !empty($_GET['id'])){
                            $message = $_POST['message_private'];
                            mysqli_query($dbc,"INSERT INTO message (to_id, from_id, message, time_sent, date_sent) VALUES('$to_id', '$from_id' ,'$message', CURTIME(), CURDATE())");
                            header('location:message.php?id='.$to_id);
                            }
                         ?>
                    </div>
                </div>
            </div>
        </div>
        <?php mysqli_close($dbc);?>
    </body>
</html>