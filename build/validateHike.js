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
$('#mall').on('change', function() {
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
        $('#overImg1').val("YES");
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

$('#owpf1').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overPmap').val("YES");
    } else {
        $('#overPmap').val("NO");
    }
});
$('#owpf2').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overPgpx').val("YES");
    } else {
        $('#overPgpx').val("NO");
    }
});
$('#owaf1').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overAmap').val("YES");
    } else {
        $('#overAmap').val("NO");
    }
});

$('#owaf2').on('change', function() {
    if ( $(this).prop('checked') === true ) {
        $('#overAgpx').val("YES");
    } else {
        $('#overAgpx').val("NO");
    }
});

if ( $('#tsvStat').text() == 'NO') {
    $('#showpics').css('display','none');
} else {
    $('#showpics').css('display','block');
}
}); // end of page is loaded...
