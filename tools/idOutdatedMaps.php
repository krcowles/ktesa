<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Save New Hike</title>
    <meta charset="utf-8" />
    <meta name="description" content="Write hike data to TblDB.csv" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
</head>

<body>
<?php
$oldata = '../data/database.csv';
if ( ($dhandle = fopen($oldata,"r")) === false) {
    die ("Could not open database.csv");
}
$files2delete = [];

while ( ($hikeDat = fgetcsv($dhandle)) !== false ) {
    $candidate = $hikeDat[15];
    if ( strpos($candidate,"html") !== false) {
        $pdat = $hikeDat[40];
        $adat = $hikeDat[41];
        if ( strpos($pdat,$candidate) === false && strpos($adat,$candidate) === false) {
            array_push($files2delete,$candidate);
            $removeMap = '../maps/' . $candidate;
            unlink($removeMap);
        }
    }
}
echo "No of maps identified: " . count($files2delete) . '<br />';
echo "The following maps were removed:" . '<br />';
for ($i=0; $i<count($files2delete); $i++) {
    echo $files2delete[$i] . '<br />';
}
?>
</body>
</html>