
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Test MySql Connect</title>
    <meta charset="utf-8" />
    <meta name="description" content="Simple connection test" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
</head>

<body>
    <div>
<?php
include 'local_mysql_config.php';
$query_fail = "<p>Query did not succeed: SHOW TABLES</p>";

$link = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE);
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
#echo "Success: A proper connection to MySQL was made! The my_db database is great." . PHP_EOL;
#echo "Host information: " . mysqli_get_host_info($link) . PHP_EOL;

$req = mysqli_query($link,"SHOW TABLES");
if (!$req) {
    die ($query_fail);
}
echo "<ul>\n";
while ($row = mysqli_fetch_row($req)) {
    echo "<li>" . $row[0] . "</li>\n";
}
echo "</ul>\nDONE";
mysqli_close($link);
?>
    </div>
</body>
</html>