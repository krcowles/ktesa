interface RowData {
	trail: string;
	hikeno: string;
	dist: string;
	elev: string;
	gpx: string;
}
/**
 * @fileoverview This defines the compare object for 'standard' compares ("std") - ie
 * comparisons of numbers or strings - and for 'like a number' compares ("lan") which are
 * text strings composed of numbers with comma separators.
 * 
 * @author Ken Cowles
 * @version 2.0 Reformatted table options; Fixed broken 'Units Converion'
 * @version 2.1 Typescripted
 */
// To use a variable as an index into an object, the following helper function is required
// to overcome typescript complaints - compliments of
// https://dev.to/mapleleaf/indexing-objects-in-typescript-1cgi
function hasKey<O>(obj: O, key: keyof any): key is keyof O {
	return key in obj
}

/**
 * Global object used to define how table items get compared in a sort:
 */
var noPart1: number;
var noPart2: number;
var aout: number;
var bout: number;
var compare = {
	std: function(a: number | string, b: number | string) {	// standard sorting - literal
		if ( a < b ) {
			return -1;
		} else {
			return a > b ? 1 : 0;
		}
	},
	lan: function(a: string, b: string) {    // "Like A Number": extract numeric portion for sort
		// commas allowed in numbers, so;
		var indx = a.indexOf(',');
		if ( indx < 0 ) {
			aout = parseFloat(a);
		} else {
			noPart1 = 1000 * parseFloat(a);
			let thou = a.substring(indx + 1, indx + 4);
			noPart2 = parseInt(thou);
			aout = noPart1 + noPart2;
		}
		indx = b.indexOf(',');
		if ( indx < 0 ) {
			bout = parseFloat(b);
		} else {
			noPart1 = 1000 * parseFloat(b);
			let thou = b.substring(indx + 1, indx + 4);
			noPart2 = parseInt(thou);
			bout = noPart1 + noPart2;
		}
		return aout - bout;
	}
};  // end of COMPARE object

var lgth_hdr: number;
var elev_hdr: number;
var hike_hdr: number;
var eng_units: RowData[] = [];
var curr_main_state: string;
var curr_ftbl_state: string;
var ftbl_init = false;
var engtxt = "Show English Units";
var mettxt = "Show Metric Units";
/**
 * This function will convert units on the table currently displayed: i.e. either
 * '#maintbl', or one of the incarnations of '#ftable' (jqTable).
 */
function convert(
	tblid: string, jqTable: JQuery<HTMLTableSectionElement>,  to_units: string
) {
	// conversion variables:
	var newDist: string;
	var newElev: string;
	var newUnits: string;
	var dist: number;
	var elev: number;
	if (to_units === 'Metric') { // currently metric; revert to originals
		newDist = ' kms';
		newElev = ' m';
		dist = 1.61;
		elev = 0.305;
		newUnits = engtxt;
	} else { // currently English; convert TO metric
		newUnits = mettxt;
	}
	if (tblid === '#maintbl') {
		curr_main_state = newUnits;
	} else {
		curr_ftbl_state = newUnits;
	}
	/**
	 * The conversion will take place on both 'length' and 'elevation change';
	 * Convert to metric, or replace original values when converting to English
	 * Re-collect $erows, as order may have changed.
	 */
	var $erows = jqTable.find('tr');
	if (to_units === 'Metric') {
		$erows.each(function() {
			let $disttd = $(this).find('td').eq(lgth_hdr);
			let dist_txt = $disttd.text();
			let dconv = parseFloat(dist_txt);
			let newdist = dconv * dist;
			newDist = newdist.toFixed(2) + ' kms';
			$disttd.text(newDist);
			let $elevtd = $(this).find('td').eq(elev_hdr);
			let elev_txt = $elevtd.text();
			let econv = parseFloat(elev_txt);
			let newelev = econv * elev;
			newElev = newelev.toFixed() + ' m';
			$elevtd.text(newElev);
		});
	} else {
		$erows.each(function() {
			let hike = <string>$(this).data('indx');
			let $disttd = $(this).find('td').eq(lgth_hdr);
			let $elevtd = $(this).find('td').eq(elev_hdr);
			eng_units.forEach(function(obj) {
				if (obj.hikeno === hike) {
					let newdist = obj.dist;
					$disttd.text(newdist);
					let newelev = obj.elev;
					$elevtd.text(newelev);
					return;
				}
			});
		});
	}
	$('#units').text(newUnits); // new data element text
	return;
}

function setupConverter(tblid: string, org: boolean) {
	var $etable = $(tblid);
	var $etbody = $etable.find('tbody');
	var $erows = $etbody.find('tr');
	if (org) {
		// get original english units from main table first pass, and use
		// them to repopulate to avoid rounding discrepencies during conversion
		$erows.each(function() {
			let $trail = $(this).find('td').eq(hike_hdr);
			let trail = $trail.text();
			let hike = $(this).data('indx');
			let $disttd = $(this).find('td').eq(lgth_hdr);
			let orgdist = $disttd.text();
			let $elevtd = $(this).find('td').eq(elev_hdr);
			let orgelev = $elevtd.text();
			let gpxdat = $(this).data('gpx');
			let orgdata: RowData =
				{trail: trail, hikeno: hike, dist: orgdist, elev: orgelev, gpx: gpxdat};
			eng_units.push(orgdata);
		});
	}
	$('#units').off('click').on('click', function() {
		let goto = $(this).text();
		let newunits = '';
		let newbtn = '';
		if(goto.indexOf('Metric') !== -1) { // "Show Metric..."
			newunits = 'Metric';
			newbtn = engtxt;
		} else { // "Show English..."
			newunits = 'English';
			newbtn = mettxt;
		}
		if (tblid === '#maintbl') {
			curr_main_state = newbtn;
		} else {
			curr_ftbl_state = newbtn;
		}
		convert(tblid, $etbody, newunits);
	});
}

var main_rows: HTMLTableRowElement[];
var ftbl_rows: HTMLTableRowElement[];
var sort_rows: HTMLTableRowElement[];
/**
 * Apply this function to either main or results table to provide sortable headers
 * Note: provide globals ...
 */
function tableSort(id: string) {
	let $table = $(id);
	let $tbody = $table.find('tbody');
	let $controls = $table.find('th');
	if (id === '#ftable') {
		// every time a new ftable is formed, old sort criteria
		// from previous tables must be cleared:
		$controls.each(function() {
			$(this).removeClass('ascending');
			$(this).removeClass('descending');
		});
	}
	$controls.each(function() {
		// Setup hikename w/class 'ascending' since hikes are pre-sorted
		if ($(this).text() === 'Hike/Trail Name') {
			$(this).addClass('ascending');
			return;
		}
	});
	// set the global value for rows:
	if (id === '#maintbl') {
		main_rows = $tbody.find('tr').toArray();
	} else {
		ftbl_rows = $tbody.find('tr').toArray();
	}
	$controls.each(function() {
		$(this).off('click').on('click', function() {
			$tbody.empty();
			var $header = $(this);
			var order: string = $header.data('sort');
			var column: number;
			// begin the sort process
			if ($header.hasClass('ascending') || $header.hasClass('descending')) {
				$header.toggleClass('ascending descending');
				if (id === '#maintbl') 
					$tbody.append(main_rows.reverse());
				else {
					$tbody.append(ftbl_rows.reverse());
				}
			} else {
				$header.addClass('ascending');
				$header.siblings().removeClass('ascending descending');
				if (compare.hasOwnProperty(order)) {  // compare object needs method for var order
					column = $controls.index(this);
					if (id === '#maintbl') {
						main_rows.sort( function(a, b) {
							let ael = $(a).find('td').eq(column);
							let acell = ael.text();
							let bel = $(b).find('td').eq(column);
							let bcell = bel.text();
							let retval = 0;
							if (hasKey(compare, order)) {
								retval = compare[order](acell , bcell);
							}
							return retval;
						});
						$tbody.append(main_rows);
					} else {
						ftbl_rows.sort( function(a, b) {
							let ael = $(a).find('td').eq(column);
							let acell = ael.text();
							let bel = $(b).find('td').eq(column);
							let bcell = bel.text();
							let retval = 0;
							if (hasKey(compare, order)) {
								retval = compare[order](acell , bcell);
							}
							return retval;
						});
						$tbody.append(ftbl_rows);
					}
				}
			}
		});
	});
}


/**
 * When page is loaded and table is estabished:
 */
$( function() {  
	// Provide table id for main table (originally loaded with class only)
	$('.sortable')[0].id = 'maintbl';
	tableSort('#maintbl');
	// Initialize the results table html by adding column headers from main table
	let tblHdrs = $('#maintbl').html();
	let bdystrt = tblHdrs.indexOf('<tbody>');
	tblHdrs = tblHdrs.substr(0, bdystrt);
	$('#ftable').prepend(tblHdrs);
	
	// get column nos. for 'Hike/Trail Name', 'Length' and 'Elev' data
	// Note: both main and filter tables use same column
	var $htable = $('#maintbl thead');
	var $hdrs = $htable.eq(0).find('th');
	$hdrs.each( function(indx) {
		if ($(this).text() === 'Hike/Trail Name') {
			hike_hdr = indx;
		}
		if ($(this).text() === 'Length') {
			lgth_hdr = indx;
		}
		if ($(this).text() === 'Elev Chg') {
			elev_hdr = indx;
		}
	});
	// first state of newly loaded table:
	curr_main_state = mettxt;
	curr_ftbl_state = mettxt;
	setupConverter('#maintbl', true);

	$('#showfilter').on('click', function() {
		if (!ftbl_init) {
			// there is no ftable yet, so don't alter converter setup, just toggle displays
			if ($('#tblfilter').css('display') === 'none') {
				$('#tblfilter').show();
			} else {
				$('#tblfilter').hide();
			}
		} else {
			if ($('#tblfilter').css('display') === 'none') {
				$('#tblfilter').show();
				$('#refTbl').hide();
				$('#units').text(curr_ftbl_state);
				setupConverter('#ftable', false);
			} else {
				$('#tblfilter').hide();
				$('#refTbl').show();
				$('#units').text(curr_main_state);
				setupConverter('#maintbl', false);
			}
		}
    });

	$('#multimap').on('click', function() {
		$('#usermodal').show();
	});
});