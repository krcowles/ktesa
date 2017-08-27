<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>TEMPLATE</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    </head>
    <body>

        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>	
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <p id="trail">HEADER</p>

        <?php
        $database = '../data/database.xml';
        $xml = simplexml_load_file($database);
        if ($xml === false) {
            die ("No db");
        }
        foreach ($xml->row as $row) {
            foreach($row->tsv->picDat as $item) {
                if (strlen($item->mid) === 0) {
                    $item->hpg = 'N';
                    $item->mpg = 'Y';
                    $item->symbol = 'Flag, Red';
                    $item->icon_size = '32x32';
                }
            }
        }
        ?>


    </body>
</html>