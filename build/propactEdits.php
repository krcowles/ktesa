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
# erase previous data, keep track of current no of tags
$prevCnt = 0;
foreach ($hProps->prop as $pitem) {
   $prevCnt++;
   $pitem->plbl = '';
   $pitem->purl = '';
   $pitem->pcot = '';
}
$pindx = 0;
for ($j=0; $j<$noProps2Process; $j++) {		
   if (!$skips[$j]) {  # NOTE: skips will be false for newly added refs
       if ($pindx < $prevCnt) {
           foreach ($hProps->prop as $nxtp) {
               if ( strlen($nxtp->plbl) === 0 ) {  # should always be at least one
                   $nxtp->plbl = $rawprops[$j];
                   $nxtp->purl = $rawplnks[$j];
                   $nxtp->pcot = $rawpctxt[$j];
                   $pindx++;
                   break;
               }    
           }
       } else {
           # time to add new nodes!
           $newref = $hProps->addChild('prop');
           $newref->addChild('plbl',$rawreftypes[$j]);
           $newref->addChild('purl',$rawrit1[$j]);
           $newref->addChild('pcot',$rawrit2[$j]);
       }
   } 
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
$prevCnt = 0;
foreach ($hActs->act as $aitem) {
   $prevCnt++;
   $aitem->albl = '';
   $aitem->aurl = '';
   $aitem->acot = '';
}
$aindx = 0;
for ($j=0; $j<$noActs2Process; $j++) {		
   if (!$skips[$j]) {  # NOTE: skips will be false for newly added refs
       if ($aindx < $prevCnt) {
           foreach ($hActs->act as $nxta) {
               if ( strlen($nxta->albl) === 0 ) {  # should always be at least one
                   $nxta->albl = $rawacts[$j];
                   $nxta->aurl = $rawalnks[$j];
                   $nxta->acot = $rawactxt[$j];
                   $aindx++;
                   break;
               }    
           }
       } else {
           # time to add new nodes!
           $newref = $hActs->addChild('act');
           $newref->addChild('albl',$rawacts[$j]);
           $newref->addChild('aurl',$rawalnks[$j]);
           $newref->addChild('acot',$rawactxt[$j]);
       }
   } 
} 
?>