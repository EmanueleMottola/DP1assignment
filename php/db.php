<?php

$conn;
$capacity = 4;

function connect_db(){
    global $conn;
    $conn = mysqli_connect('localhost', 'myuser', 'mypassword', 'mydb');
    if(mysqli_connect_errno()) die(mysqli_connect_error());

    if(!$conn){
        echo "Connection error<br>";
        return false;
    }
    else
        return true;
}

function query_db($username, $password){

    global $conn;

    $username_san = mysql_fix_string($conn, $username);
    $password_san = mysql_fix_string($conn, $password);

    $token = "";
    $salt1 = 'mk&m*';
    $salt2 = 'pl!!';
    $token = hash('ripemd128', $salt1.$password_san.$salt2);
    
    try{
        $query = "select password from user where username = '$username_san';";
        echo $query."<br>";
        $result = mysqli_query($conn, $query);
        echo mysqli_errno($conn).":".mysqli_error($conn)."<br>";
        //echo mysqli_num_rows($result);
        if($result == false){
            throw new Exception("User not logged in");
        }
        else if(mysqli_num_rows($result) > 1){ // controllo inutile, ma non si sa mai
            mysqli_free_result($result);
            throw new Exception("multiple users");
        }
        else{
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            if($row['password'] === $token){
                mysqli_free_result($result);
                echo $row['password']." = ".$token;
                return 0;
            }
            else{
                mysqli_free_result($result);
                return 1;
            }
        }
    }
    catch(Exception $e){
        return 2;
    }
}

function registration_db($username, $password){

    global $conn;

    $username_san = mysql_fix_string($conn, $username);
    $password_san = mysql_fix_string($conn, $password);

    $token = "";
    $salt1 = 'mk&m*';
    $salt2 = 'pl!!';
    $token = hash('ripemd128', $salt1.$password_san.$salt2);

    try{
        //inserisco la tupla
        $query = "INSERT INTO user (username, password) VALUES ('$username_san' , '$token' );";
        if(mysqli_query($conn, $query))
            return 0; //tupla inserita con successo
        else if(mysqli_errno($conn) == 1062){ // se l'utente è già registrato
            echo mysqli_errno($conn).": ".mysqli_error($conn)."<br>";
            $query = "SELECT password FROM user WHERE username = '".$username_san."';"; //gli faccio fare il login
            $result = mysqli_query($conn, $query);
            if($result == false)
                throw new Exception("[login] - operation failed");
            if(mysqli_num_rows($result) != 1){
                throw new Exception("multiple users with same username");
            }
            else{
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                if($row['password'] === $token){
                    echo "[login] ok<br>";
                    mysqli_free_result($result);
                    return 2;
                }
                else{
                    echo "[login] not ok<br>";
                    mysqli_free_result($result);
                    return 1;
                }
            }
        }
        throw new Exception("User not inserted");
        
    }
    catch(Exception $e){
        echo mysqli_errno($conn).": ".mysqli_error($conn)."<br>";
        return 1;
    }
}

/*fare la registrazione*/

function close_db(){
    global $conn;
    mysqli_close($conn);
}

function mysql_fix_string($connection, $string){
    if(get_magic_quotes_gpc()) $string = stripslashes($string);
    return mysqli_real_escape_string($connection, $string);
}

function mysql_entities_fix_string($connection, $string){
    return htmlentities(mysql_fix_string($connection, $string));
}

function query_location(){

    global $conn;
    $temp;$row;

    try{
        $query = "SELECT locid, next, booked FROM location;";
        $result = mysqli_query($conn, $query);
        if($result == false)
            throw new Exception("[display] query failed.");
        else if(mysqli_num_rows($result) == 0)
            return 1;
        else{
            $i = 0;
            while(( $temp = mysqli_fetch_array($result, MYSQLI_ASSOC)) != NULL)
                $row[$i++] = $temp;
                
            return $row;
        }
    }
    catch(Exception $e){
        echo mysqli_errno($conn).": ".mysqli_error($conn)."<br>";
        return 2;
    }
}


function book($username, $departure, $arrival, $persons){
    
    global $conn;
    global $capacity;
    
    $username_san = mysql_fix_string($conn, $username);
    $departure_san = mysql_fix_string($conn, $departure);
    $arrival_san = mysql_fix_string($conn, $arrival);

    try{
        mysqli_autocommit($conn, false);
        if($persons > $capacity){
            throw new Exception(); // no places
        }
        $res = mysqli_query($conn, "SELECT booked FROM location WHERE STRCMP(locid, '$departure_san')>=0 AND STRCMP(locid, '$arrival_san') < 0;");
        if($res == false)
            die("query failed");
        while(( $temp = mysqli_fetch_array($res, MYSQLI_ASSOC)) != NULL){
            if($temp['booked'] + $persons > $capacity){
                throw new Exception(); // no places
            }   
        }
        if(!mysqli_query($conn, "INSERT INTO booking VALUES('$username_san', '$departure_san', '$arrival_san', '$persons');")){
            throw new Exception(); // insertion failed
        } 
        else{
            mysqli_commit($conn);
            return 1;
        }
    }
    catch(Exception $e){
        mysqli_rollback($conn);
        echo mysqli_errno($conn).": ".mysqli_error($conn)."<br>";
        return 0;
    }
    
}

function query_booking(){

    global $conn;

    $query = "SELECT * FROM booking;";
    $result = mysqli_query($conn, $query);
    if($result == false){
        echo mysqli_errno($conn).": ".mysqli_error($conn)."<br>";
        return 2;
    }
    else if(mysqli_num_rows($result) == 0)
        return 1;
    else{
        $i = 0;
        while(( $temp = mysqli_fetch_array($result, MYSQLI_ASSOC)) != NULL)
            $row[$i++] = $temp;
        
        return $row;
    }
    

}

function unbook($username){
    global $conn;
    
    $username_san = mysql_fix_string($conn, $username);

    try{
        mysqli_autocommit($conn, false);
        if(!mysqli_query($conn, "DELETE FROM booking WHERE username = '$username';")){
            throw new Exception("Insertion failed.");
        }
        else{
            mysqli_commit($conn);
            return 1;
        }
    }
    catch(Exception $e){
        mysqli_rollback($conn);
        echo mysqli_errno($conn).": ".mysqli_error($conn)."<br>";
        return 0;
    }
}

function get_user_booking($username){
    
    global $conn;

    $username_san = mysql_fix_string($conn, $username);

    $res = mysqli_query($conn, "SELECT username FROM booking WHERE username = '$username_san'");
    if($res == false){
        die("db error");
    }
    else{
        $n = mysqli_num_rows($res);
        return $n;
    }
    
}

?>