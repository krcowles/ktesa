<?php
require_once "../mysql/setenv.php";
$hikeNo = filter_input(INPUT_GET,'hno');
$namereq = "SELECT pgTitle from EHIKES WHERE indxNo = {$hikeNo};";
$name = mysqli_query($link,$namereq);
if (!$name) {
    die("finishPage.php: Failed to retrieve hike name from EHIKES for hike {$hikeNo}: " .
        mysqli_error($link));
}
$nameDat = mysqli_fetch_row($name);
$hikeName = $nameDat[0];

?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Complete Hike</title>
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
    <p id="trail">Complete the Hike Creation Process</p>
    <p id="ptype" style="display:none">Finish</p>
    
    <form target="_blank" action="displayHikePg.php" method="POST">
    <div style="margin-left:16px;">
    <?php
        $piccntreq = "SELECT * FROM ETSV WHERE indxNo = {$hikeNo};";
        $piccnt = mysqli_query($link,$piccntreq);
        if (!$piccnt) {
            die("finishPage.php: Failed to get picdat from ETSV for hike {$hikeNo}: " .
                mysqli_error($link));
        }
        if (mysqli_num_rows($piccnt) === 0) {
            $inclPix = 'NO';
        } else {
            # Display photos for selection
            $picno = 0;
            $phNames = [];
            $phDescs = [];
            $phPics = [];
            $phWds = [];
            $rowHt = 220; 
            while ($pics = mysqli_fetch_assoc($piccnt)) {
                $phNames[$picno] = $pics['title'];
                $phDescs[$picno] = $pics['desc'];
                $phPics[$picno] = $pics['mid'];
                $pHeight = $pics['imgHt'];
                $aspect = $rowHt/$pHeight;
                $pWidth = $pics['imgWd'];
                $phWds[$picno] = floor($aspect * $pWidth);
                $picno += 1;
            }
            echo "<h3>Your hike is " . $hikeName . ". When you complete this step, "
                . "your hike will be submitted for publication</h3>";
            echo '<h4 style="text-indent:16px">Please check the boxes corresponding to ' .
                'the pictures you wish to include on the new page:</h4>' . "\n";
            echo '<div style="position:relative;top:-14px;margin-left:16px;">' .
                '<input id="all" type="checkbox" name="allPix" value="useAll" />&nbsp;' .
                'Use All Photos on Hike Page<br />' . "\n" .
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
            echo '<input type="hidden" name="usepics" value="' . $inclPix . '" />' . "\n";
            echo '<input type="hidden" name="hikeno" value="' . $hikeNo . '" />' . "\n";
            # build js arrays:
            $jsTitles = '[';
            for ($n=0; $n<count($phNames); $n++) {
                if ($n === 0) {
                    $jsTitles .= '"' . $phNames[0] . '"';
                } else {
                    $jsTitles .= ',"' . $phNames[$n] . '"';
                }
            }
            $jsTitles .= ']';
            $jsDescs = '[';
            for ($m=0; $m<count($phDescs); $m++) {
                if ($m === 0) {
                    $jsDescs .= '"' . $phDescs[0] . '"';
                } else {
                    $jsDescs .= ',"' . $phDescs[$m] . '"';
                }
            }
            $jsDescs .= ']';
        }
    ?>
    </div>
    </form>
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script type="text/javascript">
        var phTitles = <?php echo $jsTitles;?>;
        var phDescs = <?php echo $jsDescs;?>;
    </script>
    <script src="validateHike.js"></script>
    <script src="../scripts/picPops.js"></script>
    </body>
</html>