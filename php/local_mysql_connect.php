
<?php
# Error message:
$link_fail = "<p>Error: Unable to connect to MySQL; " . 
    "Debugging errno: " . mysqli_connect_errno() . "</p>" . PHP_EOL;

# Connect:
include 'local_mysql_config.php';
$link = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE);
if (!$link) {
   die ($link_fail);
}
?>
