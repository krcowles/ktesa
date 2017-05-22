$( function () { // when page is loaded...
    
$('#owpkmap').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#owflag').val("YES");
    } else {
        $('#owflag').val("NO");
    }
});


}); // end of page is loaded...