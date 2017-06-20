<?php 
    echo 
    $tmpFile = filter_input(INPUT_GET,'file');
   unlink($tmpFile);
?>
