<?php

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
    exit();
}

include 'session.php';
include 'db.php';

$ok = true;

if(isset($_POST['username'])){
    $username = $_POST['username']; // to be sanitized
    echo $username."<br>";
}
else{
    $ok = false;
}


if(isset($_POST['password'])){
    $password = $_POST['password']; // to be sanitized
    echo $password."<br>";
}
else{
    $ok = false;
}

if(isset($_POST['submit'])){
    $action = $_POST['submit'];
    if($action !== "Sign up" && $action !== "Login"){
        $ok = false;
    }
    echo $action."<br>";
}
else{
    $ok = false;
    echo "submit does not work<br>";
}

if(!filter_var($username, FILTER_VALIDATE_EMAIL)){ //check email server side
    $ok = false;
}

$re = '/(.*[A-Z0-9]+.*[a-z]+.*)|(.*[a-z]+.*[A-Z0-9]+)/';
if(!preg_match_all($re, $password)){ //check pswd serverside
    $ok = false;
}


if($ok == true){
   
    if(connect_db() == false)
        die("DB connection error..<br>");

    echo "connected"."<br>";
    
    if($action === "Login"){
        $login = query_db($username, $password);
        if($login == 1){ // wrong password
            echo "wrong password, try again";
            close_db();
            header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/front/view/login.php?success=wrongusrorpwd');
        }
        else if($login == 2){ //user not registered
            echo "user not registered<br>";
            close_db();
            header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/front/view/login.php?success=wrongusrorpwd');
        }
        else{
            begin_session($username);
            echo $_SESSION['username'];
            echo "Ho login l'utente<br>";
            close_db();
            header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/front/view/user.php');
        }
        
    }
    else if($action === "Sign up"){
        $registration = registration_db($username, $password);
        if($registration == 1){ //error accessing db or inserting or looging in
            echo "username already there";
            close_db();
            header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/front/view/signup.php?success=wrongpwd');
        }
        else if($registration == 2){ //log in
            $action = "Login";
            echo "Ho login l'utente<br>";
            $query = array(
                'username' => $username,
                'success' => "duplicateusername"
                );
            $query = http_build_query($query);
            close_db();
            header('Location: http://'.$_SERVER['HTTP_HOST']."/assignment/front/view/signup.php?$query");
        }
        else{ //registered
            begin_session($username);
            $date_of_expire = time() + 60*60*24*30; //cookie durano 30 giorni
            setcookie("userlogin", $username, $date_of_expire, "/");
            echo $_SESSION['username'];
            echo "Ho registrato l'utente<br>";
            close_db();
            header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/front/view/user.php?msg=signup');
        }
    }    
}
else{ // se fa logout da riveere questa parte
    if($action === "Login"){
        session_destroy();
        close_db();
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/front/view/login.php');
    }
    else if($action === "Sign up"){
        session_destroy();
        close_db();
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/front/view/signup.php'); 
    }
    else{
        session_destroy();
        close_db();
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/assignment/index.php'); 
    }
}




?>