<?php
$dir_iterator = new DirectoryIterator('../gpx');
foreach ($dir_iterator as $file) {
    if (!$file->isDot() && !$file->isDir()) {
        $gpxname = $file->getFilename();
        $ext = pathinfo($gpxname, PATHINFO_EXTENSION);
        if ($gpxname !== ".DS_Store" && strtolower($ext) === 'gpx'
            && $gpxname !== 'filler.gpx'
        ) {
            $loadFile = "../gpx/" . $gpxname;
            $xml = simplexml_load_file($loadFile);
            if ($xml->trk->count() > 1) {
                echo $gpxname . "<br>";
            }
        }
    }
}
