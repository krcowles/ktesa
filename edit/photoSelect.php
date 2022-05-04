<?php
/**
 * This module produces the html for placing photos with selection boxes.
 * REQUIREMENTS:
 *    - picPops.js is looking for a <p id="ptype"> on the caller's page
 *      identifying the page type: Edit (This may 
 *      be related to Flickr uploads; not currently required)
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$wayPointCount = 0;
$picCount = 0;
$picreq = "SELECT * FROM ETSV WHERE indxNo = :hikeno;";
$picq = $pdo->prepare($picreq);
$picq->execute(["hikeno" => $hikeNo]);
if ($picq->rowCount() === 0) {
    $inclPix = 'NO';
    $jsTitles = "''";
    $jsDescs = "''";
    $jsMaps = "''";
} else {
    $inclPix = 'YES';
    $picarray = $picq->fetchAll(PDO::FETCH_ASSOC);
    /**
     * The $picarray may also contain waypoints, so an array of only photos is
     * required before sorting. If any photo has its 'org' field set, then all
     * 'org' fields are set and the photo array may be sorted according to it
     * ordinal assignment. If its 'org' field is empty, then use the default order.
     */
    $photos = [];
    $waypoints = [];
    foreach ($picarray as $item) {
        if (!empty($item['mid'])) {
            array_push($photos, $item);
        } else {
            array_push($waypoints, $item);
        }
    }
    if (count($photos) > 0 && strlen(($photos[0]['org'])) > 0) {
        usort($photos, "cmp");
    }
    /**
     * The location of the 'pictures' directory is needed in order to 
     * specify <img> src attribute. The issue is that the src attribute
     * can only have a relative path or absolute path. To provide the
     * correct relative path, the 'pictures' directory needs to be located.
     */
    $picpath = getPicturesDirectory();
    
    $html = '';
    $wids = [];  // 'picIdx' of waypoint
    $wdes = [];  // 'title'
    $wlat = [];
    $wlng = [];
    $wicn = []; // 'iclr' = icon symbol
    $picno = 0;
    $wptno = 0;
    $tsvId = [];
    $phDescs = []; // caption
    $hpg = [];
    $mpg = [];
    $phPics = []; // capture the link for the mid-size version of the photo
    $phWds = []; // width
    $pMap = [];  // status of 'Map It' checkbox
    $rowHt = 220; // nominal choice for photo height in div
    foreach ($photos as $pics) {
        $tsvId[$picno] = $pics['picIdx'];
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
    }
    foreach ($waypoints as $waypt) {
        $wids[$wptno] = $waypt['picIdx'];
        $wdes[$wptno] = $waypt['title'];
        $wlat[$wptno] = $waypt['lat']/LOC_SCALE;
        $wlng[$wptno] = $waypt['lng']/LOC_SCALE;
        $wicn[$wptno++] = $waypt['iclr'];
    }
    $picCount = $picno;
    $wayPointCount = $wptno;
    /**
     * Photo html creation:
     * Each photo and its checkboxes will be held in a wrapper for insertion
     * in the CSS flex box.
     */ 
    for ($i=0; $i<$picCount; $i++) { // added re-ordering via ul/li/a elements
        $wrapper = '<li id="' . $tsvId[$i] . '" class="ui-sortable-handle">';
        $wrapper .= '<a href="javascript:void(0);" class="image_link" ' .
            'style="float:none;">';
        // the div is the container for the gallery of re-orderable elements
        $wrapper .= '<div style="margin-right:12px;width:' . $phWds[$i] . 'px;' .
            'box-sizing:content-box;">';
        // 'add to page' checkbox
        $pgbox = '<input class="hpguse" type="checkbox" name="pix[]" value="' .
            $tsvId[$i];
        if ($hpg[$i] === 'Y') {
            $pgbox .= '" checked />&nbsp;Page&nbsp;&nbsp;';
        } else {
            $pgbox .= '" />&nbsp;Page&nbsp;&nbsp;';
        }
        // 'add to map' checkbox - don't allow mapping for pix w/no lat/lng:
        if ($pMap[$i]) {
            $mpbox = '<span><input class="mpguse" type="checkbox" name="mapit[]" ' .
                'value="' . $tsvId[$i];
            if ($mpg[$i] === 'Y') {
                $mpbox .= '" checked />&nbsp;Map</span><br />' . PHP_EOL;
            } else {
                $mpbox .= '" />&nbsp;Map</span><br />' . PHP_EOL;
            }
        } else {
            $mpbox = '<span class="nomap"><input class="mpguse" type="checkbox" '
                . 'name="mapit[]" value="NO" onclick="return false;" ' .
                'disabled="disabled" /><span style="color:gray">Map</span>' .
                '<br /></span>';
        }
        // 'delete photo' checkbox
        $delbox = '<input class="delp" type="checkbox" name="rem[]" value="' .
            $tsvId[$i] . '" />&nbsp;Delete<br />';
        // photo
        $photo = '<img class="allPhotos" height="200px" width="' . 
            $phWds[$i] . 'px" src="' . $picpath . $phPics[$i] . "_z.jpg" .
            '" alt="' . $phPics[$i] . '" /><br />' . PHP_EOL;
        // caption textarea
        $tawd = $phWds[$i] - 12; // padding and borders
        $caption = '<textarea class="capts" style="width:' . $tawd .
            'px;margin-bottom:12px;" name="ecap[]" maxlength="512">' .
            $phDescs[$i] . '</textarea>';
        /**
         * Add all items to $wrapper: a self-contained <li> element which can be
         * reordered (via jquery ui 'sortable' widget)
         * NOTE: If textarea is placed inside the div, for some reason it cannot
         * be edited; hence it is placed outside the div but inside the <a> link
         */
        $wrapper .= $pgbox . $mpbox . $delbox . $photo . "</div></a>" .
            $caption . "</li>";
        $html .= $wrapper;
    }
    // create the js arrays to be passed to the accompanying script:
    $jsTitles = '[';
    for ($n=0; $n<count($phPics); $n++) {
        if ($n === 0) {
            $jsTitles .= '"' . $phPics[0] . '"';
        } else {
            $jsTitles .= ',"' . $phPics[$n] . '"';
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
}
