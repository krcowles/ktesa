<?php
/**
 * This maintenance script will walk through the links found on the hike
 * pages reference section (REFS table) in order to validate their existence.
 * Some sites become obsolete and may need to be updated. Since the php
 * get_headers() function seems to generate errors and NOT return 'false'
 * as the manual would suggest, it is necessary to handle those errors
 * by throwing an ErrorException [lines18-21], which can then be seen by a
 * try-catch block. This catches, at least, timeouts when a site no longer
 * exists. Note that because of timeouts, this script can take quite awhile
 * to complete. Also, when attempting to get headers from an unsecured http 
 * site, it is necessary to introduce the stream_context_set_default() function
 * in order to prevent more errors :: thanks to Stackoverflow.com:
 * https://stackoverflow.com/questions/40830265/php-errors-with-get-headers-and-ssl
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

set_error_handler(
    function ($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }   
);
$allRefs = $pdo->query("SELECT `rit1`,`indxNo` FROM `REFS`;");
$tableRefs = $allRefs->fetchAll(PDO::FETCH_KEY_PAIR);
$rit1      = array_keys($tableRefs);
$links     = [];
$hikenos   = [];
$bad_lnks  = [];
$lnk_indx  = [];
for ($k=0; $k<count($rit1); $k++) {
    $url = $rit1[$k];
    if (strpos($url, "http") !== false) {
        array_push($links, $url);
        array_push($hikenos, $tableRefs[$url]);
    }
}

// prepare get_headers() for SSL problems with http sites:
stream_context_set_default(
    [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]
);
// begin the validation process
for ($i=0; $i<count($links); $i++) {
    try {
        $hdrs = get_headers($links[$i]);
    } catch (Exception $ex) {
        array_push($bad_lnks, $links[$i]);
        array_push($lnk_indx, $hikenos[$i]);
    }
}
// Format for use in javascript ajax: contents of array must be strings
$jsLinks = [];
foreach ($bad_lnks as $bad) {
    array_push($jsLinks, "'" . $bad . "'");
}
$jsUrls = "[" . implode(",", $jsLinks) . "]";
?>

<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>List Bad Links</title>
    <meta charset="utf-8" />
    <meta name="description" content="Check for urls in REFS that no longer work" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/linkValidate.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
<body>

<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Hike Page Link Check</p>
<p id="active" style="display:none">Admin</p>

<div id="contents">
    <p>The following links are no longer valid:</p>

    <table id="lnk_results">
        <thead>
            <tr>
                <th>Hike Page No.</th>
                <th>Non-working Link</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($j=0; $j<count($bad_lnks); $j++) : ?>
            <tr>
                <td><?=$lnk_indx[$j];?></td>
                <td><?=$bad_lnks[$j];?></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table><br />

    <div>
    <button id="del_lnks" type="button" class="btn btn-secondary">
        Delete Links</button>
    </div>
</div>

<script>
$('#del_lnks').on('click', function() {
    let jslinks = <?=$jsUrls;?>;
    let links   = JSON.stringify(jslinks);
    let ajaxdata = {links: links};
    $.ajax({
        url: 'deleteBadLinks.php',
        data: ajaxdata,
        dataType: 'text',
        method: "post",
        success: function(status) {
            if (status === 'ok') {
                alert("Links deleted");
            } else {
                alert("Problem encountered");
            }
        }
    });
});
</script>

</body>
</html>
