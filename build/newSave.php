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
            /*
             * NOTE: In the first part of this script, simplexml is not used,
             * but rather a basic read/write of the database file. This is to 
             * take advantage of an existing text file "xmlHikeRow.txt" 
             * which is used to add the required fields for an 'empty' page, 
             * without having to iterate "simpleXml->addChild() over and over 
             * again (which incidentally also injects poor formatting).
             */
            $xmlfile = fopen($database,"r+");
            if ($xmlfile === false) {
                $errmsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Mdl - newSave.php: Could not open database for read/write '
                        . 'to add new row: contact site master</p>';
                die ($errmsg);
            }
            $newrow = file_get_contents('xmlHikeRow.txt');
            if ($newrow === false) {
                $errmsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Mdl - newSave.php: Could not acquire string xmlHikeRow.txt: '
                        . 'contact site master</p>';
                die ($errmsg);
            }
            $newrow = "\n" . $newrow;
            fseek($xmlfile,-8,SEEK_END);
            fwrite($xmlfile,$newrow);
            fwrite($xmlfile,"\n</rows>");
            fclose($xmlfile);
            
            # now use simplexml to populate startup fields 
            $xmlDB = simplexml_load_file($database);
            if ($xmlDB === false) {
                $diemsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Mdl - newSave.php: simplexml load failed: contact Site Master</p>';
            }
            if ($newHike == '') {  # this should never happen: tested in prev. script
                $nohike = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    ' Mdl - newSave.php: Hike name empty or not received: '
                        . 'contact site master</p>';
                die($nohike);
            }
            $xmlDB->row[$hikeIndx-1]->indxNo = $hikeIndx;
            $xmlDB->row[$hikeIndx-1]->pgTitle = $newHike;
            echo '<h2 style="color:brown">You have successfully created a new '
                . 'hike for ' . $newHike . " as Hike No. " . $hikeIndx . "</h2>\n"
                . '<p>[<strong>NOT IMPLEMENTED YET:</strong>] You may edit this '
                . 'hike at any time by returning to the home page and selecting '
                . '"Edit Hike"</p><p style="font-size:20px;">NOTE: For now, '
                . 'please enter the following into the browser url bar (or click):<br />';
            echo '<a style="color:darkBlue;text-indent:24px;font-size:18px;" ' .
                    'href="enterHike.php?hikeNo=' . $hikeIndx .
                    '" target="_blank">[server]/[project]/build/enterHike.php?hikeNo=' . 
                    $hikeIndx . "</a>";
            $xmlDB->asXML($database);
        ?>
        </div>
        <p id="trail"><?php echo $newHike;?></p>
    </body>
</html>