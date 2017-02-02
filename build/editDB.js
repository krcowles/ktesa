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

}); // end of page is loaded...