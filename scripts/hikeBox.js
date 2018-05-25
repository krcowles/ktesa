/**
 * Animated "New Hike" Marker box will appear on the left-hand side of the map,
 * below the 'map-style' drop-down and far enough down to ensure clearance if
 * drop-down is being used.
 * Assume that the last hike in the table is the new hike.
 */ 
var latest; // this will be the marker number of the latest hike marker
var newloc; // location of latest pin
var $box = $('#newHikeBox');
// Determine & set box position:
var winWidth = $(window).width();
var mapWidth = $('#map').width();  // same as container size (currently 960)
if (winWidth < mapWidth) {
    $box.css('left',12);
} else {
    var newHikeBoxLeft = Math.floor( (winWidth - mapWidth)/2 ) + 12;
    $box.css('left',newHikeBoxLeft);
}
// Determine last hike name & hike type:
var $hikeRows = $('#refTbl tbody tr');
var lastHikeIndx = $hikeRows.length - 1; // offset 1 for header row
var $lastHikeRow = $hikeRows.eq(lastHikeIndx).find('td');
var newHikeName = $lastHikeRow.eq(0).text();
if ($hikeRows.eq(lastHikeIndx).hasClass('clustered')) {
    newHikeName = $hikeRows.eq(lastHikeIndx).data('tool'); // use cluster name
}
if ($hikeRows.eq(lastHikeIndx).hasClass('vchike')) {
    var vcIndx = $hikeRows.eq(lastHikeIndx).data('vc'); // use visitor center name
    $hikeRows.each( function() {
        if ( $(this).data('indx') == vcIndx ) { 
            var $indxRow = $(this).find('td');
            newHikeName = $indxRow.eq(0).text();
            return;
        }
    });
    newHikeName = newHikeName.replace('Index','Visitor Center');
}
/**
 * Attempt to size the box according to the contained text:
 * The fumction 'getTextWdith()' utilizes the canvas element which has a 
 * means to measure text width - there is no inherent means via javascript.
 * 
 * @param {String} text The text to be rendered.
 * @param {String} font The css font descriptor that text is to be rendered 
 *                      with (e.g. "bold 14px verdana").
 */
function getTextWidth(text, font) {
    // re-use canvas object for better performance
    var canvas = getTextWidth.canvas || (getTextWidth.canvas = document.createElement("canvas"));
    var context = canvas.getContext("2d");
    context.font = font;
    var metrics = context.measureText(text);
    return metrics.width;
}
var boxwidth = $box.width();
var boxheight = $box.height();
var words = newHikeName.split(' ');
var wcnt = words.length;
var txt = words[0]; // prime the loop with the first word
var single = true;
var txtwidth;
for (var i=0; i<wcnt; i++) {
    txtwidth = getTextWidth(txt, "16px verdana");
    if (txtwidth >= boxwidth) {
        if (single) {
            boxwidth = txtwidth + 6; // new value for loop test
            $box.width(boxwidth);
            if ((i + 1) === wcnt) {
                break;
            }
        } else { // this reperesents a line wrap
            boxheight += 22;
            txt = words[i];
            single = true;
        }
    } else {
        if ((i + 1) === wcnt) {
            break;
        } else {
            txt += words[i];
            single = false;
        }
    }
}
$box.height(boxheight);
$('#winner').append(newHikeName);
$('#winner').css('color','DarkGreen');
$box.css('display','block');
