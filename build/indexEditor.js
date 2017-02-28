$( function () { // when page is loaded...

// gray out the hike pages - this is for index pages only; for hikes, use hikeEditor.php
var $allRows = $('table tbody tr');
var $cells; 

$allRows.each( function() {
	if ( !$(this).hasClass('indxd') ) {
		$cells = $(this).children();
		$cells.each( function() {
			$(this).css('background-color','LightGray');
		});
	}
});




// make links point to php file
$('a').on('click', function(e) {
	e.preventDefault();
	var $containerCell = $(this).parent();
	var $containerRow = $containerCell.parent();
	var indxToUse = $containerRow.data('indx');
	if ( $containerRow.hasClass('indxd') ) {
		var callPhp = 'editIndx.php?hikeNo=' + indxToUse;
		//var callPhp = 'convertIndxTbls.php?hikeNo=' + indxToUse; TO CONVERT ONE-TIME
		window.open(callPhp);
		window.close(window.self);
	}
});

// gray out non-index pages??

}); // end of page is loaded...