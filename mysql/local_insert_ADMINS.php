<?php
require "local_mysql_connect.php";
$query1 = "INSERT INTO USERS (last_name,first_name,passwd,email) " .
    "VALUES ('Cowles','Ken','000ktesa9','krcowles29@gmail.com');";
$query2 = "INSERT INTO USERS (last_name,first_name,passwd,email) " .
    "VALUES ('Sandberg','Tom','000ktesa9','tjsandberg@yahoo.com');";
$admin1 = mysqli_query($link,$query1);
if (!$admin1) {
    echo "No luck KC: did not insert you into USERS....<br />";
} 
$admin2 = mysqli_query($link,$query2);
if (!$admin2) {
    echo "Try again - Tom did not get inserted into USERS!<br />";
}
echo "DONE";
?>