$( function () { // when page is loaded...

/* Each drop-down field parameter is held in a hidden <p> element; the data (text)
   in that hidden <p> element is the default that should appear in the drop-down box
   The drop-down style parameters are:
   		- locale
   		- cluster group letter id & group name
   		- hike type
   		- difficulty
   		- exposure
   		- references
*/
   
// Locale:
var sel = $('#locality').text();
$('#area').val(sel);
// Cluster group info:
var clusgrp = $('#gletr').text();
var clusnme = $('#gnme').text();
$('#cgrp').val(clusgrp);
$('#ctip').val(clusnme);
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

}); // end of page is loaded...