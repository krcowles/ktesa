<h3>Hike Reference Sources: (NOTE: Book type cannot be changed - if needed,
    delete and add a new one)</h3>
<input type="hidden" name="rno" value="<?php echo $hikeNo;?>" />
<input type="hidden" name="rid" value="<?php echo $uid;?>" />
<?php
require_once "../mysql/dbFunctions.php";
require_once "buildFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$refreq = "SELECT * FROM EREFS WHERE indxNo = '{$hikeNo}';";
$refq = mysqli_query($link, $refreq) or die(
    "editDB.php: Failed to extract references from EREFS: " .
    mysqli_error($link)
);
$noOfRefs = mysqli_num_rows($refq);
$rtypes = [];
$rit1s = [];
$rit2s = [];
while ($refs = mysqli_fetch_assoc($refq)) {
    $reftype = fetch($refs['rtype']);
    array_push($rtypes, $reftype);
    $ritem1 = fetch($refs['rit1']);
    array_push($rit1s, $ritem1);
    $ritem2 = fetch($refs['rit2']);
    array_push($rit2s, $ritem2);
}
mysqli_free_result($refq);
// Create the book drop-down options:
$bkReq = "SELECT * FROM BOOKS;";
$bks = mysqli_query($link, $bkReq) or die(
    __FILE__ . " " . __LINE__ . "Failed to get book list: " .
    mysqli_error($link)
);
$bkopts = '';  // html for drop-down boxes
$defauth = ''; // default author when first populating selection boxes
$titles = '['; // arrays for javascript
$authors = '[';
while ($bkitem = mysqli_fetch_assoc($bks)) {
    $titles .= '"' . $bkitem['title'] . '",';
    $authors .= '"' . $bkitem['author'] . '",';
    if ($defauth === '') {
        $defauth = $bkitem['author'];
    }
    $bkopts .= '<option value="' . $bkitem['indxNo'] . '">' . 
        $bkitem['title'] . '</option>' . PHP_EOL;
}
$titles = substr($titles, 0, strlen($titles)-1) . ']';
$authors = substr($authors, 0, strlen($authors)-1) . ']';
?>
<script type=text/javascript>
    var titles = <?= $titles;?>;
    var authors = <?= $authors;?>;
</script>
<!-- Pre-populated References -->
<?php for ($k=0; $k<$noOfRefs; $k++) : ?>
<p id="rid<?= $k;?>" style="display:none"><?= $rtypes[$k];?></p>
<label for="ref<?= $k;?>">Reference Type: </label>
<select id="ref<?= $k;?>" style="height:26px;width:150px;" name="rtype[]">
    <option value="Book:" >Book</option>
    <option value="Photo Essay:">Photo Essay</option>
    <option value="Website:">Website</option>
    <option value="Link:">Website</option>
    <option value="App:">App</option>
    <option value="Downloadable Doc:">Downloadable Doc</option>
    <option value="Blog:">Blog</option>
    <option value="On-line Map:">On-line Map</option>
    <option value="Magazine:">Magazine</option>
    <option value="News Article:">News Article</option>
    <option value="Meetup Group:">Meetup Group</option>
    <option value="Related Link:">Related Link</option>
    <option value="Text:">Text Only - No Link</option>
</select><br />
<?php if ($thisref === 'Book:' || $thisref === 'Photo Essay:') : ?>
<label style="text-indent:24px;">Title: </label>
<textarea style="height:20px;width:320px"
     name="rit1[]"><?= $rit1s[$k];?></textarea>&nbsp;&nbsp;
<label>Author: </label>
<textarea style="height:20px;width:320px"
    name="rit2[]"><?= $rit2s[$k];?></textarea>&nbsp;&nbsp;
<label>Delete: </label>
<input style="height:18px;width:18px;" type="checkbox" name="delref[]" 
    value="<?= $k;?>"><br /><br />
<?php elseif ($thisref === 'Text') : ?>
<label>Text only item: </label>
<textarea style="height:20px;width:320px;" name="rit1[]"><?= $rit1s;?></textarea>
<label>Delete: </label>
<input style="height:18px;width:18px;" type="checkbox" name="delref[]"
    value="<?= $k;?>"><br /><br />
<?php else : ?>
<label>Item link: </label>
 <textarea style="height:20px;width:500px;"
    name="rit1[]"><?= $rit1s[$k];?></textarea>&nbsp;&nbsp;
<label>Cick text: </label>
<textarea style="height:20px;width:330px;" 
    name="rit2[]"><?= $rit2s[$k];?></textarea>&nbsp;&nbsp;
<label>Delete: </label>
<input style="height:18px;width:18px;" type="checkbox" name="delref[]"
    value="<?= $k;?>" /><br /><br />
<?php endif; ?>
<?php endfor; ?>
<p id="refcnt" style="display:none"><?= $k;?></p>


<!-- Unpopulated References -->
<p><em style="font-weight:bold;">Add</em> references here:</p>
<p>Select the type of reference and its accompanying data below:</p>
<?php for($j=1; $j<5; $j++) : ?>
<select id="href<?= $j;?>" style="height:26px;" name="rtype[]">
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
</select>&nbsp;&nbsp;&nbsp;
<span id="bk<?= $j;?>">
<select style="height:26px;width:360px;" id="bkttl<?= $j;?>" class="bksel"
    name="rit1[]"><?= $bkopts;?>
</select>&nbsp;&nbsp;&nbsp;
<input style="height:24px;width:280px;" class="bkauths" type="text"
    id="bkauth<?= $j;?>" name="rit2[]" value="<?= $defauth;?>" /></span>
<!-- Invisible unless other than book type is selected: -->
<span style="display:none;" id="nbk<?= $j;?>">
<input style="height:24px;width:352px;" type="text" name="rit1[]"
    id="nr1<?= $j;?>" class="upbox" />&nbsp;&nbsp;&nbsp;
<input style="height:24px;width:282px;" type="text" name="rit2[]"
    id="nr2<?= $j;?>" class="upbox" /></span><br />
<?php endfor; ?>


<!-- Pre-populated GPS Data -->
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
        echo 'Label: <textarea class="tstyle1" ' .
            'name="labl[]">' .$pl . '</textarea>&nbsp;&nbsp;' . PHP_EOL;
        echo 'Url: <textarea class="tstyle2" ' .
            'name="lnk[]">' . $pu . '</textarea>&nbsp;&nbsp;' . PHP_EOL;
        echo 'Click-on text: <textarea class="tstyle3" ' .
            'name="ctxt[]">' . $pc . '</textarea>&nbsp;&nbsp;' . PHP_EOL
            . '<label>Delete: </label>' .
            '<input style="height:18px;width:18px;" type="checkbox" '
            . 'name="delgps[]" value="' . $x . '"><br /><br />' . PHP_EOL;
        $x++;
    }
    mysqli_free_result($gps);
}   
?>
<!-- Unpopulated Data -->
<p><em style="color:brown;font-weight:bold;">Add</em> GPS Data:</p>
<label>Label: </label><input class="tstyle1" name="labl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="lnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="ctxt[]" size="30" /><br />
<label>Label: </label><input class="tstyle1" name="labl[]" size="30" />&nbsp;&nbsp;
<label>Url: </label><input class="tstyle2" name="lnk[]" size="55" />
<label style="text-indent:30px">Click-on text: </label><input class="tstyle3" name="ctxt[]" size="30" />

<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Apply the Edits&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>	