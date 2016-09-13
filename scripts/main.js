$( function() {  // wait until document is loaded...
// ------------------

$('#b1').on('click', function() {
	window.open('pages/mapTblWithGeo.html','_blank');
});
$('#b2').on('click', function() {
	window.open('pages/mapPgWithGeo.html','_blank');
});
$('#b3').on('click', function() {
	window.open('pages/mapTblPg.html','_blank');
});
$('#b4').on('click', function() {
	window.open('pages/mapPg.html','_blank');
});

$('#turnon').on('click', function() {
	$('#more').css('display','block');
});
$('#turnoff').on('click', function() {
	
	$('#more').css('display','none');
});

}); // end of page-loading wait statement