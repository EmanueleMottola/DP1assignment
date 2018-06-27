<?php

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
    exit();
}
    include_once 'db.php';

    function getuserbooking($username){
        
        if(connect_db() == false)
            die("DB connection error..<br>");

        if(get_user_booking($_SESSION['username'])){ //controllo se $_SESSION['username'] è settato, lo faccio sopra
            $_SESSION['book'] = 1;
        }
        else{
            $_SESSION['book'] = 0;
        }
        close_db();
    }
?>