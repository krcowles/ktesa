$( function () { // when page is loaded...

var sel = $('#locality').text();
$('#area').val(sel);
var clusgrp = $('#gletr').text();
var clusnme = $('#gnme').text();
$('#cgrp').val(clusgrp);
$('#ctip').val(clusnme);
var htype = $('#ctype').text();
$('#type').val(htype);
var diffic = $('#dif').text();
$('#diff').val(diffic);
var exposure = $('#expo').text();
$('#sun').val(exposure);
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