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
            die("No db");
        }
        foreach ($xml->row as $row) {
            $ino = intval($row->indxNo->__toSTring());
            if ($ino > 4 &&  $ino !== 98 && $ino !== 99) {
                # no index pages...
                unset($row->content);
                unset($row->albLinks);
            }
        }
        $xml->asXML($database);
        ?>
        <p>DONE!</p>

    </body>
</html>