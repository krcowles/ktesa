<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>MySql Connect</title>
    <meta charset="utf-8" />
    <meta name="description" content="Use MySql database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <style type="text/css">
        table {
           border-collapse: collapse;
           border-style: solid;
           border-width: 3px;
        }
        thead tr {
           border-style: solid;
           border-width: 2px;
        }
    </style>
</head>

<body>
    <div>
<?php
    require '000mysql_connect.php';   # returns $link as connection
    echo "<p>Opened</p>";

    echo "<p>Removing previous instantiation of table 'test' to regenerate below</p>";
    $remtbl = mysqli_query($link,"DROP TABLE test;");
    if (!remtbl) {
        die("<p>Did not delete tbl 'test'; Check to see if already deleted" . $mysqli_error($link) . "</p>");
    }

    # AUTO_INCREMENT seems to have conditional requirement surrounding it, esp PRIMARY KEY
    $tbl = mysqli_query( $link,"CREATE TABLE test(
        indxNo int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        pgTitle varchar(30) NOT NULL,
        locale varchar(20),
        marker varchar(11),
        collection varchar(15),
        cgroup varchar(2),
        cname varchar(25),
        logistics varchar(12),
        miles varchar(4),
        feet varchar(6),
        diff varchar(14),
        fac varchar(30),
        seasons varchar(12),
        expo varchar(15),
        gpx varchar(30),
        trk varchar(30),
        lat varchar(12),
        lng varchar(12),
        aoimg1 varchar(25),
        aoimg2 varchar(25),
        purl1 varchar(100),
        purl2 varchar(100),
        dirs varchar(250),
        tips varchar(400),
        info varchar(1200),
        refs varchar(200),
        prop varchar(100),
        act varchar(100),
        tsv varchar(2000)  );" );
    if (!$tbl) {
        die("<p>CREATE TABLE failed" . mysqli_error($link) . "</p>");
    }

    $req = mysqli_query($link,"SHOW TABLES;");
    if (!$req) {
        die("<p>DB Request Failed: SHOW TABLES" . mysqli_error($link) . "</p>");
    }
    echo "<p>Results from SHOW TABLES:</p><ul>";
    while ($row = mysqli_fetch_row($req)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
?>
        <table>
            <thead>
                <tr>
                    <th>FIELD</th>
                    <th>TYPE</th>
                    <th>NULL</th>
                    <th>KEY</th>
                    <th>DEFAULT</th>
                    <th>EXTRA</th>
                </tr>
            </thead>
            <tbody>
<?php
    $tbl = mysqli_query($link,"DESCRIBE test;");
    if (!$tbl) {
        die("<p>DESCRIBE 'test' FAILED: " . mysqli_error($link) . "/p>");
    } 
    $first = true;  
    while ($row = mysqli_fetch_row($tbl)) {
        if ($first) {
            $first = false;
        } else {
            echo "<tr>";
            for ($i=0; $i<count($row); $i++) {
                echo "<td>" . $row[$i] . "</td>";
            }
            echo "</tr>" . PHP_EOL;
       }
    }
    mysqli_close($link);
?>
           </tbody>
        </table>
    <p>DONE</p>
    </div>
</body>
</html>