$( function () { // when page is loaded...

// Locale:
var sel = $('#locality').text();
$('#area').val(sel);

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