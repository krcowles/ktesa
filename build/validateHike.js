$( function () { // when page is loaded...

var $hboxes = $('.hpguse');
var $mboxes = $('.mpguse');

$('#all').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $hboxes.each( function() {
                $(this).prop('checked',false);
        });
    } else {
        $hboxes.each( function() {
                $(this).prop('checked',true);
        });
    }
});
$('#mall').on('change', function() {
    if ( $(this).prop('checked') === false ) {
        $mboxes.each( function() {
                $(this).prop('checked',false);
        });
    } else {
        $mboxes.each( function() {
                $(this).prop('checked',true);
        });
    }
});

if ( $('#tsvStat').text() === 'NO') {
    $('#showpics').css('display','none');
} else {
    $('#showpics').css('display','block');
}

// load the database and extract "title" and "description"
// for photos on page to allow mouseover display: 'mouseDat' established in php
var $mo = $( mouseDat );  // jQuery object
var $ptitles = $mo.find('title');
$ptitles.each( function(indx) {
    phTitles[indx] = $(this).text();
});
var $pdescs = $mo.find('desc');
$pdescs.each( function(indx) {
    phDescs[indx] = $(this).text();
});
            
}); // end of page is loaded...
