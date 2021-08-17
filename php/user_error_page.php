<?php
/**
 * This page will appear only if the site is in 'Production' mode and an
 * error/exception is encountered.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$appMode = 'production';
?>
<!DOCTYPE html>

<html lang="en-us">
<head>
    <title>Error Encountered</title>
    <meta charset="utf-8" />
    <meta name="description" content="User notice of problem encountered" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <style type="text/css">
        body { 
            background-color: #eaeaea;
            margin: 0px;
        };
    </style>
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Problem Encountered</p>
<p id="active" style="display:none">Error</p>


<div style="margin-left:16px;font-size:18px;color:brown">
    <p style="margin:0;font-weight:bold;">We are sorry, but a problem has
        occurred while processing your request.</p>
    <p style="margin:0;text-indent:30px;color:black;">An email has been
        sent to the web master with details, and the problem will be
        investigated promptly.
    </p>
    <p>You may wish to try again at a later date/time.
        Thanks for your patience!</p>   
</div>

</body>
</html>
