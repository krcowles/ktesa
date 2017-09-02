<?php
/* 
 * Process any and all edits to both Proposed Data and Actual Data in 
 *    the GPS Maps & Data Section: this will include:
 *  - changed items;
 *  - added items;
 *  - deleted items;
 *  - unchanged items;
 */
$rawprops = $_POST['plabl'];
$rawplnks = $_POST['plnk'];
$rawpctxt = $_POST['pctxt'];
$delProps = $_POST['delprop'];
$noOfPs = count($rawprops);  # starting assumption
for ($i=0; $i<count($rawprops); $i++) {
   if ($rawplnks[$i] == '') {
       $noOfPs = $i;
       break;
   }
}
$noOfSkips = 0;
$skips = [];  # intialize all false:
for ($k=0; $k<$noOfPs; $k++) {
   $skips[$k] = false;
}  # NOTE: this array includes any newly added prop dats, which have no delete checkbox
foreach ($delProps as $box) {
   if ( isset($box) ) {
       $indx = $box;
       $skips[$indx] = true;
       $noOfSkips++;
   }
}
# changes may result in the same number, fewer, or more references than before;
$noProps2Process = $noOfPs - $noOfSkips;

# erase previous data:
$pcnt = $hikeLine->dataProp->prop->count();
for ($i=0; $i<$pcnt; $i++) {
    unset($hikeLine->dataProp->prop[0]);
}

# re-enter modified data
$hProps = $hikeLine->dataProp;
for ($j=0; $j<$noProps2Process; $j++) {		
    $newprop = $hProps->addChild('prop');
    $newprop->addChild('plbl',$rawprops[$j]);
    $newprop->addChild('purl',$rawplnks[$j]);
    $newprop->addChild('pcot',$rawpctxt[$j]);
} 

/*
 * Basically, repeat above code, only for Actual Data variables:
 */
$rawacts = $_POST['alabl'];
$rawalnks = $_POST['alnk'];
$rawactxt = $_POST['actxt'];
$delActs = $_POST['delact'];
$noOfAs = count($rawacts);;  # starting assumption
for ($j=0; $j<count($rawacts); $j++) {
   if ($rawalnks[$j] == '') {
       $noOfAs = $j;
       break;
   }
}
$noOfSkips = 0;
$skips = [];  # intialize all false:
for ($k=0; $k<$noOfAs; $k++) {
   $skips[$k] = false;
}  # NOTE: this array includes any newly added prop dats, which have no delete checkbox
foreach ($delActs as $box) {
   if ( isset($box) ) {
       $indx = $box;
       $skips[$indx] = true;
       $noOfSkips++;
   }
}
# changes may result in the same number, fewer, or more references than before;
$noActs2Process = $noOfAs - $noOfSkips;

# erase previous data, keep track of current no of tags
$acnt = $hikeLine->dataAct->act->count();
for ($i=0; $i<$acnt; $i++) {
    unset($hikeLine->dataAct->act[0]);
}

# re-enter modified data:
$hActs = $hikeLine->dataAct;
for ($j=0; $j<$noActs2Process; $j++) {		
    $newact = $hActs->addChild('act');
    $newact->addChild('albl',$rawacts[$j]);
    $newact->addChild('aurl',$rawalnks[$j]);
    $newact->addChild('acot',$rawactxt[$j]);
} 
?>