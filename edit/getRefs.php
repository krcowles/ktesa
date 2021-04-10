<?php
/**
 * Extract references from EREFS, where they exist, and create corresponding
 * lines of html ("pre-populated section"); Also provide the user with (4) 
 * lines of html in which he/she may add references ("unpopulated section").
 * This script will be invoked by either of the two current editors: 
 * editClusterPage.php; or tab4display.php. Both of these editors have a
 * session_start(), the global_boot.php module invoked, and the $hikeIndexNo
 * specified. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$refopts = <<<ROPTS
<option value="Book:" >Book</option>
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
ROPTS;
// all books
$books    = []; // for javascript
$authors  = []; // for javascript
$bookopts = ''; // for html
$bkReq = "SELECT * FROM `BOOKS`;";
$allbooks = $pdo->query($bkReq)->fetchAll(PDO::FETCH_ASSOC);
foreach ($allbooks as $book) {
    array_push($books, $book['title']);
    array_push($authors, $book['author']);
    $bookopts .= '<option value="' . $book['indxNo'] . '">' . 
        $book['title'] . '</option>' . PHP_EOL;
}
$jsonBooks = json_encode($books);
$jsonAuths = json_encode($authors);

// page's references in EREFS
$refReq = "SELECT `rtype`,`rit1`,`rit2` FROM `EREFS` WHERE `indxNo`=?;";
$refs = $pdo->prepare($refReq);
$refs->execute([$hikeIndexNo]);
$references = $refs->fetchAll(PDO::FETCH_ASSOC);
$noOfRefs = count($references);
$rtypes = [];
$rit1s  = [];
$rit2s  = [];
foreach ($references as $ref) {
    array_push($rtypes, trim($ref['rtype']));
    array_push($rit1s, $ref['rit1']);
    array_push($rit2s, $ref['rit2']);
}
?>
<!-- Pre-populated HTML References -->
<p id="refcnt" style="display:none"><?=$noOfRefs;?></p>
<?php if (isset($_SESSION['riturl']) && $_SESSION['riturl'] !== '') {
    echo '<p style="color:brown;">' . $_SESSION['riturl'] . '</p>';
    $_SESSION['riturl'] = '';
} /* Bad URL found during save */?>
<?php for ($k=0; $k<$noOfRefs; $k++) : ?>
<p id="rtype<?= $k;?>" style="display:none"><?= $rtypes[$k];?></p>
<p id="rit1<?= $k;?>" style="display:none"><?= $rit1s[$k];?></p>
<p id="rit2<?= $k;?>" style="display:none"><?= $rit2s[$k];?></p>
<select id="sel<?= $k;?>" name="drtype[]">
    <?=$refopts;?>
</select>&nbsp;&nbsp;&nbsp;
    <?php if ($rtypes[$k] === 'Book:' || $rtypes[$k] === 'Photo Essay:') : ?>
        <select id="bkname<?=$k;?>" name="drit1[]">
            <?=$bookopts;?>
        </select>&nbsp;&nbsp;&nbsp; 
        <input  id="auth<?=$k;?>" class="refs" type="text" name="drit2[]" />
            &nbsp;&nbsp;
        <label>Delete: </label>
        <input type="checkbox" name="delref[]" value="<?=$k;?>"><br />
    <?php else : ?>
        <input id="url<?=$k;?>" class="urlbox refs" name="drit1[]"
            value="<?= $rit1s[$k];?>" />&nbsp;&nbsp;&nbsp;
        <input id="txt<?= $k;?>"  class="refs" name="drit2[]" 
            value="<?= $rit2s[$k];?>" />&nbsp;&nbsp;&nbsp;
        <label>Delete: </label>
        <input type="checkbox" name="delref[]" value="<?= $k;?>" /><br />
    <?php endif; ?>
<?php endfor; ?>

<!-- Unpopulated References -->
<p><strong><em>Add</em></strong> references here:</p>
<p>Select the type of reference and its accompanying data below:</p>
<?php for($j=0; $j<4; $j++) : ?>
    <select id="href<?= $j;?>" name="rtype[]">
        <?=$refopts;?>
    </select>&nbsp;&nbsp;&nbsp;
    <!-- Default: Book selections -->
    <span id="bk<?= $j;?>">
    <input id="usebk<?= $j;?>" class="refs" type="hidden"
        name="usebks[]" value="yes" />
    <select id="bkttl<?= $j;?>"
        name="brit1<?= $j;?>"><?=$bookopts;?>
    </select>&nbsp;&nbsp;&nbsp;
    <input id="bkauth<?= $j;?>" class="refs" type="text" name="brit2<?= $j;?>"
        value="" />
    </span>

    <!-- Invisible unless other than book type is selected: -->
    <span id="nbk<?= $j;?>">
    <input id="notbk<?= $j;?>" type="hidden" class="refs"
        name="notbks[]" value="no" />
    <input id="nr1<?= $j;?>" class="refs" type="text" name="orit1<?= $j;?>"
        value="" />&nbsp;&nbsp;&nbsp;
    <input id="nr2<?= $j;?>" class="refs" type="text" name="orit2<?= $j;?>" 
        value="" />
    </span><br />
<?php endfor; ?>
