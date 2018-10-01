<?php
    echo
    $tmpFile = filter_input(INPUT_GET, 'file');
    if (file_exists($tmpFile)) {
        unlink($tmpFile);
    }
