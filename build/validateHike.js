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

$('#owtsv').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overTsv').val("YES");
    } else {
        $('#overTsv').val("NO");
    }
});

$('#owmap').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overMap').val("YES");
    } else {
        $('#overMap').val("NO");
    }
});
$('#owgpx').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overGpx').val("YES");
    } else {
        $('#overGpx').val("NO");
    }
});
$('#owjson').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overJSON').val("YES");
    } else {
        $('#overJSON').val("NO");
    }
});

$('#owtrk').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overJSON').val("YES");
    } else {
        $('#overJSON').val("NO");
    }
});

$('#owim1').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overimg1').val("YES");
    } else {
        $('#overImg1').val("NO");
    }
});

$('#owim2').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overImg2').val("YES");
    } else {
        $('#overImg2').val("NO");
    }
});
}); // end of page is loaded...
