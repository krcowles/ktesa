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

# clear out old refs:
$hikeLine->refs = '';
$hRefs = $hikeLine->refs;

# add xml back in:
for ($j=0; $j<$noRefs2Process; $j++) {		
   if (!$skips[$j]) {  # NOTE: skips will be false for newly added refs
           $newref = $hRefs->addChild('ref');
           $newref->addChild('rtype',$rawreftypes[$j]);
           $newref->addChild('rit1',$rawrit1[$j]);
           $newref->addChild('rit2',$rawrit2[$j]);
   } 
}
?>