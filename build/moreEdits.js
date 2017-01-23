$( function() {  // wait until document is loaded...

var pageType = $('#more').data('pageType');
var hike = $('#more').data('indxno');
var editThisPg;
var editDiffPg;

if (pageType == 'hike') {
	editThisPg = 'editDB.php?hikeNo=' + hike;
	editDiffPg = 'hikeEditor.php';
} else {
	editThisPg = 'editIndx.php?hikeNo=' + hike;
	editDiffPg = 'indexEditor.php';
}

$('#same').on('click', function() {
	window.open(editThisPg);
});

$('#diff').on('click', function() {
	window.open(editDiffPg);
});

}); // end of page-loading wait statement