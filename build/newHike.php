<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Start A New Hike Page</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
        <link href="newHike.css" type="text/css" rel="stylesheet" />
    </head>
    <body>

        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>	
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <p id="trail">Assign New Hike</p>

        <form target="_blank" action="enterHike.php" method="GET">
        <div id="newrow" style="margin-left:16px;font-size:18px;">
            <p>Begin by Assigning a Hike Name: &nbsp;
                <input id="newname" type="text" name="new" size="40" />
            </p>
        </div>
        <?php
        $database = '../data/database.xml';
        $xmlfile = fopen($database,"r+");
        if ($xmlfile === false) {
            $errmsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                'Could not open database to add new row: contact site master</p>';
            die ($errmsg);
        }
        fseek($xmlfile,-8,SEEK_END);
        $newrow = file_get_contents('xmlHikeRow.txt');
        if ($newrow === false) {
            $errmsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                'Could not get string xmlHikeRow.txt: contact site master</p>';
            die ($errmsg);
        }
        $newrow = "\n" . $newrow;
        fwrite($xmlfile,$newrow);
        fwrite($xmlfile,"\n</rows>");
        fclose($xmlfile);
        $xmlDB = simplexml_load_file('../data/database.xml');
        if ($mxlDB === false) {
            $errout = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Could not open database: Contact Site Master</p>';
            die($errout);
        }
        $newNo = $xmlDB->row->count() + 1; 
        foreach ($xmlDB->row as $row) {
            echo "; Row" . $row->indxNo . ":" . $row->pgTitle . '<br />';
        }
        # NOTE: addChild requires string content:
        die ("CHECK FILE");
        if ($newRow === false) {
            $errmsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Could not get new Hike Row xml as txt: contact Site Master</p>';
            die ($errmsg);
        }
        $rowXml = $xmlDB->addChild('row',$newrow);
        $rowXml->asXML('tmp.xml');
        ?>
        <p id="assigned" style="display:none;"><?php echo $newNo;?></p>
        <input type="hidden" name="newno" value="<?php echo $newNo;?>" />
        <div style="margin-left:16px;">
            To save this hike and return later to complete it:
            <button id="save">Save &amp; Return</button><br /><br />
            To continue with data entry for this hike:
            <button id="cont">Continue to Create</button>
        </div>
            
        </form>

        <script src="../scripts/jquery-1.12.1.js"></script>
        <script src="newHike.js"></script>
    </body>
</html>