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
$refDels = $_POST['delref'];
$noOfRtypes = count($rawreftypes);  # this will be > actual entered values...
$noOfRefs = 0;   # this should always be >= 1 after the loop below
for ($i=0; $i<$noOfRtypes; $i++) {
   if ($rawrit1[$i] == '') {
       $noOfRefs = $i;
       break;
   }
}
$noOfSkips = 0;
$skips = array();  # intialize all false:
for ($k=0; $k<$noOfRefs; $k++) {
   $skips[$k] = false;
}  # NOTE: this array includes any newly added refs, which have no delete checkbox
foreach ($refDels as $box) {
   if ( isset($box) ) {
       $indx = $box;
       $skips[$indx] = true;
       $noOfSkips++;
   }
}
# changes may result in the same number, fewer, or more references than before;
$noRefs2Process = $noOfRefs - $noOfSkips;
/*
* After much unsucessful effort to try to 'unset' each ref within refs, 
* the end result was to set the items within each ref to ''; 
* $prevCnt advises how many items can be replaced before needing to
* add child nodes; unset() apparently eliminates only leaf nodes, tag & all;
*/
$prevCnt = 0;
foreach ($hRefs->ref as $ritem) {
   $prevCnt++;
   $ritem->rtype = '';
   $ritem->rit1 = '';
   $ritem->rit2 = '';
}
$rindx = 0;
for ($j=0; $j<$noRefs2Process; $j++) {		
   if (!$skips[$j]) {  # NOTE: skips will be false for newly added refs
       if ($rindx < $prevCnt) {
           foreach ($hRefs->ref as $nxt) {
               if ( strlen($nxt->rtype) === 0 ) {  # should always be at least one
                   $nxt->rtype = $rawreftypes[$j];
                   $nxt->rit1 = $rawrit1[$j];
                   $nxt->rit2 = $rawrit2[$j];
                   $rindx++;
                   break;
               }    
           }
       } else {
           echo "RINDX: " . $rindx . ", PREVCNT: " . $prevCnt;
           # time to add new nodes!
           $newref = $hRefs->addChild('ref');
           $newref->addChild('rtype',"A");
           $newref->addChild('rit1',"B");
           $newref->addChild('rit2',"C");
       }
   } 
}
echo "----" . $rindx . "; prevcnt: " . $prevCnt . ";   " . $hRefs->asXML();

?>