<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>MySql Table Test</title>
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
    <?php
    require '000mysql_connect.php';
    echo "Opened";
    ?>
    <div>
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
    
    /* WHEN DATA ACTUALLY EXISTS:
    $result = mysqli_query($link,"SELECT * FROM test;");
    if (!$result) {
        die("<p>Could not open table 'test': " . mysqli_error($link) . "</p>");
    }
     */
?>
            </tbody>
        </table>
        <p>DONE</p>
    </div>
    
</body>

</html>