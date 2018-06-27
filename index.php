<?php
    include_once 'php/session.php';

    if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
        $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $redirect");
        exit();
    }

    if(!test_session()){
        if(isset($_SESSION['username'])){
            $_SESSION=array(); //pulisco l'array

            // If it's desired to kill the session, also delete the session cookie.
             // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies")) { // get cookies info from init file
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 3600*24, //tempo neg per uccidere il cookie
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();  // destroy session
            session_write_close();
        }
    }
    
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Shuttle booking webpage">
    <meta name="author" content="Emanuele">
    <meta http-equiv="pragma" content="no-cache">

    <title>Shuttle</title>

    <!-- import stylesheets -->
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/index.css">
    <link href=' http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>

    <!-- import jQuery -->
    <script type="text/javascript" src="jquery/jquery.js"></script>

    <!-- import js -->
    <script type="text/javascript" src="front/controller/login.js"></script>

    <script type="text/javascript"><!--
        if (!navigator.cookieEnabled)
            window.location.replace("../front/view/nocookie.html");
    // --></script>

</head>

<noscript>
    <h3>Javascript is not enabled</h3>
    <?php
    if(!isset($_COOKIE['foo'])){ // guardo se è settato, al primo giro non lo sarà
        setcookie('foo', 'bar', time()+3600); //creo il cookie
        header("Location: ./php/checkcookie.php"); // mando a checkcookie per vedere se è settato dopo che l'ho creato
    }
    ?>
</noscript>

<body>

    <header>
        
        <div class="htitle1"><h1 class='h1nav'>Shuttle.com</h1>
        <p>The best way to get where you want</p></div>
        
        <div class="htitle2"><img class='htitle' src="images/shuttle.png"></div>

        <?php 
            $login = '<div class="htitle3"><a class="hleft" href="front/view/login.php"><button>Log in</button></a>  ';
            $signup = '<a class="hright" href="front/view/signup.php"><button>Sign up</button></a></div>';
            $logout = '<div class="htitle3"><a class="logout" href="front/view/logout.php"><button id="logout">Logout</button></a></div>';
            if(!isset($_SESSION['username'])){
                echo $login;
                echo $signup;
            }
            else{
                echo $logout;
            }
        ?>        
    </header>

    <nav>
        <div class="navbutton">
        <a href="index.php"><button>Home</button></a><br>
        <?php if(isset($_SESSION['username']))
                    echo '<a href="front/view/user.php"><button>User</button></a><br>';
                else
                    echo '<a href="front/view/login.php"><button>User</button></a><br>'; ?>
        </div>
    </nav>

    <section>
        <h2>
            Programmed route
        </h2>
        
        
        <?php include_once ('front/view/table.php'); display(); ?>
       
    </section>

    <footer>

    </footer>

    <script type="text/javascript" src="front/controller/error.js"></script>

</body>

</html>