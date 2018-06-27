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
        echo $_POST['departure'];
        echo $_POST['arrival'];
        if(strcmp($_POST['departure'], $_POST['arrival']) >= 0 ){
            $_SESSION['book'] = 0;
            $query = array(
                'book' => "unsuccessful"
                );
            $query = http_build_query($query);
            header("Location: ../front/view/user.php?$query");
        }

        if(connect_db() == false)
            die("DB connection error..<br>");

        echo "sessione ok<br>";
        $user = true;

        $username = $_SESSION['username'];
        if(isset($_POST['departure']))
            $departure = $_POST['departure'];

        if(isset($_POST['arrival']))
            $arrival = $_POST['arrival'];

        if(isset($_POST['persons']))
            $persons = $_POST['persons'];

        $res = book($username, $departure, $arrival, $persons); //mettere a posto i valori di ritorno di book e i controlli che si fanno qui

        if($res){
            $_SESSION['book'] = 1;
            $query = array(
                'book' => "booked"
                );
            $query = http_build_query($query);
            close_db();
            header('Location: https://'.$_SERVER['HTTP_HOST']."/assignment/front/view/user.php?$query");
        }
        else{
            $query = array(
                'book' => "notbooked"
                );
            $query = http_build_query($query);
            close_db();
            header('Location: https://'.$_SERVER['HTTP_HOST']."/assignment/front/view/user.php?$query");
        }
        return;
        
    }
    else{
        echo "sess not ok";
        header("Location: ../front/view/login.php");
    }

?>