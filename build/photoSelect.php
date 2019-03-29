<?php
/**
 * This module produces the html for placing photos with selection boxes.
 * REQUIREMENTS:
 *    - picPops.js is looking for a <p id="ptype"> on the caller's page
 *      identifying the page type: Edit (This may 
 *      be related to Flickr uploads; not currently required)
 * PHP Version 7.1
 * 
 * @package Photos
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$picreq = "SELECT * FROM ETSV WHERE indxNo = :hikeno;";
$picq = $pdo->prepare($picreq);
$picq->execute(["hikeno" => $hikeNo]);
if ($picq->rowCount() === 0) {
    $inclPix = 'NO';
    $jsTitles = "''";
    $jsDescs = "''";
} else {
    $inclPix = 'YES';
}
$wayPointCount = 0;
if ($inclPix === 'YES') {
    $h4txt = "Please check the boxes corresponding to the pictures you wish to " .
    "include on the hike page, and those you wish to include on the geomap.";
    $html = $h4txt;
    /**
     * The location of the 'pictures' directory is needed in order to 
     * specify <img> src attribute. The issue is that the src attribute
     * can only have a relative path or absolute path. To provide the
     * correct relative path, the 'pictures' directory needs to be
     * located, which resides at "DOCUMENT_ROOT". Unfortunately, the 
     * $_SERVER[] for that var specifies the server's absolute path,
     * e.g. on the MacOS, the DOCUMENT_ROOT includes "/Users/... etc."
     * This would look like a relative path to the img tag, having a
     * location of "root"/Users/..., which doesn't exist. Therefore, it
     * is necessary to extract the correct relative path to the pictures
     * directory from wherever this code is invoked. 
     */
    $picpath = "";
    // iteratively look for the pictures directory from here, and form
    // the appropriate path:
    $current = getcwd();
    while (!in_array('pictures', scandir($current))) {
        $picpath .= "../";
        chdir('..');
        $current = getcwd();
    }
    $wids = [];
    $wdes = [];
    $wlat = [];
    $wlng = [];
    $wicn = [];
    $picCount = 0;
    $picpath .= "pictures/nsize/";
    $picno = 0;
    $tsvId = [];
    $phNames = []; // filename w/o extension
    $phDescs = []; // caption
    $hpg = [];
    $mpg = [];
    $phPics = []; // capture the link for the mid-size version of the photo
    $phWds = []; // width
    $rowHt = 220; // nominal choice for row height in div
    $maxOccupy = 940;
    while ($pics = $picq->fetch(PDO::FETCH_ASSOC)) {
        $phNames[$picno] = $pics['title'];
        $phDescs[$picno] = $pics['desc'];
        $hpg[$picno] = $pics['hpg'];
        $mpg[$picno] = $pics['mpg'];
        $wlat[$picno] = $pics['lat'];
        $wlng[$picno] = $pics['lng'];
        $wicn[$picno] = $pics['iclr'];
        if ($pics['mid']) {  // Picture
            $phPics[$picno] = $pics['mid'] . "_" . $pics['thumb'];
            $pHeight = $pics['imgHt'];
            $aspect = $rowHt/$pHeight;
            $pWidth = $pics['imgWd'];
            $phWds[$picno] = floor($aspect * $pWidth);
            $picCount++;
        } else {  // Waypoint
            $wids[$wayPointCount] = $pics['picIdx'];
            $wdes[$wayPointCount] = $phNames[$picno];
            $waylat[$wayPointCount] = $wlat[$picno];
            $waylng[$wayPointCount] = $wlng[$picno];
            $wicon[$wayPointCount++] = $wicn[$picno];
        }
        $picno++;
    }
    for ($i=0; $i<$picCount; $i++) {
        if ($phWds[$i] > $maxOccupy) {
            $aspect = $rowHt/$phWds[$i];
            $newht = floor($maxOccupy * $aspect);
            $html .= '<div style="width:' . $maxOccupy . 'px;margin-left:2px;'
                . 'margin-right:2px;display:inline-block;">';
        } else {
            $html .= '<div style="width:' . $phWds[$i] . 'px;margin-left:2px;'
                . 'margin-right:2px;display:inline-block;">';
        }
        $pgbox = '<input class="hpguse" type="checkbox" name="pix[]" value="'
            . $phNames[$i];
        if ($hpg[$i] === 'Y') {
            $pgbox .= '" checked />Page&nbsp;&nbsp;';
        } else {
            $pgbox .= '" />Page&nbsp;&nbsp;';
        }
        $html .= $pgbox;
        $mpbox = '<input class="mpguse" type="checkbox" name="mapit[]" value="'
            . $phNames[$i];
        if ($mpg[$i] === 'Y') {
            $mpbox .= '" checked />Map<br />' . PHP_EOL;
        } else {
            $mpbox .= '" />Map<br />' . PHP_EOL;
        }
        $html .= $mpbox;
        $html .= '<input class="delp" type="checkbox" name="rem[]" value="'
            . $phNames[$i] . '" />Delete<br />';
        if ($phWds[$i] > $maxOccupy) {
            $html .= '<img class="allPhotos" height="' . $newht . 'px" width="' 
            . $maxOccupy . 'px" src="' . $picpath . $phPics[$i] . "_n.jpg"
            . '" alt="' . $phNames[$i] . '" /><br />' . PHP_EOL;
        } else {
            $html .= '<img class="allPhotos" height="200px" width="' . $phWds[$i]
                . 'px" src="' . $picpath . $phPics[$i] . "_n.jpg"
                . '" alt="' . $phNames[$i]
                . '" /><br />' . PHP_EOL;
        }
        if ($phWds[$i] > $maxOccupy) {
            $tawd = $maxOccupy - 12; // textarea widths don't compute exactly
        } else {
            $tawd = $phWds[$i] - 12; 
        } 
        $html .= '<textarea class="capts" style="width:' . $tawd .
            'px" name="ecap[]" maxlength="512">' . $phDescs[$i] . "</textarea>";
        $html .= "</div>" . PHP_EOL;
    }
    // create the js arrays to be passed to the accompanying script:
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
    $html .= '</div>';
}
