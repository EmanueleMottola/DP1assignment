<?php (isset($_COOKIE['foo']) && $_COOKIE['foo']=='bar') ? // il cookie c'è?
             header("location: ../index.php") : //se si, ritorno a index con il cookie settato
             header("location: ../front/view/nocookie.html");  // altrimenti lo mando a nocookie.html?> 
