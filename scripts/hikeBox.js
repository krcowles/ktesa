/**
 * Animated "New Hike" Marker box will appear on the left-hand side of the map,
 * below the 'map-style' drop-down and far enough down to ensure clearance if
 * drop-down is being used.
 * Assume that the last hike in the table is the new hike.
 */ 
var latest; // this will be the marker number of the latest hike marker
var newloc; // location of latest pin
// Determine & set box position:
var winWidth = $(window).width();
var mapWidth = $('#map').width();  // same as container size (currently 960)
if (winWidth < mapWidth) {
    $('#newHikeBox').css('left',12);
} else {
    var newHikeBoxLeft = Math.floor( (winWidth - mapWidth)/2 ) + 12;
    $('#newHikeBox').css('left',newHikeBoxLeft);
}
// Determine last hike name & hike type:
var $hikeRows = $('#refTbl tbody tr');
var lastHikeIndx = $hikeRows.length - 1; // offset 1 for header row
var $lastHikeRow = $hikeRows.eq(lastHikeIndx).find('td');
var newHikeName = $lastHikeRow.eq(1).text();
if ($hikeRows.eq(lastHikeIndx).hasClass('clustered')) {
    newHikeName = $hikeRows.eq(lastHikeIndx).data('tool'); // use cluster name
}
if ($hikeRows.eq(lastHikeIndx).hasClass('vchike')) {
    var vcIndx = $hikeRows.eq(lastHikeIndx).data('vc'); // use visitor center name
    $hikeRows.each( function() {
        if ( $(this).data('indx') == vcIndx ) { 
            var $indxRow = $(this).find('td');
            newHikeName = $indxRow.eq(1).text();
            return;
        }
    });
    newHikeName = newHikeName.replace('Index','Visitor Center');
}
$('#winner').append(newHikeName);
//$('#winner').css('color','DarkGreen');
$('#newHikeBox').css('display','block');
