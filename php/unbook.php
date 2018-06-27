<?php

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
    exit();
}

    include_once 'session.php';
    include_once 'db.php';

    echo "sto per verificare la sessione<br>";
    if(test_session() && isset($_SESSION['username'])){

        if(connect_db() == false)
            die("DB connection error..<br>");

        echo "sessione ok<br>";
        $user = true;

        $username = $_SESSION['username'];           

        if(unbook($username)){
            $_SESSION['book'] = 0;
            echo "Unbook successful";
        }
        else
            die("error unbooking");

        close_db();
        
        header("Location: ../front/view/user.php");
    }
    else{
        echo "sess not ok";
        $user = false;
        header("Location: ../front/view/login.php");
    }

?>