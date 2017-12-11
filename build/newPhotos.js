$(function () { // when page is loaded...

var $ckboxes = $('.ckbox');

$('#addall').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $ckboxes.each( function() {
            $(this).prop('checked',false);
        });
    } else {
        $ckboxes.each( function() {
            $(this).prop('checked',true);
        });
    }
});

});


