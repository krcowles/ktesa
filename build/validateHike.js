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

$('#unval').on('click', function(ev) {
    ev.preventDefault();
    window.open('unvalidate.php',"_blank");
});

}); // end of page is loaded...
