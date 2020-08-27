$( function () { // when page is loaded...
/*
 * This script responds based on the current status:
 *  1. HIKES table items -- these are all 'published' and need to first be
 *     transferred 'as is' to the EHIKES (et al) tables. Then direct the user
 * 	   to the editDB.php page with the stat field = HIKES indxNo.
 *  2. EHIKES table items -- these will be directed immediately to editDB.php;
 *     If the hike was originally published, a reminder will pop up.
 */
var useEditor = 'editDB.php?tab=1&hikeNo=';
$rows = $('tbody').find('tr');
$('a:not(.navs)').each(function(i) {
	// *** THESE HIKES ARE EHIKES ***
	var ptr = $(this).prop('href');
    if (age === 'new') {
		// find the hiken no:
		if (ptr.indexOf('hikeIndx') !== -1) {
			var hikeloc = ptr.indexOf('hikeIndx=') + 9;
			var hikeno = ptr.substr(hikeloc);
			var editlnk = useEditor + hikeno;
			$(this).attr('href', editlnk);
			$(this).attr('target', '_self');
		}
	// *** THESE HIKES ARE PUBLSIHED ***
    } else {
		if (ptr.indexOf('hikeIndx') !== -1) {
			// need to differentiate between index and hike pages:
			var $containerRow = $(this).parent().parent();
			if ( !$containerRow.hasClass('indxd') ) {
				var hikeToUse = $containerRow.data('indx');
				var callPhp = 'xfrPub.php?hikeNo=' + hikeToUse;
				$(this).attr('href', callPhp);
				$(this).attr('target', '_self');
			} else {
				// there is currently no ability to edit index pages
				$containerRow.find('td').css('background-color', 'lightgray');
				$(this).on('click', function(ev) {
					ev.preventDefault();
					alert("Index pages/Visitor Centers cannot be edited");
				});
			}
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