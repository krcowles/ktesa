<?php
$msg = "This is a test";
if (mail("krcowles29@gmail.com", "Test mail", $msg)) {
    echo "OK";
} else {
    echo "NO GO";
}