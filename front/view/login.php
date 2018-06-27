<?php
    if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
        $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $redirect");
        exit();
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
    <link rel="stylesheet" href="../../css/basic.css">
    <link rel="stylesheet" href="../../css/login.css">
    <link href=' http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>

    <!-- import jQuery -->
    <script type="text/javascript" src="../../jquery/jquery.js"></script>

    <!-- import js -->
    <script type="text/javascript" src="../controller/login.js"></script>

    <script type="text/javascript"><!--
        if (!navigator.cookieEnabled)
            window.location.replace("nocookie.html");
    // --></script>

</head>

<noscript>
    <h3>Javascript is not enabled</h3>
    <?php
    if(!isset($_COOKIE['foo'])){ // guardo se è settato, al primo giro non lo sarà
        setcookie('foo', 'bar', time()+3600); //creo il cookie
        header("Location: ../../php/checkcookie.php"); // mando a checkcookie per vedere se è settato dopo che l'ho creato
    }
    ?>
</noscript>

<body>

    <header>
        <div class="htitle1"><h1 class='h1nav'>Shuttle.com</h1>
        <p>The best way to get where you want</p></div>
        
        <div class="htitle2"><img class='htitle' src="../../images/shuttle.png"></div>
    </header>

    <nav>
        <div class="navbutton">
        <a href="../../index.php"><button>Home</button></a><br>
        <a href="signup.php"><button>Sign up</button></a>
        </div>
    </nav>

   
        <form id="formid" method="post" action="../../php/authentication.php">
            
            <input name="username" type="text" maxlength="128" placeholder="username"><br>

            <input id="password" name="password" type="password" maxlength="128" placeholder="password"><br>

            <input id="login" type="submit" name="submit" value="Login" onclick="return validate()">

            <?php 
                $p_error = 'id="p-error"';
                if(isset($_GET['success'])){
                    if($_GET['success']== "wrongusrorpwd"){
                        echo "<p $p_error>Invalid username or password. <a href='signup.php'>Not registered yet?</a></p><br>";
                        echo "";
                    }
                }  
            ?>

        </form>

    <footer>

    </footer>

    <script type="text/javascript" src="../controller/error.js"></script>

</body>

</html>