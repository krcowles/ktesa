<?php
/**
 * This script is called when the admin selects (submits) either:
 * "Delete Selected Photo" or "Create Shell to Delete". Any items
 * whose checkbox is checked will be removed.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$action = filter_input(INPUT_POST, 'submit');  // remove files or create shell?
$shell = false; // unless proven otherwise...
$shell_file = "pictures/cleanpix.sh";
// find level at which pictures directory resides
$current = getcwd();
$adminDir = $current;
$ups = 0;
while (!in_array('pictures', scandir($current))) {
    chdir('..');
    $current = getcwd();
    $ups++;
    if ($ups > 10) { 
        throw new Exception("Can't find pictures directory!");
    }
}
if (strpos($action, "Create") !== false) {
    $shell = true;
    $handle = fopen($shell_file, "w"); // this writes over any existing
    $bash_start = "#!/bin/bash\n" . "# Version 1.0\n";
    fwrite($handle, $bash_start);
    fclose($handle);
    $script = fopen($shell_file, "a"); // now append to this new file
}
$checkboxes = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : null;
$deleted = [];
$failures = [];
$msg = '';
if (isset($checkboxes)) {
    foreach ($checkboxes as $deletion) {
        $file = 'pictures/zsize/' . $deletion;
        if ($shell) {
            // append the remove command
            $cmd = "rm zsize/" . $deletion . "\n";
            fwrite($script, $cmd);
            array_push($deleted, $file);
        } else {
            if (file_exists($file)) {
                array_push($deleted, $file);
                unlink($file);
            } else {
                array_push($failures, $file);
            }
        }
    }
    if ($shell) {
        fclose($script);
        chmod($shell_file, 0755);
    }
} else {
    $msg = "No Pictures were selected for deletion";
}
chdir($adminDir);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Photos Deleted</title>
    <meta charset="utf-8" />
    <meta name="description" content="Check for extraneous photos" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="cleanPix.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Photos Deleted</p>
<p id="page_id" style="display:none">Admin</p>

</div>
<div style="margin-left:24px;">
<?php if (strpos($msg, "No pictures")) : ?>
    <?= $msg;?>
<?php else : ?>
    <span style="color:brown;font-size:18px;">
        The following (<?= count($deleted);?>) photo(s) were 
        <?php if ($shell) : ?>
            entered into the executable shell script 'cleanpix.sh'
            in pictures/;</span>
        <?php else : ?>
            deleted from pictures/ (exceptions noted):</span>
        <?php endif; ?>
    <ul style="list-style-type:square;">
        <?php for ($j=0; $j<count($deleted); $j++) : ?>
            <li><?= $deleted[$j];?></li>
        <?php endfor; ?>
    </ul>
        
    <?php if (count($failures) > 0) : ?>
    <span style="color:brown;font-size=18px;">The following files were 
        unable to be deleted:
        <ul style="list-style-type:square">
        <?php for ($k=0; $k<count($failures); $k++) : ?>
            <li><?= $failures[$k];?></li>
        <?php endfor; ?>
        </ul>
    <?php endif; ?>
    <?php if (!empty($msg)) : ?>
    <br /><?= $msg;?>
    <?php endif; ?>
<?php endif; ?>
<script src="../scripts/menus.js"></script>

</body>
</html>