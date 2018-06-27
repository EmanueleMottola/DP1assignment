<?php

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
    exit();
}

define('ROOT', dirname(__FILE__));
include_once (ROOT.'/../../php/db.php');

$booking;
$user;


function display_user(){

    global $booking;
    global $user;

    if(connect_db() == false)
        die("DB connection error..<br>");

    $booking = query_booking();
    if($booking == 1)
        $booking = [];
    if($booking == 2){
        die("db error");
    }

    $row = query_location();
    if($row == 1){
        echo "<p class='empty'>No route planned</p>";
        return; // message no route planned
    }
    if($row == 2)
        return; // db error

    echo "<table>
    <tr>
        <th>Departure</th>
        <th>Arrival</th>
        <th>Passengers</th>
        <th>Users</th>
    </tr>";
    for($i = 0; $i < count($row) - 1; $i++){
        $part = $row[$i]['locid'];
        $dest = $row[$i]['next'];
        $pren = $row[$i]['booked'];
        
        if($pren == 0){
            $pren = "No passengers booked.\n";
        }
        echo "<tr><td onclick='select(this.innerHTML);' class='tabledata tabhighlight depredcolor'>$part</td>
        <td onclick='select(this.innerHTML);' class='tabledata tabhighlight arrredcolor'>$dest</td><td class='tabledata'>$pren</td><td>";
        
        for($j = 0; $j < count($booking); $j++){
            if($booking[$j]['locP'] <= $part && $booking[$j]['locD'] >= $dest){
                $user = $booking[$j]['username'];
                $pers = $booking[$j]['persons'];
                echo $user."(".$pers."); ";
            }
        }
        echo "</td></tr>";
    }

    echo "</table>";

    close_db();
}

function display(){

    if(connect_db() == false)
        die("DB connection error..<br>");

    $row = query_location();
    
    if($row == 1){
        echo "<p class='empty'>No route planned</p>";
        return; // message no route planned
    }
    if($row == 2)
        return; // db error
    echo "<p>The following table displays the route the shuttle will follow according to the prenotations done.</p>
    <table>
    <tr>
        <th class='taleft'>Departure</th>
        <th class='tabright'>Arrival</th>
        <th>Passengers</th>
    </tr>";
    for($i = 0; $i < count($row) - 1; $i++){
        $part = $row[$i]['locid'];
        $dest = $row[$i]['next'];
        $pren = $row[$i]['booked'];
        
        if($pren == 0){
            $pren = "No passengers booked.\n";
        }
        echo "<tr><td>$part</td><td>$dest</td><td>$pren</td></tr>";
        
    }

    echo "</table>";

    close_db();
}

function display_booking($username){

    global $booking;

    if($booking == 1)
        die("db error");

    for($i = 0; $i < count($booking); $i++){
        if($booking[$i]['username'] == $_SESSION['username']){
            $part = $booking[$i]['locP'];
            $dest = $booking[$i]['locD'];
            $pren = $booking[$i]['persons'];
            echo "<tr><td id='bookeddeperture' class='tabright tabledata'>$part</td><td id='bookedarrival' class='tableft tabledata'>$dest</td>
            <td class='tabledata'>$pren</td><td class='delete'><a href='../../php/unbook.php'><button id='delbutton'>Delete</button></a></tr>";
        }       
    }
}

?>