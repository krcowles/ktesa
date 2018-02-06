$(function () { // when page is loaded...
var includes = [];
$('.allPhotos').each( function() {
    includes.push(0);
}); // initialize selections (photos to include) to none
var noOfPix = includes.length;

$('#addall').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $ckboxes.each( function(indx) {
            $(this).prop('checked',false);
            includes[indx] = 0;
        });
    } else {
        $ckboxes.each( function(indx) {
            $(this).prop('checked',true);
            includes[indx] = 1;
        });
    }
    // update
});
var $ckboxes = $('.ckbox');
$ckboxes.each( function() {
    $(this).on('click', function() {
        for (var i=0; i<phTitles.length; i++) {
            if ($(this).val() == phTitles[i]) {
                includes[i] = 1;
            }
        }
        var x = 0;
    });
});

});


