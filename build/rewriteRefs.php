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
//$noOfRefs = mysqli_num_rows($old);
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
        if (substr($oldrefs['rit1'], 0, 8) === '60 Hikes') {
            $altReq = "UPDATE REFS SET rit1 = '2',rit2 = null WHERE " .
                "refId = {$oldref['refId']};";
            $alt = mysqli_query($link, $altReq) or die(
                "Could not update REFS for refid = {$oldref['refId']} (60 Hikes): " .
                mysqli_error($link)
            );
        }
        if (trim($oldrefs['rtype']) === 'Photo Essay:') {
            $essayReq = "UPDATE REFS SET rit1 = '20', rit2 = null WHERE " .
                "refId = {$oldref['refId']};";
        }
    }
}
mysqli_free_result($old);
echo "DONE";
$fixedReq = "SELECT * FROM REFS;";
$fixed = mysqli_query($link, $fixedReq) or die(
    "Failed to retrieve modified ref data: " . mysqli_error($link)
);
while ($updt = mysqli_fetch_assoc($fixed)) {
    if (trim($updt['rtype']) === "Book:" || trim($updt['rtype']) === "Photo Essay:") {
        if (!is_numeric($updt['rit1'])) {
            echo $updt['refId'] . ": " . $updt['rit1'];
        }
    }
}
myqli_free_result($fixed);
echo " ...ALL GO";
