// create table elements for #sideTable
var tblItemHtml; // this will hold an html "wrapper" for items id'd for inclusion by new bounds
tblItemHtml = '<div class="tableItem"><div class="tip">Add to Favorites</div>';
//tblItemHtml += '<img class="like" src="../images/like.png" alt="favorites icon" />';
tblItemHtml += '<div class="content">';

var $tblRows = $('#refTbl tbody tr'); // table contains hike info
var refRows = $tblRows.toArray();
/**
 * the sideTbl array holds the index nos. for the hikes to be included in the
 * side table. For page load, it is assumed every hike is to be listed.
 */
var sideTbl = new Array();
for ( var i=0; i<refRows.length; i++ ) {
	sideTbl[i] = i + 1;  // there is no hike index = 0
}
formTbl(sideTbl);

// Create the html for the side table, using the rows identified in "tblRowsArray"  
function formTbl (indxArray) {
    $('#sideTable').empty();
    // extract the needed info: map.js (loaded previously) contains col. defs
    $.each(indxArray, function(i, value) {
        var tbl = tblItemHtml;
        $tblRows.each(function() {
            if ($(this).data('indx') == value && !$(this).hasClass('indxd')) {
                var lat = $(this).data('lat');
                var lng = $(this).data('lon');
                var $lnk = $(this).children().eq(hike_hdr).children().eq(0);
                $lnk.addClass('tlnk');
                tbl += $lnk[0].outerHTML;
                tbl += '<br /><span class="subtxt">Rating: '
                tbl += $(this).children().eq(diff_hdr).text();
                tbl += ' / ' + $(this).children().eq(lgth_hdr).text();
                tbl += '</span><br /><span class="subtxt">Elev Change: ';
                tbl += $(this).children().eq(elev_hdr).text();
                tbl += '</span><p id="sidelat" style="display:none">';
                tbl += lat  + '</p><p id="sidelng" style="display:none">';
                tbl += lng + '</p></div></div>';
                $('#sideTable').append(tbl);
                return false;
            }
        });
        // tooltips for 'favorites'
        $('.like').each(function() {
            var pos = $(this).offset();
            var $txtspan = $(this).parent().children().eq(0); // div holding tooltip
            $(this).on('mouseover', function() {
                var left = pos.left - 124 + 'px'; // width of tip is 120px
                var top = pos.top + 14 + 'px';
                $txtspan[0].style.top = top;
                $txtspan[0].style.left = left;
                $txtspan[0].style.display = 'block';
            });
            $(this).on('mouseout', function() {
                $txtspan[0].style.display = 'none';
            });
        });
    });
}  // end of formTbl() function

// Function to find elements within current bounds and display them in a table
function IdTableElements(boundsStr) {
    // ESTABLISH CURRENT VIEWPORT BOUNDS:
    var beginA = boundsStr.indexOf('((') + 2;
    var leftParm = boundsStr.substring(beginA,boundsStr.length);
    var beginB = leftParm.indexOf('(') + 1;
    var rightParm = leftParm.substring(beginB,leftParm.length);
    var south = parseFloat(leftParm);
    var north = parseFloat(rightParm);
    var westIndx = leftParm.indexOf(',') + 1;
    var westStr = leftParm.substring(westIndx,leftParm.length);
    var west = parseFloat(westStr);
    var eastIndx = rightParm.indexOf(',') + 1;
    var eastStr = rightParm.substring(eastIndx,rightParm.length);
    var east = parseFloat(eastStr);

    /* FIND HIKES WITHIN THE CURRENT VIEWPORT BOUNDS */
    var n = 0;
    var rowCnt = 0;
    var subtbl = [];
    var pinLat;
    var pinLon;
    $tblRows.each(function() {
        if (!$(this).hasClass('indxd')) {
            pinLat = parseFloat($(this).data('lat'));
            pinLon = parseFloat($(this).data('lon'));
            if ($(this).data('indx') == '53') {
                $x = 1;
            }
            if( pinLon <= east && pinLon >= west && pinLat <= north && pinLat >= south ) {
                var indx = $(this).data('indx');
                subtbl[n] = parseInt(indx);
                n++;
                rowCnt ++;
            }	
        }
    });
    if ( rowCnt === 0 ) {
        msg = "NO hikes in this area";
        alert(msg);
    } else {
        formTbl(subtbl);
    }
} // END: IdTableElements() FUNCTION
