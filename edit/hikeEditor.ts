declare var age: string;
declare var inEdits: string[];
declare function toggleScrollSelect(state: boolean): void;
declare function setNodDatAlerts(): void;
declare function tableSort(tableid: string): void;
/**
 * @fileoverview For each link in the table of hikes, parse for the href
 * attribute, then replace with an attribute which points to the correct
 * editor for the page type.
 * 
 * @author Ken Cowles
 * 
 * @version 2.0 Support for cluster pages
 * @version 2.1 Typescripted
 * @version 2.2 Replaced local sort with new columnSort.ts/js script
 */

$( function () { // DOM loaded
/**
 * This script responds based on the current scenario:
 *  1. The user selected Contribute->Edit A Published Hike;
 *     A list of pages already published is presented as a table.
 *     A list of any published hikes already in edit is parsed. If the hike
 *     is in edit, the corresponding table cells are grayed out, and a message
 *     appears on mouseover that the hike page is currently in edit. 
 *     The working links for these pages are modified by this script to point
 *     to the xfrPub.php script, which will transfer a copy of the page into
 *     the edit tables. If the page is a cluster page, the link contains a
 *     query string parameter to identify it (clus=y).
 *  2. The user selected Contribute->Continue Editing Your Hikes
 *     In this case, a list of pages already in edit mode is presented
 *     as a table. The links for these pages are modified by this
 *     script to point either to editDB.php if the page is a hike page,
 *     or to editClusterPage.php if the page is a cluster page.
 *  3. The user selected Contribute->Submit for Publication
 * 	   In this case, a list of all in-edit hikes created by the user is displayed.
 *     The display will be the same as Item 2, above. The links however will
 * 	   generate an email to the admin for processing the request to publish the
 *     selected hike page.
 */
var page_type = $('#page_id').text();
var useHikeEd = 'editDB.php?tab=1&hikeNo=';
var useClusEd = 'editClusterPage.php?hikeNo=';
var xfrPage   = 'xfrPub.php?hikeNo=';
var shipit    = '../edit/notifyAdmin.php?hikeNo=';
var $rows     = $('tbody').find('tr');
$('table').attr('id', 'editTbl');
var hikeno: string;
var lnk: string;
$rows.each(function() {
	let $tdlink = $(this).children().eq(0);
	let $anchor = $tdlink.children().eq(0);
	let ptr = $anchor.prop('href');
	let pubpg = $anchor.text();
	let clushike =  ptr.indexOf('clus=y') !== -1 ? true : false;
	if (ptr.indexOf('hikeIndx') !== -1) {
		// find the hike no:
		let hikeloc = ptr.indexOf('hikeIndx=') + 9;
		hikeno = ptr.substr(hikeloc); // NOTE: this picks up the clus=y parm if present
	} else {
		hikeno = '0';
		alert("Could not locate hike");
	}
	if (page_type === 'PubReq') {
		let loc = shipit + hikeno;
		// This is a request to publish a hike
		$anchor.on('click', function(ev) {
			ev.preventDefault();
			$.ajax({
				url: loc,
				method: 'get',
				dataType: 'text',
				success: function(results) {
					if (results === "OK") {
						alert("An email has been sent to the admin");
					} else {
						alert("The email did not get sent: please use Help->Contact Us");
					}
				},
				error: function (jqXHR) {
					let newDoc = document.open();
					newDoc.write(jqXHR.responseText);
					newDoc.close();
				}
			});
		});
	} else {
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
		// gray out hikes already in-edit
		if (age === 'old' && inEdits.indexOf(pubpg) !== -1) {
			$(this).css('background-color', 'gainsboro');
			$anchor.css('cursor', 'default');
			$anchor.on('click', function(ev) {
				ev.preventDefault();
			});
			let $pubrow = $anchor.parent().parent();
			$pubrow.on('mouseover', function() {
				$(this).css('cursor', 'pointer');
				let affected = <JQuery.Coordinates>$(this).offset();
				$('#ineditModal').css({
					top: affected.top + 25,
					left: affected.left - 28
				});
				$('#ineditModal').show();
				return;
			});
			$pubrow.on('mouseout', function() {
				$('#ineditModal').hide();
				return;
			});
		} else {
			$anchor.attr('href', lnk);
			$anchor.attr('target', '_self');
		}
	}
});
tableSort('#editTbl');

});