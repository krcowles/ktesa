<h3>Hike Reference Sources: (NOTE: Book type cannot be changed - if needed,
    delete and add a new one)</h3>
<input type="hidden" name="rno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="rid" value="<?php echo $uid;?>" />
<?php
$z = 0;  # index for creating unique id's
$refreq = "SELECT * FROM EREFS WHERE indxNo = '{$hikeNo}';";
$refq = mysqli_query($link, $refreq);
if (!$refq) {
    die("editDB.php: Failed to extract references from EREFS: " .
        mysqli_error($link));
}
while ($ritem = mysqli_fetch_assoc($refq)) {
    $rid = 'rid' . $z;
    $reftype = 'ref' . $z;
    $thisref = fetch($ritem['rtype']);
    echo '<p id="' . $rid  . '" style="display:none">' . $thisref . "</p>\n";
    echo '<label for="' . $reftype . '">Reference Type: </label>' . "\n";
    echo '<select id="' . $reftype . '" style="height:26px;width:150px;" name="rtype[]">' . "\n";
    echo '<option value="Book:" >Book</option>' . "\n";
    echo '<option value="Photo Essay:">Photo Essay</option>' . "\n";
    echo '<option value="Website:">Website</option>' . "\n";
    echo '<option value="Link:">Website</option>' . "\n"; # leftover category from index pages
    echo '<option value="App:">App</option>' . "\n";
    echo '<option value="Downloadable Doc:">Downloadable Doc</option>' . "\n";
    echo '<option value="Blog:">Blog</option>' . "\n";
    echo '<option value="On-line Map:">On-line Map</option>' . "\n";
    echo '<option value="Magazine:">Magazine</option>' . "\n";
    echo '<option value="News Article:">News Article</option>' . "\n";
    echo '<option value="Meetup Group:">Meetup Group</option>' . "\n";
    echo '<option value="Related Link:">Related Link</option>' . "\n";
    echo '<option value="Text:">Text Only - No Link</option>' . "\n";
    echo '</select><br />' . "\n";
    $rit1 = fetch($ritem['rit1']);
    $rit2 = fetch($ritem['rit2']);
    if ($thisref === 'Book:' || $thisref === 'Photo Essay:') {
        echo '<label style="text-indent:24px;">Title: </label>'
            . '<textarea style="height:20px;width:320px" name="rit1[]">' .
            $rit1 . '</textarea>&nbsp;&nbsp;';
        echo '<label>Author: </label>'
            . '<textarea style="height:20px;width:320px" name="rit2[]">' .
            $rit2 . '</textarea>&nbsp;&nbsp;'
            . '<label>Delete: </label>' .
            '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="'.
            $z . '"><br /><br />' . "\n";
    } elseif ($thisref === 'Text') {
        echo '<label>Text only item: </label><textarea style="height:20px;width:320px;" name="rit1[]">' .
            $rit1 . '</textarea><label>Delete: </label>' .
            '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
            $z . '"><br /><br />' . "\n";
    } else {
        echo '<label>Item link: </label><textarea style="height:20px;width:500px;" name="rit1[]">' .
            $rit1 . '</textarea>&nbsp;&nbsp;<label>Cick text: </label><textarea style="height:20px;width:330px;" name="rit2[]">' .
            $rit2 . '</textarea>&nbsp;&nbsp;<label>Delete: </label>' .
            '<input style="height:18px;width:18px;" type="checkbox" name="delref[]" value="' .
            $z . '"><br /><br />' . "\n";
    }
    $z++;
}
mysqli_free_result($refq);
echo '<p id="refcnt" style="display:none">' . $z . '</p>';
?>
<p><em style="font-weight:bold;">Add</em> references here:</p>
<p>Select the type of reference and its accompanying data below:</p>
<select id="href1" style="height:26px;" name="rtype[]">
    <option value="Book:" selected="selected">Book</option>
    <option value="Photo Essay:">Photo Essay</option>
    <option value="Website:">Website</option>
    <option value="App:">App</option>
    <option value="Downloadable Doc:">Downloadable Doc</option>
    <option value="Blog:">Blog</option>
    <option value="On-line Map:">On-line Map</option>
    <option value="Magazine:">Magazine</option>
    <option value="News Article:">News Article</option>
    <option value="Meetup Group:">Meetup Group</option>
    <option value="Related Link:">Related Link</option>
    <option value="Text:">Text Only - No Link</option>
</select>
Book Title/Link URL:<input id="ritA1" type="text" name="rit1[]" size="55" 
    placeholder="Book Title" />&nbsp;
Author/Click-on Text<input id="ritA2" type="text" name="rit2[]" size="35" 
    placeholder="Author Name" /><br /><br />
<select id="href2" style="height:26px;" name="rtype[]">
    <option value="Book:" selected="selected">Book</option>
    <option value="Photo Essay:">Photo Essay</option>
    <option value="Website:">Website</option>
    <option value="App:">App</option>
    <option value="Downloadable Doc:">Downloadable Doc</option>
    <option value="Blog:">Blog</option>
    <option value="On-line Map:">On-line Map</option>
    <option value="Magazine:">Magazine</option>
    <option value="News Article:">News Article</option>
    <option value="Meetup Group:">Meetup Group</option>
    <option value="Related Link:">Related Link</option>
    <option value="Text:">Text Only - No Link</option>
</select>
Book Title/Link URL:<input id="ritB1" type="text" name="rit1[]" size="55" 
    placeholder="Book Title" />&nbsp;
Author/Click-on Text<input id="ritB2" type="text" name="rit2[]" size="35" 
    placeholder="Author Name" /><br /><br />
<select id="href3" style="height:26px;" name="rtype[]">
    <option value="Book:" selected="selected">Book</option>
    <option value="Photo Essay:">Photo Essay</option>
    <option value="Website:">Website</option>
    <option value="App:">App</option>
    <option value="Downloadable Doc:">Downloadable Doc</option>
    <option value="Blog:">Blog</option>
    <option value="On-line Map:">On-line Map</option>
    <option value="Magazine:">Magazine</option>
    <option value="News Article:">News Article</option>
    <option value="Meetup Group:">Meetup Group</option>
    <option value="Related Link:">Related Link</option>
    <option value="Text:">Text Only - No Link</option>
</select>
Book Title/Link URL:<input id="ritC1" type="text" name="rit1[]" size="55" 
    placeholder="Book Title" />&nbsp;
Author/Click-on Text<input id="ritC2" type="text" name="rit2[]" size="35" 
    placeholder="Author Name" /><br /><br />
<select id="href4" style="height:26px;" name="rtype[]">
    <option value="Book:" selected="selected">Book</option>
    <option value="Photo Essay:">Photo Essay</option>
    <option value="Website:">Website</option>
    <option value="App:">App</option>
    <option value="Downloadable Doc:">Downloadable Doc</option>
    <option value="Blog:">Blog</option>
    <option value="On-line Map:">On-line Map</option>
    <option value="Magazine:">Magazine</option>
    <option value="News Article:">News Article</option>
    <option value="Meetup Group:">Meetup Group</option>
    <option value="Related Link:">Related Link</option>
    <option value="Text:">Text Only - No Link</option>
</select>
Book Title/Link URL:<input id="ritD1" type="text" name="rit1[]" size="55" 
    placeholder="Book Title" />&nbsp;
Author/Click-on Text<input id="ritD2" type="text" name="rit2[]" size="35" 
    placeholder="Author Name" /><br />

<h3>GPS Data:</h3>
<?php
$gpsreq = "SELECT * FROM EGPSDAT WHERE indxNo = '{$hikeNo}' " .
    "AND (datType = 'P' OR datType = 'A');";
$gps = mysqli_query($link, $gpsreq);
if (!$gps) {
    die(
        "tab4display.php: Failed to extract GPS Data from EGPSDAT: " .
        mysqli_error($link)
    );
}
if (mysqli_num_rows($gps) !== 0) {
    $x = 0;
    while ($gpsdat = mysqli_fetch_assoc($gps)) {
        $pl = fetch($gpsdat['label']);
        $pu = fetch($gpsdat['url']);
        $pc = fetch($gpsdat['clickText']);
        echo 'Label: <textarea class="tstyle1" name="labl[]">' .
                $pl . '</textarea>&nbsp;&nbsp;' . PHP_EOL;
        echo 'Url: <textarea class="tstyle2" name="lnk[]">' .
                $pu . '</textarea>&nbsp;&nbsp;' . PHP_EOL;
        echo 'Click-on text: <textarea class="tstyle3" name="ctxt[]">' .
                $pc . '</textarea>&nbsp;&nbsp;' . PHP_EOL
                . '<label>Delete: </label>' .
                '<input style="height:18px;width:18px;" type="checkbox" '
                . 'name="delgps[]" value="' . $x . '"><br /><br />' . PHP_EOL;
        $x++;
    }
    mysqli_free_result($gps);
}   
?>
<p><em style="color:brown;font-weight:bold;">Add</em> GPS Data:</p>
<label>Label: </label><input class="tstyle1" name="labl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="lnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="ctxt[]" size="30" /><br />
<label>Label: </label><input class="tstyle1" name="labl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="lnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="ctxt[]" size="30" />
<?php
/**
 * Conditonal message after upload:
 */
if (isset($_SESSION['gpsmsg']) && $_SESSION['gpsmsg'] !== '') {
    echo '<p style="font-size:18px;color:Blue;">The following ' .
        'action has resulted from your latest "APPLY":</p>';
    echo $_SESSION['gpsmsg'];
    $_SESSION['gpsmsg'] = '';
}
?>
<p style="font-weight:bold;margin-bottom:0px;">Upload New Data File:<br />
<em style="font-weight:normal;">
    - Note: A Reference Will Automatically Be Added When Upload Is Complete</em></p><br />
<label style="color:brown;">Upload New File
    &nbsp;(Accepted file types: gpx, html, kml)</label>&nbsp;
    <input type="file" name="newgps" />
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>	