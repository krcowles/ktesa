<?php
$dir_iterator = new RecursiveDirectoryIterator("../", RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
// could use CHILD_FIRST if you so wish

// Set the default timezone to use. Available as of PHP 5.1
//date_default_timezone_set('UTC');

$uploadDate = filemtime("./dummy.txt") + 20; // Upload time plus 20 seconds for unzip
//$inputDate = "02/06/2018 1:30:00"; // Use these lines to manually enter a date
//$uploadDate = strtotime($inputDate); // Use these lines to manually enter a date

echo "Upload date: " . date(DATE_RFC2822, $uploadDate) . "<br /><br />";
echo "Files changed since upload:<br />";

foreach ($iterator as $file) {
    if ($file->isFile()) {
        if ($file->getMTime() > $uploadDate) {
            //$leaf = $iterator->getSubPathName();
            if (substr($iterator->getSubPathName(), 0, 4) !== '.git') {
                echo $iterator->getSubPathName() . ": " . date(DATE_RFC2822, $file->getMTime()) . "<br>";
            }
        }
    }
}

echo "<br />Done\n";
?>
