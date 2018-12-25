<?php
$is_success = "You have successfully uploaded your photos!";
$is_error = "Failed to upload: Contact site master";
die(json_encode([ 'success'=> $is_success, 'error'=> $error_msg]));
