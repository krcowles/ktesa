$( function () { // when page is loaded...

// make links point to php file
$('a').on('click', function(e) {
	e.preventDefault();
	var $containerCell = $(this).parent();
	var $containerRow = $containerCell.parent();
	var hikeToUse = $containerRow.data('indx');
	var callPhp = 'editDB.php?hikeNo=' + hikeToUse;
	window.open(callPhp);
	window.close(window.self);
});

}); // end of page is loaded...