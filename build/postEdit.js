$( function() {  // wait until document is loaded...

var pageType = $('#more').data('ptype');
var hike = $('#more').data('indxno');
var editThisPg;
var editDiffPg;
var viewPg;

if (pageType == 'hike') {
	editThisPg = 'editDB.php?hikeNo=' + hike;
	editDiffPg = 'hikeEditor.php';
	viewPg = '../pages/hikePageTemplate.php?hikeIndx=' + hike;
} else {
	editThisPg = 'editIndx.php?hikeNo=' + hike;
	editDiffPg = 'indexEditor.php';
	viewPg = '../pages/indexPageTemplate.php?hikeIndx=' + hike;
}

$('#same').on('click', function() {
	window.open(editThisPg);
});

$('#diff').on('click', function() {
	window.open(editDiffPg);
});

$('#view').on('click', function() {
	window.open(viewPg,"_blank");
});

}); // end of page-loading wait statement