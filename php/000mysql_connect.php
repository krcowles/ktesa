<?php
    DEFINE("HOSTNAME","localhost",true);
    DEFINE("USERNAME","id140870_krcowles",true);
    DEFINE("PASSWORD","000ktesa9",true);
    DEFINE("DATABASE","id140870_hikemaster",true);

    $link = mysqli_connect(HostName, UserName, PASSWORD, Database);
    if (!$link) {
        echo "<p>Error: Unable to connect to MySQL.</p>" . PHP_EOL;
        echo "<p>Debugging errno: " . mysqli_connect_errno() . "; " . 
             mysqli_connect_error() . "</p>" . PHP_EOL;
        exit;
    }
    #echo "Host information: " . mysqli_get_host_info($link) . PHP_EOL;
?>