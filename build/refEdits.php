<?php
/* 
 * Process any and all edited references: this will include:
 *  - changed references;
 *  - added references;
 *  - deleted references;
 *  - unchanged references;
 */
$rawreftypes = $_POST['rtype'];
$rawrit1 = $_POST['rit1'];
$rawrit2 = $_POST['rit2'];
$refDels = $_POST['delref'];  # Note: checkboxes only for pre-existing refs

# Create 'delete' array big enough to include ALL refs (pre-existing and new)
$delete = [];
for ($i=0; $i<count($rawrit1); $i++) {
    $delete[$j] = false;
}
foreach ($refDels as $box) {
    if ( isset($box) ) {
       $indx = $box;  # values in each delete box set to an id no.
       $delete[$indx] = true;
   }
}
# clear out old refs:
$rcnt = $hikeLine->refs->ref->count();
for ($i=0; $i<$rcnt; $i++) {
    unset($hikeLine->refs->ref[0]);
}
/*
 * Add xml for refs back in
 * NOTE: it's possible to have interceding empty boxes, so it is necessary
 * to process ALL boxes
 */
$hRefs = $hikeLine->refs;
for ($j=0; $j<count($rawrit1); $j++) {		
   if (!$delete[$j] && $rawrit1[$j] !== '') {
        $newref = $hRefs->addChild('ref');
        $newref->addChild('rtype',$rawreftypes[$j]);
        $newref->addChild('rit1',urlencode($rawrit1[$j]));
        $newref->addChild('rit2',$rawrit2[$j]);
   } 
}
?>