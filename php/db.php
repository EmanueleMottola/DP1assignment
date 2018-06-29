<?php

$conn;
$capacity = 4;

function connect_db(){
    global $conn;
    $conn = mysqli_connect('localhost', 's243854', 'tionsnit', 's243854');
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
            die($temp['booked'] + $persons);
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
        if(!perform_check_insert($departure_san, $arrival_san, $persons)){
            throw new Exception();
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
        $res = mysqli_query($conn, "SELECT locP, locD, persons FROM booking WHERE username = '$username';");
        if($res == false)
            throw new Exception();
        $temp = mysqli_fetch_array($res, MYSQLI_ASSOC);
        $departure_san = $temp['locP'];
        $arrival_san = $temp['locD'];
        $persons = $temp['persons'];
        
        if(!perform_check_delete($departure_san, $arrival_san, $persons)){
            throw new Exception();
        }
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

function perform_check_insert($departure_san, $arrival_san, $persons){
    global $conn;

    //prendo quante righe si hanno
    $res = mysqli_query($conn, "SELECT COUNT(*) FROM location;");
    if($res == false)
        return 0;
    $tempcount = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $conta = $tempcount['COUNT(*)'];

    //se il db è vuoto, faccio gli inserimenti
    if($conta == 0){
        $res = mysqli_query($conn, "INSERT INTO location VALUES('$departure_san', '$arrival_san', 0, 0);");
        if($res == false)
            return 0;
        $res = mysqli_query($conn, "INSERT INTO location VALUES('$arrival_san', 'NULL', 0, 0);");
        if($res == false)
            return 0;
        
        $res = mysqli_query($conn, "UPDATE location
                        SET booked = booked + '$persons'
                        WHERE STRCMP(locid, '$departure_san')>=0 AND STRCMP(locid, '$arrival_san') < 0;");
        if($res == false)
            return 0;

        $res = mysqli_query($conn, "UPDATE location
                        SET used = used + 1
                        WHERE locid='$departure_san' OR locid = '$arrival_san';");
        if($res == false)
            return 0;

        return 1;
    }
    
    $res = mysqli_query($conn, "SELECT * FROM location;");
    if($res == false)
        return 0;
    $i=0;
    while(($templocation = mysqli_fetch_array($res, MYSQLI_ASSOC)) != NULL){
        $location[$i++] = $templocation;
    }

    $check1 = trova_locid($location, $departure_san);
    $check2 = trova_locid($location, $arrival_san);

    $prec_part = trova_prec($location, $departure_san);
    $succ_part = trova_succ($location, $departure_san);

    $prec_dest = trova_prec($location, $arrival_san);
    $succ_dest = trova_succ($location, $arrival_san);

    if($prec_part != NULL ){
        $prec_part_locid = $prec_part['locid'];
        $prec_part_next = $prec_part['next'];
        $prec_part_booked = $prec_part['booked'];
    }
    else{
        $prec_part_locid = NULL;
        $prec_part_next = NULL;
        $prec_part_booked = NULL;
    }

    if($succ_part != NULL){
        $succ_part_locid = $succ_part['locid'];
        $succ_part_next = $succ_part['next'];
        $succ_part_booked = $succ_part['booked'];
    }
    else{
        $succ_part_locid = NULL;
        $succ_part_next = NULL;
        $succ_part_booked = NULL;
    }

    if($prec_dest != NULL){
        $prec_dest_locid = $prec_dest['locid'];
        $prec_dest_next = $prec_dest['next'];
        $prec_dest_booked = $prec_dest['booked'];
    }
    else{
        $prec_dest_locid = NULL;
        $prec_dest_next = NULL;
        $prec_dest_booked = NULL;
    }

    if($succ_dest != NULL){
        $succ_dest_locid = $succ_dest['locid'];
        $succ_dest_next = $succ_dest['next'];
        $succ_dest_booked = $succ_dest['booked'];
    }else{
        $succ_dest_locid = NULL;
        $succ_dest_next = NULL;
        $succ_dest_booked = NULL;
    }

    if($check1 == 0 && $check2 == 0){
        if($prec_part_locid == NULL){
            $res = mysqli_query($conn, "INSERT INTO location VALUES('$departure_san', '$succ_part_locid', 0, 0);");
            if($res == false)
                return 0;
        }
        else{
            $res = mysqli_query($conn, "UPDATE location SET next = '$departure_san' WHERE locid = '$prec_part_locid';");
            if($res == false)
                return 0;
            $res = mysqli_query($conn, "INSERT INTO location VALUES('$departure_san', '$prec_part_next', '$prec_part_booked', 0);");
            if($res == false)
                return 0;
            //die($departure_san."-".$prec_part_locid);
        }

        $res = mysqli_query($conn, "SELECT * FROM location;");
        if($res == false)
            return 0;
        $i=0; $location = array();
        while(($templocation = mysqli_fetch_array($res, MYSQLI_ASSOC)) != NULL){
            $location[$i++] = $templocation;
        }

        $prec_dest = trova_prec($location, $arrival_san);
        echo "prec_dest_locid:  3".$prec_dest['locid'];
        $succ_dest = trova_succ($location, $arrival_san);

        if($prec_dest != NULL){
            $prec_dest_locid = $prec_dest['locid'];
            $prec_dest_next = $prec_dest['next'];
            $prec_dest_booked = $prec_dest['booked'];
        }
        else{
            $prec_dest_locid = NULL;
            $prec_dest_next = NULL;
            $prec_dest_booked = NULL;
        }
    
        if($succ_dest != NULL){
            $succ_dest_locid = $succ_dest['locid'];
            $succ_dest_next = $succ_dest['next'];
            $succ_dest_booked = $succ_dest['booked'];
        }else{
            $succ_dest_locid = NULL;
            $succ_dest_next = NULL;
            $succ_dest_booked = NULL;
        }

        $res = mysqli_query($conn, "UPDATE location SET next = '$arrival_san' WHERE locid = '$prec_dest_locid';");
        if($res == false)
            return 0;
        $res = mysqli_query($conn, "INSERT INTO location VALUES('$arrival_san', '$prec_dest_next', '$prec_dest_booked', 0);");
        if($res == false)
            return 0;
    }

    if($check1 != 0 && $check2 == 0){
        $res = mysqli_query($conn, "UPDATE location SET next = '$arrival_san' WHERE locid = '$prec_dest_locid';");
        if($res == false)
            return 0;
        $res = mysqli_query($conn, "INSERT INTO location VALUES('$arrival_san', '$prec_dest_next', '$prec_dest_booked', 0);");
        if($res == false)
            return 0; 
    }

    if($check1==0 && $check2!=0){
        if($prec_part_locid == NULL){
            $res = mysqli_query($conn, "INSERT INTO location VALUES('$departure_san', '$succ_part_locid', 0, 0);");
            if($res == false)
                return 0;
        }
        else{
            $res = mysqli_query($conn, "UPDATE location SET next = '$departure_san' WHERE locid = '$prec_part_locid';");
            if($res == false)
                return 0;
            $res = mysqli_query($conn, "INSERT INTO location VALUES('$departure_san', '$prec_part_next', '$prec_part_booked', 0);");
            if($res == false)
                return 0;
        }
    }

    $res = mysqli_query($conn, "UPDATE location
                                SET booked = booked + '$persons'
                                WHERE STRCMP(locid, '$departure_san')>=0 AND STRCMP(locid, '$arrival_san') < 0;");
    if($res == false)
        return 0;

    $res = mysqli_query($conn, "UPDATE location
        SET used = used + 1
        WHERE locid='$departure_san' OR locid = '$arrival_san';");
    if($res == false)
        return 0;

    return 1;
}

function trova_locid($lista, $da_trovare){
    for($i=0; $i < count($lista); $i++){
        $locid = $lista[$i]['locid'];
        if($locid == $da_trovare)
            return 1;
    }
    return 0;
}

function trova_prec($lista, $da_trovare){
    $da_trovare = strtoupper($da_trovare);
    $prec = NULL;
    $precid = NULL;
    $temp;
    $tempid;
    for($i=0; $i < count($lista); $i++){
        $tempid = strtoupper($lista[$i]['locid']);
        $temp = $lista[$i];
        if(strcmp($tempid, $da_trovare) < 0 ){
            if($precid == NULL){
                $precid =  $tempid;
                $prec = $temp;
            }   
            if(strcmp($tempid, $precid) > 0){
                $prec = $temp;
                $precid = $tempid;
            }  
        }
    }
    return $prec;
}

function trova_succ($lista, $da_trovare){
    $da_trovare = strtoupper($da_trovare);
    $succ = NULL;
    $succid = NULL;
    $temp;
    $tempid;

    for($i=0; $i < count($lista); $i++){
        $tempid = strtoupper($lista[$i]['locid']);
        $temp = $lista[$i];
        if(strcmp($tempid, $da_trovare) > 0 ){
            if($succid == NULL){
                $succid = $tempid;
                $succ = $temp;
            }   
            if(strcmp($tempid, $succid) < 0){
                $succ = $temp;
                $succid = $tempid;
            }   
        }
    }
    return $succ;
}

function perform_check_delete($departure_san, $arrival_san, $persons){
    global $conn;

    $res = mysqli_query($conn, "UPDATE location
                                SET booked = booked - '$persons'
                                WHERE STRCMP(locid, '$departure_san') >=0 AND STRCMP(locid, '$arrival_san') < 0;");
    if($res == false)
        return 0;
    $res = mysqli_query($conn, "UPDATE location
                                SET used = used - 1
                                WHERE locid = '$departure_san' OR locid = '$arrival_san';");
    if($res == false)
        return 0;

    $res = mysqli_query($conn, "SELECT * FROM location;");
    if($res == false)
        return 0;
    $i=0; $location = array();
    while(($templocation = mysqli_fetch_array($res, MYSQLI_ASSOC)) != NULL){
        $location[$i++] = $templocation;
    }



    $check1 = trova_locid($location, $departure_san);
    $check2 = trova_locid($location, $arrival_san);

    $prec_part = trova_prec($location, $departure_san);
    $succ_part = trova_succ($location, $departure_san);

    $prec_dest = trova_prec($location, $arrival_san);
    $succ_dest = trova_succ($location, $arrival_san);

    
    if($prec_part != NULL ){
        $prec_part_locid = $prec_part['locid'];
        $prec_part_next = $prec_part['next'];
        $prec_part_booked = $prec_part['booked'];
    }
    else{
        $prec_part_locid = NULL;
        $prec_part_next = NULL;
        $prec_part_booked = NULL;
    }

    if($succ_part != NULL){
        $succ_part_locid = $succ_part['locid'];
        $succ_part_next = $succ_part['next'];
        $succ_part_booked = $succ_part['booked'];
    }
    else{
        $succ_part_locid = NULL;
        $succ_part_next = NULL;
        $succ_part_booked = NULL;
    }

    if($prec_dest != NULL){
        $prec_dest_locid = $prec_dest['locid'];
        $prec_dest_next = $prec_dest['next'];
        $prec_dest_booked = $prec_dest['booked'];
    }
    else{
        $prec_dest_locid = NULL;
        $prec_dest_next = NULL;
        $prec_dest_booked = NULL;
    }

    if($succ_dest != NULL){
        $succ_dest_locid = $succ_dest['locid'];
        $succ_dest_next = $succ_dest['next'];
        $succ_dest_booked = $succ_dest['booked'];
    }else{
        $succ_dest_locid = NULL;
        $succ_dest_next = NULL;
        $succ_dest_booked = NULL;
    }

    $res = mysqli_query($conn, "SELECT used FROM location WHERE locid = '$departure_san'");
    if($res == false)
        return 0;
    $temp = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $flag1 = $temp['used'];

    $res = mysqli_query($conn, "SELECT used FROM location WHERE locid = '$arrival_san'");
    if($res == false)
        return 0;
    $temp = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $flag2 = $temp['used'];

    if($flag1== 0 && $prec_part_locid==NULL){
        $res = mysqli_query($conn, "DELETE FROM location WHERE locid='$departure_san';");
        if($res == false)
            return 0;
    }
    
    if($flag1==0 && $prec_part_locid!= NULL && $succ_part_locid!=NULL){
        $res = mysqli_query($conn, "DELETE FROM location WHERE locid='$departure_san';");
        if($res == false)
            return 0;
        $res = mysqli_query($conn, "UPDATE location SET next = '$succ_part_locid' WHERE locid= '$prec_part_locid';");
        if($res == false)
            return 0;
    }

    $res = mysqli_query($conn, "SELECT * FROM location;");
    if($res == false)
        return 0;
    $i=0; $location = array();
    while(($templocation = mysqli_fetch_array($res, MYSQLI_ASSOC)) != NULL){
        $location[$i++] = $templocation;
    }

    $prec_dest = trova_prec($location, $arrival_san);
    $succ_dest = trova_succ($location, $arrival_san);

    if($prec_dest != NULL){
        $prec_dest_locid = $prec_dest['locid'];
        $prec_dest_next = $prec_dest['next'];
        $prec_dest_booked = $prec_dest['booked'];
    }
    else{
        $prec_dest_locid = NULL;
        $prec_dest_next = NULL;
        $prec_dest_booked = NULL;
    }

    if($succ_dest != NULL){
        $succ_dest_locid = $succ_dest['locid'];
        $succ_dest_next = $succ_dest['next'];
        $succ_dest_booked = $succ_dest['booked'];
    }else{
        $succ_dest_locid = NULL;
        $succ_dest_next = NULL;
        $succ_dest_booked = NULL;
    }

    if($flag2 == 0 && $succ_dest_locid==NULL){
        $res = mysqli_query($conn, "DELETE FROM location WHERE locid='$arrival_san';");
        if($res == false)
            return 0;
        $res = mysqli_query($conn, "UPDATE location SET next = NULL WHERE locid= '$prec_dest_locid';");
        if($res == false)
            return 0;
    }

    if($flag2==0 && $succ_dest_locid!=NULL && $prec_dest_locid==NULL){
        $res = mysqli_query($conn, "DELETE FROM location WHERE locid='$arrival_san';");
        if($res == false)
            return 0;
    }

    if($flag2==0 && $succ_dest_locid!=NULL && $prec_dest_locid!=NULL){
        $res = mysqli_query($conn, "DELETE FROM location WHERE locid='$arrival_san';");
        if($res == false)
            return 0;

        $res = mysqli_query($conn, "UPDATE location SET next = '$succ_dest_locid' WHERE locid= '$prec_dest_locid';");
        if($res == false)
            return 0;

    }

    
    return 1;
}

?>