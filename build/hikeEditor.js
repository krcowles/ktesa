$( function () { // when page is loaded...
/*
 * The editor must direct the user to the correct edit tool, depending
 * on the type and state of the item in the table.
 *  1. HIKES table items -- all should pt to editDB (or editIndx)
 *  2. EHIKES table items:
 *      a. Status = new; point to enterHike
 *      b. Status = upl; files have been uploaded from enterHike, but no
 *          picture/map choices have been made; point to finishPage.php
 *      c. Status = sub; files have been submitted for release to HIKES;
 *          point to editDB (or editIndx)
 */
var statfields = JSON.parse(status);  // array of status fields from EHIKES
// If age = new, this array will not be empty...
var useEditor = 'editDB.php?hikeNo=';
var uid = $('#uid').text();
var umsg;
$rows = $('tbody').find('tr');
$('a').on('click', function(e) {
    e.preventDefault();
    if (age === 'new') {
        var ptr = $(this).prop('href');
        $rows.each( function(indx) {
            var currptr = $(this).find('a').prop('href');
            if (currptr == ptr) {
                // extract the hikeNo:
                var eqpos = ptr.indexOf('=') + 1;
                var hikeNo = ptr.substring(eqpos,ptr.length);
                if (statfields[indx] === 'new') {
                    umsg = "REMINDER: If you chose files in the 'File Data'" 
                        + " section:\nthey are not yet saved and you will need " 
                        + "to re-enter them";
                    useEditor = 'enterHike.php?&hno=' + hikeNo + '&usr=' + uid;
                } else if (statfields[indx] === 'upl') {
                    umsg = "REMINDER: You have uploaded files and possibly "
                        + "photos:\n Select which photos are to be " 
                        + "displayed on the hike page & map";
                    useEditor = 'finishPage.php?hno=' + hikeNo;
                } else if (statfields[indx] === 'sub') {
                    useEditor += '?hno=' + hikeNo;
                }
                alert(umsg);
                window.open(useEditor,"_blank");
            }
        });
    } else {
        var $containerCell = $(this).parent();
        var $containerRow = $containerCell.parent();
        if ( !$containerRow.hasClass('indxd') ) {
            var hikeToUse = $containerRow.data('indx');
            var callPhp = 'editDB.php?hikeNo=' + hikeToUse;
            window.open(callPhp, target="_blank");
        } else {
            var hikeToUse = $containerRow.data('indx');
            var callPhp = 'editIndx.php?hikeNo=' + hikeToUse;
            window.open(callPhp, target="_blank");
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