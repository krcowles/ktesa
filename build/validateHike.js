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

$('#overTsv').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $(this).val("YES");
    } else {
        $(this).val("NO");
    }
});

$('#overMap').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $(this).val("YES");
    } else {
        $(this).val("NO");
    }
});
$('#overGpx').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $(this).val("YES");
    } else {
        $(this).val("NO");
    }
});
$('#overJSON').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $(this).val("YES");
    } else {
        $(this).val("NO");
    }
});


}); // end of page is loaded...
