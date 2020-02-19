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
    // NOTE: this will also be yes when there are no pics but there are waypoints
}
$wayPointCount = 0;
$picCount = 0;
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
    $prev = $current;
    while (!in_array('pictures', scandir($current))) {
        $picpath .= "../";
        chdir('..');
        $current = getcwd();
    }
    $wids = [];  // 'picIdx' of waypoint
    $wdes = [];  // 'title'
    $wlat = [];
    $wlng = [];
    $wicn = []; // 'iclr' = icon symbol
    $picpath .= "pictures/nsize/";
    $picno = 0;
    $wptno = 0;
    $tsvId = [];
    $phNames = []; // filename w/o extension
    $phDescs = []; // caption
    $hpg = [];
    $mpg = [];
    $phPics = []; // capture the link for the mid-size version of the photo
    $phWds = []; // width
    $pMap = [];  // status of 'Map It' checkbox
    $rowHt = 220; // nominal choice for row height in div
    $maxOccupy = 940;
    while ($pics = $picq->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($pics['mid'])) {  // Picture
            $phNames[$picno] = $pics['title'];
            $phDescs[$picno] = $pics['desc'];
            $hpg[$picno] = $pics['hpg'];
            $mpg[$picno] = $pics['mpg'];
            $phPics[$picno] = $pics['mid'] . "_" . $pics['thumb'];
            $pHeight = $pics['imgHt'];
            $aspect = $rowHt/$pHeight;
            $pWidth = $pics['imgWd'];
            $pMap[$picno] = true;
            if (empty($pics['lat']) || empty($pics['lng'])) {
                $pMap[$picno] = false;
            }
            $phWds[$picno++] = floor($aspect * $pWidth);
        } else {  // Waypoint
            $wids[$wptno] = $pics['picIdx'];
            $wdes[$wptno] = $pics['title'];
            $wlat[$wptno] = $pics['lat'];
            $wlng[$wptno] = $pics['lng'];
            $wicn[$wptno++] = $pics['iclr'];
        }
    }
    $picCount = $picno;
    $wayPointCount = $wptno;
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
        // don't allow mapping for pix w/no lat/lng:
        if ($pMap[$i]) {
            $mpbox = '<input class="mpguse" type="checkbox" name="mapit[]" value="'
                . $phNames[$i];
            if ($mpg[$i] === 'Y') {
                $mpbox .= '" checked />Map<br />' . PHP_EOL;
            } else {
                $mpbox .= '" />Map<br />' . PHP_EOL;
            }
        } else {
            $mpbox = '<span class="nomap"><input class="mpguse" type="checkbox" name="mapit[]" '
                . 'value="NO" onclick="return false;" disabled="disabled" ' .
                '/><span style="color:gray">Map</span><br /></span>';
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
    $jsMaps = '[';
    for ($p=0; $p<count($pMap); $p++) {
        if ($pMap[$p]) {
            if ($p === 0) {
                $jsMaps .= '1';
            } else {
                $jsMaps .= ',1';
            }
        } else {
            if ($p === 0) {
                $jsMaps .= '0';
            } else {
                $jsMaps .= ",0";
            }
        }
    }
    $jsMaps .= ']';
    $html .= '</div>';
    chdir($prev);
}
