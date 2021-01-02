"use strict"
/**
 * @fileoverview For each link in the table of hikes, parse for the href
 * attribute, then replace with an attribute which points to the correct
 * editor for the page type.
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 Support for cluster pages
 */
$( function () { // when page is loaded...
/**
 * This script responds based on the current scenario:
 *  1. The user selected Contribute->Edit Your Published Hike
 *     In this case, a list of pages created by the user (and already
 *     published) is presented as a table. The links for these pages
 *     are modified by this script to point to the xfrPub.php scipt,
 *     which will transfer a copy of the page into the edit tables.
 *     If the page is a cluster page, the link contains a query string
 *     parameter to identify it (clus=y).
 *  2. The user selected Contribute->Continue Editing Your Hikes
 *     In this case, a list of pages already in edit mode is presented
 *     as a table. The links for these pages are modified by this
 *     script to point either to editDB.php if the page is a hike page,
 *     or to editClusterPage.php if the page is a cluster page.
 */
var useHikeEd = 'editDB.php?tab=1&hikeNo=';
var useClusEd = 'editClusterPage.php?hikeNo=';
var xfrPage   = 'xfrPub.php?hikeNo=';
var $rows      = $('tbody').find('tr');
var hikeno;
var lnk;
$rows.each(function() {
	var $tdlink = $(this).children().eq(0);
	var $anchor = $tdlink.children().eq(0);
	var ptr = $anchor.prop('href');
	var clushike =  ptr.indexOf('clus=y') !== -1 ? true : false;
	if (ptr.indexOf('hikeIndx') !== -1) {
		// find the hike no:
		var hikeloc = ptr.indexOf('hikeIndx=') + 9;
		hikeno = ptr.substr(hikeloc); // NOTE: this picks up the clus=y parm if present
	} else {
		hikeno = 0;
		alert("Could not locate hike");
	}
	// *** THESE HIKES ARE NEW (EHIKES) ***
	if (age === 'new') { // age is established in hikeEditor.php
		if (clushike) {
			lnk = useClusEd + hikeno;
		} else {
			lnk = useHikeEd + hikeno;
		}
	// *** THESE HIKES ARE PUBLSIHED ***
    } else {
		lnk = xfrPage + hikeno;	
	}
	$anchor.attr('href', lnk);
	$anchor.attr('target', '_self');
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
		var $header = $(this);
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