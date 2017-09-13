<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Start A New Hike Page</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
        <link href="newHike.css" type="text/css" rel="stylesheet" />
        <script src="../scripts/jquery-1.12.1.js"></script>
        <script type="text/javascript">
            var dupName = true;  // allows for also checking empty name box
        </script>
    </head>
    <body>

        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>	
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <p id="trail">Assign New Hike</p>

        <form id="startpg" target="_blank" action="newSave.php" method="GET">
        <p style="color:brown;font-size:16px;margin-left:16px;">
            <strong>NOTE: Hitting the 'Enter' key will not submit the form on this
                page - you must use the 'Reserve This Hike' button</strong></p>

        <div id="newrow" style="margin-left:16px;font-size:18px;">
            <p>Begin by Assigning a Hike Name: &nbsp;
                <input id="newname" type="text" name="new" size="40" />
            </p>
        </div>
        <?php
        $database = '../data/database.xml';
        $xmlDB = simplexml_load_file($database);
        if ($xmlDB === false) {
            $errout = '<p style="color:red;font-size:20px;margin-left:16px">' .
                    'Mdl - newHike.php: simplexml load failed:  '
                    . 'contact site mster</p>';
            die($errout);
        }
        # Create a list of existing hike names against which js can check for dups
        # and also provide the next available indxNo to the save script ($lastIndx+1)
        $lastIndx = 0;
        $names = '[';
        foreach ($xmlDB->row as $row) {
            $thisNo = intval($row->indxNo->__toString());
            if (strpos($thisNo,".") === false && $thisNo !== 0) {
                /* Allowing for potential future use of 'fractional' index no's to
                 * indicate that a hike is being edited, so not using ->count()
                 */
                if ($thisNo === $lastIndx + 1) {
                    $lastIndx = $thisNo;
                    $names .= '"' . $row->pgTitle . '",';
                } else {
                    $badindx = '<p style="color:red;font-size:20px;margin-left:16px">' .
                        'Mdl - newHike.php: Database index nos are out of '
                            . 'sequence: contact Site Master</p>';
                    die($badindx);
                }
            }
        }
        $newNo = $lastIndx + 1;
        $jsnames = substr($names,0,strlen($names)-1);
        $names = $jsnames . ']';
        ?>
        <input id="newhikeno" type="hidden" name="newno" value="<?php echo $newNo;?>" />
        <div id="closeit" style="margin-left:16px;">
            To reserve this hike: &nbsp;&nbsp;
            <input id="saveit" type="submit" name="resrv" value="Reserve This Hike" /><br /><br />
            <span style="color:brown">You will be able to continue to add hike data to this hike,
                or proceed at a later date.</span>
        </div>
        <div id="advise" style="margin-left:16px;color:red;font-size:20px;display:none;">
            <p>This form has already been submitted and cannot be submitted again</p>
        </div>
        </form>
        <script src="newHike.js"></script>
        <script type="text/javascript">
            var hnames = <?php echo $names;?>;
        </script>
    </body>
</html>