$( function () { // when page is loaded...

/* Each drop-down field parameter is held in a hidden <p> element; the data (text)
   in that hidden <p> element is the default that should appear in the drop-down box
   The drop-down style parameters are:
   		- locale
   		- cluster group name
   		- hike type
   		- difficulty
   		- exposure
   		- references
*/
   
// Locale:
var sel = $('#locality').text();
$('#area').val(sel);
// Cluster group info:
var clusnme = $('#group').text();  // previous cluster for hike being edited (may be empty)
var currnme = clusnme;  // current selection value
$('#ctip').val(clusnme);  // show above in select box on page load
var mrkr = $('#mrkr').text();  // original state of marker type
var msg = "&nbsp;&nbsp;&nbsp;Restore Marker to: " + mrkr + "&nbsp;&nbsp;";
$('#chgBack').prepend(msg);  // if cluster is selected, record previous state

/* Special case: when changing to or from a cluster marker */
var warned = false;
$('#ctip').change(function() {  // record any changes to the marker type
	// even if changed back later, record change:
	$('#grpChg').val("YES");
	/* If marker was not originally a cluster type, prepare to change: */
	if (mrkr !== 'Cluster') {
		if (!warned) {
			window.alert("Marker will be changed to cluster type");
			$('#addbrk').css('display','block');
			$('#chg2Clus').val("YES");
			// display option to restore marker type:
			$('#oldmrkr').val(mrkr);
			$('#chgBack').css('display','inline');
			warned = true;
		}
	}
});
	
// Hike type:
var htype = $('#ctype').text();
$('#type').val(htype);
// Difficulty:
var diffic = $('#dif').text();
$('#diff').val(diffic);
// Exposure:
var exposure = $('#expo').text();
$('#sun').val(exposure);
// References section:
var refCnt = parseFloat($('#refcnt').text());
var refid;
var rid;
var refname;
for (var i=0; i<refCnt; i++) {
	refid = '#rid' + i;
	rid = $(refid).text();
	refname = '#ref' + i;
	$(refname).val(rid);
}

// When changing marker type to Cluster:


}); // end of page is loaded...