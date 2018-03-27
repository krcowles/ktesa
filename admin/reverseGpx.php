<?php
require "gpxedit.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Site Admin Tools</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Site Administration Tools</p>
<div style="margin-left:24px;font-size:18px;">
    <p>DONE!</p>
    <p>File with reversed track(s) is stored on site as ../gpx/reversed.gpx</p>
</div>
<script src="../scripts/dwnld.js"></script>
<script type="text/javascript">
    var filestr = `<?= $jsvar;?>`;
    download(filestr, 'reversed.gpx', 'application/octet-stream');
</script>
</body>
</html>