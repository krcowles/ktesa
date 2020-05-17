<?php
/**
 * This page will appear only if the site is in 'Production' mode and an
 * error/exception is encountered.
 * PHP Version 7.1
 * 
 * @package Error
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
?>
<!DOCTYPE html>

<html lang="en-us">
<head>
    <title>Error Encountered</title>
    <meta charset="utf-8" />
    <meta name="description" content="User notice of problem encountered" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { 
            background-color: #eaeaea;
            margin: 0px;
        };
    </style>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Problem Encountered</p>
<p id="page_id" style="display:none">Error</p>


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
<script src="../scripts/menus.js"></script>
</body>
</html>
