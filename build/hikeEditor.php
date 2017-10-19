<?php
$usr = filter_input(INPUT_GET,'usr');
$age = filter_input(INPUT_GET,'age');
$show = filter_input(INPUT_GET,'show');
if ($show === 'inx') {
    $tab = 'Select Index Page to Edit';
} else {
    $tab = 'Select Hike to Edit';
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?php echo $tab;?></title>
    <meta charset="utf-8" />
    <meta name="description"
            content="Select hike to edit from table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="tables.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />

</head>

<body>

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>

    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $tab;?></p>

<div><p style="text-align:center;">When you click on the "Web Pg" link in the table
    below, you will be presented with an editable version of the hike page.</p>
</div>
<p style="display:none"><?php echo $age;?></p>
<div><br />
<?php 
    require "../php/TblConstructor.php";
?>
</div>

<script type="text/javascript">
    var age = "<?php echo $age;?>";
    var status = '<?php echo $javastat;?>';
    var icnt = "<?php echo $usrcnt;?>";
</script>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="hikeEditor.js"></script>
</body>
</html>