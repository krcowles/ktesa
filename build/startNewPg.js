$( function () { // when page is loaded...

var titleList;
$.ajax({
    type: "POST",
    url: "getTitles.php",
    dataType: 'JSON',
    success: function(titles) {
        titleList = titles;
    },
    error: function(jqXHR, textStatus, errorThrown) {
        var newDoc = document.open();
		newDoc.write(jqXHR.responseText);
		newDoc.close();
    }
});

// Prevent submitting form when user hits 'Enter' key in input field
$('form').find('#newname').keypress(function(ev) {
    var type = ev.which;
    if (type == 13) {
        $('#atvc').focus();  //Use whatever selector necessary to focus the 'next' input
        return false;
    }
})

// establish new page/refresh radio button states
$('#atvc').prop('checked', false);
$('#cluster').prop('checked', false);
$('#normal').prop('checked', true);
$('#newname').on('change', function(ev) {
    for (var i=0; i<titleList.length; i++) {
        if ($(this).val() == titleList[i]) {
            alert("This name already exists; Please try another");
            $(this).val('');
        }
    }
});

$('#atvc').on('change', function() {
    if ($(this).prop('checked')) {
        $('#vcs').css('display','block');
        $('#cls').css('display','none');
    }
});
$('#cluster').on('change', function() {
    if ($(this).prop('checked')) {
        $('#cls').css('display','block');
        $('#vcs').css('display','none');
    }
});
$('#normal').on('change', function() {
    if ($(this).prop('checked')) {
        $('#vcs').css('display','none');
        $('#cls').css('display','none');
    }
});
$('#newname').focus();

});