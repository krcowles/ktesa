<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>TEMPLATE</title>
        <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
        <link href="validateHike.css" type="text/css" rel="stylesheet" />
    </head>
    <body>

    <div id="logo">
        <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
        <p id="logo_left">Hike New Mexico</p>	
        <img id="tmap" src="../images/trail.png" alt="trail map icon" />
        <p id="logo_right">w/Tom &amp; Ken</p>
    </div>
    <p id="trail">HEADER</p>

    <form target="_blank" action="displayHikePg.php" method="POST">
    <div style="margin-left:16px;">
    <?php
        $valNo = filter_input(INPUT_GET,'hikeNo',FILTER_VALIDATE_INT);
        $hikeNo = $valNo - 1;
        $xml = simplexml_load_file('../data/database.xml');
        if ($xml === false) {
            $errmsg = '<p style="margin-left:16px;color:red;font-size:18px;">' .
                'Could not open database: contact Site Master</p>';
            die($errmsg);
        }
        if ($xml->row[$hikeNo]->tsv->picDat->count() === 0) {
            $inclPix = 'NO';
        } else {
            # Display photos for selection
            $picno = 0;
            $phNames = [];
            $phPics = [];
            $phWds = [];
            $rowHt = 220; 
            foreach ($xml->row[$hikeNo]->tsv->picDat as $imgData) {
                $phNames[$picno] = $imgData->title;
                $phPics[$picno] = $imgData->mid;
                $pHeight = $imgData->imgHt;
                $aspect = $rowHt/$pHeight;
                $pWidth = $imgData->imgWd;
                $phWds[$picno] = floor($aspect * $pWidth);
                $picno += 1;
            }
            $mdat = $xml->row[$hikeNo]->tsv->asXML();
            $mdat = preg_replace('/\n/','', $mdat);
            $mdat = preg_replace('/\t/','', $mdat);

            echo '<h4 style="text-indent:16px">Please check the boxes corresponding to ' .
                'the pictures you wish to include on the new page:</h4>' . "\n";
            echo '<div style="position:relative;top:-14px;margin-left:16px;">' .
                '<input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;' .
                'Use All Photos on Hike Page<br />' . "\n";
                '<input id="mall" type="checkbox" name="allMap" value="mapAll" />&nbsp;' .
                'Use All Photos on Map' . "\n";
            echo "</div>\n";
            echo '<div style="margin-left:16px;">' . "\n";

            for ($i=0; $i<$picno; $i++) {
                echo '<div class="selPic" style="width:' . $phWds[$i] . 'px;float:left;'
                        . 'margin-left:2px;margin-right:2px;">';
                echo '<input class="hpguse" type="checkbox" name="pix[]" value="' .  $phNames[$i] .
                    '" />Display&nbsp;&nbsp;';
                echo '<input class="mpguse" type="checkbox" name="mapit[]" value="' . $phNames[$i] .
                     '" />Map<br />' . "\n";
                echo '<img class="allPhotos" height="200px" width="' . $phWds[$i] . 'px" src="' .
                        $phPics[$i] . '" alt="' . $phNames[$i] . '" />' . "\n";
                echo "</div>\n";
            }
            echo "</div>\n";

            echo '<div style="width:200px;position:relative;top:90px;left:20px;float:left;">' .
                '<input type="submit" value="Create Page w/This Data" /><br /><br />' . "\n";
            echo "</div>\n";

            echo '<div class="popupCap"></div>' . "\n";
            $inclPix = 'YES';
        }
        echo '<input type="hidden" name="usepics" value="' . $inclPix . '" />' . "\n";
        echo '<input type="hidden" name="hikeno" value="' . $hikeNo . '" />' . "\n";
    ?>
    </div>
    </form>
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script type="text/javascript">
        var mouseDat = $.parseXML("<?php echo $mdat;?>");
        var phTitles = [];
        var phDescs = [];
    </script>
    <script src="validateHike.js"></script>
    <script src="../scripts/picPops.js"></script>
    </body>
</html>