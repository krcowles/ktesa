$(function() {

jQuery.get('diablo.tsv', function(gitData) {
	var gitTxt = gitData;
	$('#fritz').append('<p>SUCCESS</p>');
})
.fail( function() {
	document.getElementById('fritz').textContent = 'FAILED TO GET GPSV DATA';
});

	
});