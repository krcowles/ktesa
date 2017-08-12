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

        <form target="_blank" action="newSave.php" method="GET">
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
        $newrow = file_get_contents('xmlHikeRow.txt');
        if ($newrow === false) {
            $errmsg = '<p style="color:red;font-size:20px;margin-left:16px">' .
                'Could not get string xmlHikeRow.txt: contact site master</p>';
            die ($errmsg);
        }
        $newrow = "\n" . $newrow;
        fseek($xmlfile,-8,SEEK_END);
        fwrite($xmlfile,$newrow);
        fwrite($xmlfile,"\n</rows>");
        fclose($xmlfile);
        $xmlDB = simplexml_load_file('../data/database.xml');
        if ($xmlDB === false) {
            $errout = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Could not open database: Contact Site Master</p>';
            die($errout);
        }
        $lastIndx = 0;
        $names = '[';
        foreach ($xmlDB->row as $row) {
            $thisNo = intval($row->indxNo->__toString());
            if (strpos($thisNo,".") === false && $thisNo !== 0) {
                /* Allowing for future use of 'fractional' index no's to
                 * indicate that a hike is being edited, so not using ->count()
                 */
                if ($thisNo === $lastIndx + 1) {
                    $lastIndx = $thisNo;
                    $names .= '"' . $row->pgTitle . '",';
                } else {
                    $badindx = '<p style="color:red;font-size:20px;margin-left:16px">' .
                        'Database index nos are out of sequence: contact Site Master</p>';
                    #echo "last found: " . $lastIndx . ", this: " . $thisNo;
                    die($badindx);
                }
            }
        }
        $newNo = $lastIndx + 1;
        $jsnames = substr($names,0,strlen($names)-1);
        $names = $jsnames . ']';
        # Hikes start at "1", but indices for rows start at '0'
        $xmlDB->row[$lastIndx]->indxNo = $newNo;
        $xmlDB->asXML($database);
        ?>
        <input type="hidden" name="newno" value="<?php echo $newNo;?>" />
        <div style="margin-left:16px;">
            To reserve this hike: &nbsp;&nbsp;
            <input id="saveit" type="submit" name="resrv" value="Reserve This Hike" /><br /><br />
            <span style="color:brown">You will be able to continue to add hike data to this hike,
                or proceed at a later date.</span>
        </div>
        </form>
        <script type="text/javascript">
            var hnames = <?php echo $names;?>;
        </script>
        <script src="../scripts/jquery-1.12.1.js"></script>
        <script src="newHike.js"></script>
    </body>
</html>