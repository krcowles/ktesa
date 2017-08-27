<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>MySql Connect</title>
    <meta charset="utf-8" />
    <meta name="description" content="Use MySql database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
</head>

<body>
    <div>
<?php
    require '000mysql_connect.php';
    echo "Opened";
    /* Depending on what type of query is made, php will need to respond differently;
     * ---> mysqli_query returns a "RESOURCE", not a php variable;
     *      e.g. For SHOW TABLES, use mysqli_fetch_row(); 
     *          only row[0] contains anything
     * ---> these only return "TRUE":
     *      CREATE, INSERT, UPDATE, DELETE, DROP4
     */
    
    $tbl = mysqli_query($link,"CREATE TABLE tst(id int, name varchar(30));");
    if (!$tbl) {
        die("<p>CREATE TABLE failed</p>");
    }
    $req = mysqli_query($link,"SHOW TABLES;");
    if (!$req) {
        die("<p>DB Request Failed: SHOW TABLES</p>");
    }
    

    echo "<p>Results from SHOW TABLES:</p><ul>";
    while ($row = mysqli_fetch_row($req)) {
        echo "WHILE..";
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    

    mysqli_close($link);
?>
    </div>
</body>
</html>