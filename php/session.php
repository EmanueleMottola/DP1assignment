<?php

function destroy_session(){
    session_start();
    $_SESSION=array(); //pulisco l'array

    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) { // get cookies info from init file
        $params = session_get_cookie_params();
        setcookie('foo', '', time() - 3600*24, //tempo neg per uccidere il cookie
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();  // destroy session
    session_write_close();
    // redirect client to login page
    header('HTTP/1.1 307 temporary redirect');
    header('Location: ../../index.php');
    exit;
}

function begin_session($username){
    session_start();
    $_SESSION['time'] = time();
    $_SESSION['username'] = $username;
}

function test_session(){
    session_start();
    $diff = 0;

    if( isset($_SESSION['time'])){
        $diff = time() - $_SESSION['time'];
        if($diff >= 120)
            return 0;
        else{
            $_SESSION['time'] = time();
            return 1;
        }
    }
    else
        return 0;
}

?>