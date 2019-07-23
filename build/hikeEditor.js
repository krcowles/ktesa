$( function () { // when page is loaded...
/*
 * This script responds based on the current status:
 *  1. HIKES table items -- these are all 'published' and need to first be
 *     transferred 'as is' to the EHIKES (et al) tables. Then direct the user
 * 	   to the editDB.php page with the stat field = HIKES indxNo.
 *  2. EHIKES table items -- these will be directed immediately to editDB.php;
 *     If the hike was originally published, a reminder will pop up.
 */
var uid = $('#uid').text();
var useEditor = 'editDB.php?tab=1&usr=' + uid + "&hikeNo=";
$rows = $('tbody').find('tr');
$('a:not(.navs)').on('click', function(ev) {
    ev.preventDefault();
    if (age === 'new') {  // *** THESE HIKES ARE EHIKES ***
        var ptr = $(this).prop('href');
        $rows.each( function(indx) {
            var currptr = $(this).find('a').prop('href');
            if (currptr == ptr) {
                // extract the hikeNo:
                var eqpos = ptr.indexOf('=') + 1;
                var hikeNo = ptr.substring(eqpos, ptr.length);
                var stat = statfields[indx];
                if (stat !== '0') {
                    umsg = "This hike can be viewed in \nits original state on " +
                        'the main site';
					alert(umsg);
                }
				useEditor += hikeNo;
				window.open(useEditor);
				window.close();
            }
        });
    } else { // this hike is being pulled from published HIKES
        var $containerCell = $(this).parent();
        var $containerRow = $containerCell.parent();
        if ( !$containerRow.hasClass('indxd') ) {
            var hikeToUse = $containerRow.data('indx');
            var callPhp = 'xfrPub.php?hikeNo=' + hikeToUse + '&usr=' + uid;
			window.open(callPhp);
			window.close();
        } else {
            //currently, only site master can edit index pages
            var hikeToUse = $containerRow.data('indx');
			var callPhp = 'editIndx.php?hikeNo=' + hikeToUse 
				+ '&tbl=old&usr=mstr&tab=1';
			window.open(callPhp);
			window.close();
        }
    }
});

// global object used to define how table items get compared in a sort:
var noPart1;
var noPart2;
var compare = {
	std: function(a,b) {	// standard sorting - literal
		if ( a < b ) {
			return -1;
		} else {
			return a > b ? 1 : 0;
		}
	},
	lan: function(a,b) {    // "Like A Number": extract numeric portion for sort
		// commas allowed in numbers, so;
		var indx = a.indexOf(',');
		if ( indx < 0 ) {
			a = parseFloat(a);
		} else {
			noPart1 = parseFloat(a);
			msg = a.substring(indx + 1, indx + 4);
			noPart2 = msg.valueOf();
			a = noPart1 + noPart2;
		}
		indx = b.indexOf(',');
		if ( indx < 0 ) {
			b = parseFloat(b);
		} else {
			noPart1 = parseFloat(b);
			msg = b.substring(indx + 1, indx + 4);
			noPart2 = msg.valueOf();
			b = noPart1 + noPart2;
		}
		return a - b;
	} 
};  // end of COMPARE object

$('.sortable').each( function() {
	var $table = $(this);
	var $tbody = $table.find('tbody');
	var $controls = $table.find('th');
	var rows = $tbody.find('tr').toArray();
	
	$controls.on('click', function() {
		$header = $(this);
		var order = $header.data('sort');
		var column;
		
		if ($header.is('.ascending') || $header.is('descending')) {
			$header.toggleClass('ascending descending');
			$tbody.append(rows.reverse());
		} else {
			$header.addClass('ascending');
			$header.siblings().removeClass('ascending descending');
			if (compare.hasOwnProperty(order)) {  // compare object needs method for var order
				column =$controls.index(this);
				
				rows.sort( function(a,b) {
					a = $(a).find('td').eq(column).text();
					b = $(b).find('td').eq(column).text();
					return compare[order](a,b);
				});
				
				$tbody.append(rows);
			}  // end compare
		}  // end else
	});
});  // end sortable each loop

}); // end of page is loaded...