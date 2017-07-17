<?php
/*
 * This routine extracts exif data by partially downloading the original 
 * photo - sufficient to contain the exif metadata - and writing the truncated
 * file to a temp directory, reading the exif data from that file, storing 
 * the required information in arrays, and then deleting the file
 */
# original photos assumed to be stored in the $o array
for ($k=0; $k<count($o); $k++) {
    $truncFile = 'tmp/photo' . $k;
    $exifFile = fopen($truncFile,"w");
    
}



?>