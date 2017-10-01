<!DOCTYPE html>

<html lang="en-us">

<head>
    <title>Error Encountered</title>
    <meta charset="utf-8" />
    <meta name="description" content="Form for entering new hike data" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { background-color: #eaeaea; };
    </style>
</head>

<body>
<div id="logo">
	<img id="hikers" src="../images/hikers.png" alt="hikers icon" />
	<p id="logo_left">Hike New Mexico</p>	
	<img id="tmap" src="../images/trail.png" alt="trail map icon" />
	<p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Problem Encountered</p>

<div style="margin-left:16px;font-size:18px;color:brown">
    <p style="margin:0;font-weight:bold;">We are sorry, but a problem has occurred processing your request.</p>
    <p style="margin:0;text-indent:30px;color:black;">Specifically:
        <?php
        $errmsgs = array(
            "Unable to connect to the server's database; this may be a " .
            "server problem - try again later..."
            
        );
        $eno = intval(filter_input(INPUT_GET,'errno'));
        $ecd = filter_input(INPUT_GET,'errcd');
        echo $errmsgs[$eno];
        ?>
    </p>
    <p>Not to worry - we have been notified!</p>
    <p>Please try again at a later date/time. Thanks for your patience!</p>   
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
</body>

</html>