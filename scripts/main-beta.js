$( function() {  // wait until document is loaded...
// ------------------

$('#b1').on('click', function() {
	localStorage.setItem('geoLoc',true);
	window.open('pages/mapTblPg.html','_self');
});
$('#b2').on('click', function() {
	localStorage.setItem('geoLoc',true);
	window.open('pages/mapPg.html','_self');
});
$('#b3').on('click', function() {
	localStorage.setItem('geoLoc',false);
	window.open('pages/mapTblPg.html','_self');
});
$('#b4').on('click', function() {
	localStorage.setItem('geoLoc',false);
	window.open('pages/mapPg.html','_self');
});

var passIt = true;
$('#keepTryin').on('click', function() {
	window.location.href = "pages/mapTblPg.html?geoLoc="+passIt+"";
});

}); // end of page-loading wait statement