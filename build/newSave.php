<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Hike Reserved</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
        <style type="text/css">
            body { background-color: #dfdfdf; }
        </style>
    </head>
    <body>

        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>	
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <div style="margin-left:24px">
        <?php
            $database = '../data/database.xml';
            $hikeIndx = intval(filter_input(INPUT_GET,'newno'));
            $newHike = filter_input(INPUT_GET,'new');
            $xmlDB = simplexml_load_file($database);
            if ($xmlDB === false) {
                $diemsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Could not open database: contact Site Master</p>';
            }
            if ($newHike == '') {
                $nohike = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Hike name empty or not received: go back and enter a hike name</p>';
                die($nohike);
            }
            $xmlDB->row[$hikeIndx-1]->pgTitle = $newHike;
            echo '<h2 style="color:brown">You have successfully created a new '
                . 'hike for ' . $newHike . " as Hike No. " . $hikeIndx . "</h2>\n"
                . "<p>You may edit this hike at any time by returning to the "
                . "home page and selecting 'Edit Hike'</p>\n NOTE: For now, " .
                "please enter the following into the browser url bar (or click): ";
            echo '<a style="color:darkBlue;text-indent:24px;font-size:18px;" ' .
                    'href="enterHike.php?hikeNo=' . $hikeIndx .
                    '" target="_blank">' . 'localhost/build/enterHike.php?hikeNo=' . 
                    $hikeIndx . "</a>";
            $xmlDB->asXML($database);
        ?>
        </div>
        <p id="trail"><?php echo $newHike;?></p>
    </body>
</html>