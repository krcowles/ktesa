<?php
/**
 * This module produces the html for placing photos with selection boxes.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$picCount = 0;
$picreq = "SELECT * FROM ETSV WHERE indxNo = :hikeno;";
$picq = $pdo->prepare($picreq);
$picq->execute(["hikeno" => $hikeNo]);
/**
 * It is possible that waypoint data may exist when there are no photos;
 * The following waypoint arrays are required to populate tab2Display.php
 * with the javascript vars indicated at the bottom of this script. These
 * data arrays set up alternate gps formats for lat/lngs when waypoints
 * already exist in the database for the edited hike.
 */
if ($picq->rowCount() === 0) {
    $inclPix = 'NO';
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
    foreach ($picarray as $item) {
        if (!empty($item['mid'])) {
            array_push($photos, $item);
        } 
    }
    // New in php8 - null not allowed in strlen - switched to test for null...
    if (count($photos) > 0 && !is_null($photos[0]['org'])) {
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
    $picno = 0;
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
    $picCount = $picno;
    /**
     * Photo html creation:
     * Each photo and its checkboxes will be held in a wrapper for insertion
     * in the CSS flex box and div for jQueryUI sortable operation
     */ 
    for ($i=0; $i<$picCount; $i++) {
        $wrapper = '<li id="' . $tsvId[$i] . '" class="ui-sortable-handle">';
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
            $mpbox = '<input class="mpguse" type="checkbox" name="mapit[]" ' .
                'value="' . $tsvId[$i];
            if ($mpg[$i] === 'Y') {
                $mpbox .= '" checked />&nbsp;Map<br />' . PHP_EOL;
            } else {
                $mpbox .= '" />&nbsp;Map<br />' . PHP_EOL;
            }
        } else {
            $mpbox = '<input class="mpguse nomap" type="checkbox" ' .
                'name="mapit[]" value="' . $tsvId[$i] . '" ' .
                'onclick="return false;" disabled="disabled" />' .
                '<span style="color:gray">Map</span><br />';
        }
        // 'delete photo' checkbox
        $delbox = '<input class="delp" type="checkbox" name="rem[]" value="' .
            $tsvId[$i] . '" />&nbsp;Delete<br />';
        // photo
        $photo = '<img id="' . $tsvId[$i] . '" class="allPhotos" height="200px" ' .
            'width="' . $phWds[$i] . 'px" src="' . $picpath . $phPics[$i] .
            "_z.jpg" . '" alt="' . $phPics[$i] . '" /><br />' . PHP_EOL;
        // caption textarea
        $tawd = $phWds[$i] - 12; // padding and borders
        $caption = '<textarea class="capts" style="width:' . $tawd .
            'px;margin-bottom:12px;" name="ecap[]" maxlength="512">' .
            $phDescs[$i] . '</textarea>';
        /**
         * Add all items to $wrapper: a self-contained <li> element which can be
         * reordered (via jquery ui 'sortable' widget)
         */
        $wrapper .= $pgbox . $mpbox . $delbox . $photo . "</div>" .
            $caption . "</li>";
        $html .= $wrapper;
    }
}
