<?php
/**
 * This is a temporary helper to expedite movement of club_assets
 * into their respect regions in the database (`CLUB_ASSETS` table).
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
 */
require "../php/global_boot.php";

/**
 * Draggable elements need position:absolute, so the items need to
 * have invrementing 'top' values (height + 4px)
 */
$block = '<div class="draggable" style="top:';
$eob   = '</div>';
$files = [];
$assets= scandir("../club_hold");
for ($j=2; $j<count($assets); $j++) {
    $item = $block . ($j-2) * 42 . 'px">' . $assets[$j] . $eob;
    array_push($files, $item);
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Transfer Files</title>
    <meta charset="utf-8" />
    <meta name="description" content="Transfer Helper" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style type="text/css">
        #files {
            position: relative;
            top: 10px;
            left: 16px;
        } 
        #areas {
            position: relative;
            height: 90%;
            padding-top: 4px;
        }
        #files {
            width: 28%;
        }
        #areas {
            left: 32%;
            width: 50%;
            display: inline-block;
            opacity: 55%;
        }
        .items {
            margin-top: 1px;
            margin-bottom: 1px;
            padding-left: 6px;
            font-size: 20px;
            border: 1px solid black;
        }
        .areas {
            display: inline-block;
            margin-bottom: 12px;
            margin-right: 8px;
            width: 30%;
            height: 120px;
            line-height: 120px;
            text-align: center;
            font-size: 20px;
            border: 2px solid blue;
        }
        .is-dragover {
            box-sizing: content-box;
            background-color: gray;
        }
        .draggable {
            position: absolute;
            height: 24px;
            padding: 6px;
            font-size: 20px;
            border: 1px solid black;
            cursor: grab;
            background-color: linen;
            border-radius: 3px;
            user-select: none;
        }
        .draggable.dragging {
            cursor: grabbing;
        }
        #r1 {
            background-color: #bfd8d9;
        }
        #r2 {
            background-color: khaki;
        }
        #r3 {
            background-color: lightgreen;
        }
        #r4 {
            background-color: lightpink;
        }
        #r5 {
            background-color: #ebc7eb;
        }
        #r6 {
            background-color: powderblue;
        }
        #r7 {
            background-color: palegoldenrod;
        }
        #r8 {
            background-color: darkseagreen;
        }
        #r9 {
            background-color: #f8c9a0;
        }
        #r10 {
            background-color: #adebe9;
        }
    </style>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
    
<div id="files">
    <?php for ($i=0; $i<count($files); $i++) {
        echo $files[$i];
    }
    ?>
</div>
<div id="areas">
    <div id="r1" class="areas">Northwest Deserts</div>
    <div id="r2" class="areas">Jemez & Abiquiu</div>
    <div id="r3" class="areas">Sangre de Cristos</div>
    <div id="r4" class="areas">Northeast Plains</div>
    <div id="r5" class="areas">Mt Taylor & Zuni</div>
    <div id="r6" class="areas">Sandias & Monzanos</div>
    <div id="r7" class="areas">Gila & Bootheel</div>
    <div id="r8" class="areas">Lower Rio Grande</div>
    <div id="r9" class="areas">Sierra Blanca Region</div>
    <div id="r10" class="areas">SE New Mexico</div>
</div>

<script type="text/javascript">
    var $items = $('.draggable');
    $items.each(function(indx, item) {
        $(item).draggable();
    });
    var $areas = $('.areas');
    $areas.each(function(indx, area) {
        $(area).droppable({
            drop: function(ev, ui) {
                let draggedItem = ui.draggable;
                let dragContent = $(draggedItem).text();
                let newloc = this.innerText;
                let db_data = {filename: dragContent, region: newloc};
                $.ajax({
                    url: 'moveFile.php',
                    method: "post",
                    data: db_data,
                    dataType: "text",
                    success: function(result) {
                        if (result == "OK") {
                            alert("File successfully moved and entered in DB");
                        } else {
                            alert(result);
                        }
                    },
                    error: function() {
                        alert("No DB entry");
                    }
                });
                $(draggedItem).remove();
            }
        });
    });
</script>
</body>
</html>
