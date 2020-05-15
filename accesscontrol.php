<?php
session_start(); 
if(!isset($_SESSION['pwd']) && !isset($_SESSION['email'])){
    $password = $firstname_row['password'];
    if(!password_verify($_SESSION['pwd'],$password) && $_SESSION['email']!=$email){
        header('location:index.php');
    }
}
?>