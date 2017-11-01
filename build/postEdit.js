$( function() {  // wait until document is loaded...

var pageType = $('#more').data('ptype');
var hike = $('#more').data('indxno');
var editDiffPg;

if (pageType == 'hike') {
    var viewPg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + hike;
}

$('#view').on('click', function() {
	window.open(viewPg,"_blank");
});

}); // end of page-loading wait statement