<?php
include_once '../../php/session.php';
include_once '../../php/getuserbooking.php';

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
    exit();
}

if(!test_session() || !isset($_SESSION['username'])){
    // not logged in or 2 minutes expired
    destroy_session();
    header('Location: login.php');
    exit;
}

getuserbooking($_SESSION['username']);

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
    <link rel="stylesheet" href="../../css/user.css">

    <!-- import jQuery -->
    <script type="text/javascript" src="../../jquery/jquery.js"></script>

    <!-- import js -->
    <script type="text/javascript" src="../controller/login.js"></script>
    <script type="text/javascript" src="../controller/book.js"></script>

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
        <div class="htitle1"><h1>Shuttle.com</h1>
        <p>The best way to get where you want</p></div>
        
        <div class="htitle2"><img class='htitle' src="../../images/shuttle.png"></div>
        <div class="htitle3"><a href="logout.php"><button id="logout">Logout</button></a></div>
    </header>

    <nav>
        <p class="username"><?php echo $_SESSION['username']; ?></p>
        <div class="navbutton">
        <a href="../../index.php"><button>Home</button></a><br>
        </div>
    </nav>

    <section id="first">
        <h2>Bookings</h2>
        <?php
            if( (!isset($_SESSION['book'])) || (isset($_SESSION['book']) && $_SESSION['book'] == 0 )){
        ?>
        <form action="../../php/book.php" method="POST">
            <div class="departure"><label class="labdep">Departure</label><input onclick="update_select(this.id);" id="dep" type="text" name="departure" maxlength="128" autocomplete="off"></div>
            <div class="arrival"><label class="labarr">Arrival</label><input onclick="update_select(this.id);" id="arr" type="text" name="arrival" maxlength="128" autocomplete="off"></div>
            <div class="persons"><label class="labpers">Persons</label><input id='num' type="number" name="persons" value="1" min="1" max="<?php include_once '../../php/db.php'; echo $capacity; ?>"></div>
            <input id='book' type="submit" value="Book" onclick = "return validate_book();">
        </form>
        <?php } ?>
        
        <?php include_once ('table.php'); display_user(); ?>
        
    </section>

    <section id="second">
    <?php 
        include_once ('table.php');
        if(isset($_GET['book']) && $_SESSION['book'] == 0){
            echo "<script type='text/javascript'>";
            if($_GET['book'] == 'unsuccessful')
                echo "alert('Departure and arrival are not in alphabetic order.');";
            else if($_GET['book'] == 'notbooked'){
                echo "alert('There are no places on the shuttle for the segment you chose.');";
            }
            else{
                echo "alert('Error in booking operation. Try again.');";
            }
            echo "window.location.replace('user.php');</script>";
        }

        if(isset($_SESSION['book']) && $_SESSION['book'] > 0){
            echo "<table>
                    <tr>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Passengers</th>
                    </tr>";
            display_booking($_SESSION['username']);
            echo "</table>";
        }
    ?>
    </section>

    <script type="text/javascript" src="../controller/search.js"></script>
    <script type="text/javascript" src="../controller/user.js"></script>

</body>

</html>