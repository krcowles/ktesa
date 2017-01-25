$( function () { // when page is loaded...

// rule out index page editing: use indexEditor.php for index pgs
var $allRows = $('table tbody tr');
var $cells; 

$allRows.each( function() {
	if ( $(this).hasClass('indxd') ) {
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
	if ( !$containerRow.hasClass('indxd') ) {
		var hikeToUse = $containerRow.data('indx');
		var callPhp = 'editDB.php?hikeNo=' + hikeToUse;
		window.open(callPhp);
		window.close(window.self);
	}
});

}); // end of page is loaded...