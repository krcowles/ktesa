<?php
/* 
 * Process any and all edits to both Proposed Data and Actual Data in 
 * the GPS Maps & Data Section: this will include:
 *  - changed items;
 *  - added items;
 *  - deleted items;
 *  - unchanged items;
 */
$rawprops = $_POST['plabl'];
$rawplnks = $_POST['plnk'];
$rawpctxt = $_POST['pctxt'];
$delProps = $_POST['delprop'];

# Create 'delete' array big enough to include ALL refs (pre-existing and new)
$delete = [];
for ($k=0; $k<count($rawplnks); $k++) {
   $delete[$k] = false;
}
foreach ($delProps as $box) {
   if ( isset($box) ) {
       $indx = $box;
       $delete[$indx] = true;
   }
}
# erase previous data:
$pcnt = $hikeLine->dataProp->prop->count();
for ($i=0; $i<$pcnt; $i++) {
    unset($hikeLine->dataProp->prop[0]);
}
/*
 * Add xml for proposed data back in
 * NOTE: it's possible to have interceding empty boxes, so it is necessary
 * to process ALL boxes
 */
$hProps = $hikeLine->dataProp;
for ($j=0; $j<count($rawplnks); $j++) {
    if (!$delete[$j] && $rawplnks[$j] !== '') {
        $newprop = $hProps->addChild('prop');
        $newprop->addChild('plbl',$rawprops[$j]);
        $newprop->addChild('purl',$rawplnks[$j]);
        $newprop->addChild('pcot',$rawpctxt[$j]);
    }
} 

/*
 * Basically, repeat above code, only for Actual Data variables:
 */
$rawacts = $_POST['alabl'];
$rawalnks = $_POST['alnk'];
$rawactxt = $_POST['actxt'];
$delActs = $_POST['delact'];

# Create 'delete' array big enough to include ALL refs (pre-existing and new)
$delete = [];
for ($k=0; $k<count($rawalnks); $k++) {
   $skips[$k] = false;
}
foreach ($delActs as $box) {
   if ( isset($box) ) {
       $indx = $box;
       $delete[$indx] = true;
   }
}
# erase previous data, keep track of current no of tags
$acnt = $hikeLine->dataAct->act->count();
for ($i=0; $i<$acnt; $i++) {
    unset($hikeLine->dataAct->act[0]);
}

$hActs = $hikeLine->dataAct;
for ($j=0; $j<count($rawalnks); $j++) {
    if (!$delete[$j] && $rawalnks[$j] !== '') {
        $newact = $hActs->addChild('act');
        $newact->addChild('albl',$rawacts[$j]);
        $newact->addChild('aurl',$rawalnks[$j]);
        $newact->addChild('acot',$rawactxt[$j]);
    }
} 
$hikeLine->dataAct->asXML('rob.xml');
die ("Want");
?>