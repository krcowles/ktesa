<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Save Database Deletion</title>
    <meta charset="utf-8" />
    <meta name="description" content="Reomve a line in the database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
</head>

<body>	

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>

    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Confirm Deletion</p>

<?php
    $database = '../data/database.csv';
    $dbhandle = fopen($database,"r");	
    $pageNo = filter_input(INPUT_GET,'hikeno',FILTER_VALIDATE_INT);
    if ($pageNo === -1) {
        $msgout =  '<p style="color:red;font-size:22px;">NO HIKE SELECTED</p>' .
            '<p style="font-size:16px;">No hike will be deleted</p>';
        fclose($dbhandle);
    } else {
        $wholeDB = [];
        $dbindx = 0;
        while ( ($hikeDat = fgetcsv($dbhandle)) !== false ) {
            $wholeDB[$dbindx] = $hikeDat;
            $dbindx++;
        }
        fclose($dbhandle);
        # id the entry in $wholeDB that corresponds to pageNo:
        for ($k=0; $k<count($wholeDB); $k++) {
            if ($wholeDB[$k][0] === (string) $pageNo) {
                $delete = $wholeDB[$k];  # note: line nos. in the DB do not start at 0!
                break;
            }
        }
        if (filter_input(INPUT_GET,'savePg') === 'Site Master') {
            $passwd = filter_input(INPUT_GET,'mpass');
            if ($passwd !== '000ktesa') {
                die('<p style="padding-left:16px;color:brown;">Incorrect Password - save not executed</p>');
            }
            $dbhandle = fopen($database,"w");
            # Now eliminate the one page and save;
            # NOTE: index numbers are reassigned... !!
            $msgout = '<p style="font-size:18px;">The page "' . $delete[1] . 
                '" has been deleted from the database</p>';
            $newIndx = 1;
            foreach ($wholeDB as $hikedat) {
                if ($hikedat[0] !== $delete[0]) {
                    if (trim($hikedat[0]) !== 'Indx#') {
                        $hikedat[0] = $newIndx;
                        $newIndx++;
                    }
                    fputcsv($dbhandle,$hikedat);
                }
            }
            fclose($dbhandle);
        } else if (filter_input(INPUT_GET,'savePg') === 'Submit for Review') {
            $msgout = '<p style="color:Brown;font-size:20px;">Your request to delete
                the hike "' . $delete[1] . '" will be sent to the site master</p>';
            $sent = mail("krcowles29@gmail.com","User submitted hike for deletion",$msgout,'From: krcowles29@gmail.com');
            if (!$sent) {
                echo "FAILED TO SEND EMAIL...";
            }
        } else {
            die('<p style="color:brown;">Contact Site Master: Submission not recognized');
        }   
    }
?>
<div style="padding:16px;">
    <?php echo $msgout;?>
</div>

</body>

</html>