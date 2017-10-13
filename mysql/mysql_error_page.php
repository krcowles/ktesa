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
            "server problem - try again later...",
            "The data for this hike could not be accessed.",
            "Your user data did not get entered: try again - if problem "
            . "persists, we will look into it!",
            "The photo data for the map did not get retrieved - we will look " .
            "into this issue",
            "Could not process the table to add a row: we'll look into this"
        );
        $eno = intval(filter_input(INPUT_GET,'eno',FILTER_SANITIZE_NUMBER_INT));
        $ecd = filter_input(INPUT_GET,'ecd');
        echo $errmsgs[$eno];
        if ( mail("krcowles29@gmail.com","user error","Msg No " . $eno . 
                "; Code: " . $ecd) ) {
            # This works on 000webhost, but not on local server
            echo '<p>Not to worry - we have been notified!</p>';
        } else {
            echo "<p>Mail did NOT get sent, so please send us an email by using " .
                    "the link below, and describe the symptoms:<br />";
            echo '<a href="mailto:krcowles29@gmail.com">Click here!</a></p>';
        }
        ?>
    </p>
    <p>You may wish to try again at a later date/time. Thanks for your patience!</p>   
</div>

</body>

</html>