$( function() {  // wait until document is loaded...
// ------------------

$('#turnon').on('click', function() {
	$('#more').css('display','block');
	$(this).css('display','none');
});
$('#turnoff').on('click', function() {
	$('#more').css('display','none');
	$('#turnon').css('display','block');
});

}); // end of page-loading wait statement