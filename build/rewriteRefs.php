<?php
require "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
// get BOOKS authors:
$bkReq = "SELECT indxNo,title FROM BOOKS;";
$bks = mysqli_query($link, $bkReq) or die(
    "Failed to extract book titles from BOOKS: " . mysqli_error($link)
);
$titles = [];
$keys = [];
while ($books = mysqli_fetch_assoc($bks)) {
    array_push($titles, $books['title']);
    array_push($keys, $books['indxNo']);
}
mysqli_free_result($bks);
$noOfBooks = count($titles);
// get old refs:
$oldReq = "SELECT * FROM REFS;";
$old = mysqli_query($link, $oldReq) or die(
    "Failed to retrieve old ref data: " . mysqli_error($link)
);
while ($oldref = mysqli_fetch_assoc($old)) {
    for ($i=0; $i<$noOfBooks; $i++) {
        if ($oldref['rit1'] === trim($titles[$i])) {
            $replReq = "UPDATE REFS SET rit1 = '{$keys[$i]}'," .
                "rit2 = null WHERE refId = {$oldref['refId']};";
            $repl = mysqli_query($link, $replReq) or die(
                "Could not update REFS for refid = {$oldref['refId']}: " .
                mysqli_error($link)
            );
        }
    }
}
mysqli_free_result($old);
echo "DONE";
$checkReq = "SELECT * FROM REFS;";
$checks = mysqli_query($link, $checkReq) or die(
    "No data from REFS: " . mysqli_error($link)
);
while ($item = mysqli_fetch_assoc($checks)) {
    if ($item['rtype'] === 'Book:' && !is_numeric($item['rit1'])) {
        echo "ID: " . $item['refId'] . "; " . $item['rit1'];
    }
}
mysqli_free_result($checks);
echo "ALL GO";
