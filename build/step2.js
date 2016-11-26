$( function () { // when page is loaded...

var $boxes = $('.selPic>input');

$('#all').on('change', function() {
	if ( $(this).prop('checked') === false ) {
		$boxes.each( function() {
			$(this).prop('checked',false);
		});
	} else {
		$boxes.each( function() {
			$(this).prop('checked',true);
		});
	}
});


}); // end of page is loaded...
