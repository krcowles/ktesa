<?php
$dir_iterator = new RecursiveDirectoryIterator("../");
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
// could use CHILD_FIRST if you so wish

// Set the default timezone to use. Available as of PHP 5.1
//date_default_timezone_set('UTC');

$size = 0;
// $uploadDate = mktime(7, 17, 55, 2, 20, 2018);
$uploadDate = filemtime("../index.html") + 200;
echo $file . " Upload date: " . date(DATE_RFC2822, $uploadDate) . "<br>";

foreach ($iterator as $file) {
    if ($file->isFile()) {
        if ($file->getMTime() > $uploadDate) {
            echo $file . ": " . date(DATE_RFC2822, $file->getMTime()) . "<br>";
            $size += $file->getSize();
        }
    }
}

echo "\nTotal file size: ", $size, " bytes\n";
?>
