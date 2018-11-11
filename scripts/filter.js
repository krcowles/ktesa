// jQuery UI widget:
var spinner = $('#spinner').spinner({
    min: 1,
    max: 50,
    page: 5,
    value: 5
});
spinner.spinner("value", 5);

/* Table sorting for the table-only page is different than the map+table page
   as there is one table only, the refTbl. */
function filterSetup() {
    var winwidth = $(window).innerWidth();
    if (pg === 'tbl') {
        var tblwidth = $('.sortable').width();
    } else {
        var tblwidth = $('.msortable').width();
    }
    var margs = Math.floor((winwidth - tblwidth)/2) + "px";
    $('#tblfilter').css('margin-left', margs);
    $('#tblfilter').css('margin-right', margs);
    $('#filtnote').css('margin-left', margs);
    $('#filtnote').css('margin-right', margs);
    // filtering functionality:
    $('#showfilter').on('click', function() {
        $('#dispopts').toggle();
    });
    $('#loc').on('click', function() {
        if ($('#selloc').hasClass('hidden')) {
            $('#selloc').removeClass('hidden');
            $('#selloc').addClass('inline');
            $('#loclbl').removeClass('normal');
            $('#loclbl').addClass('hilite');
        } else {
            $('#selloc').removeClass('inline');
            $('#selloc').addClass('hidden');
            $('#loclbl').removeClass('hilite');
            $('#loclbl').addClass('normal');
            $(this).prop('checked', false);
        }
        $('#hike').prop('checked', false);
        if ($('#selhike').hasClass('inline')) {
            $('#selhike').removeClass('inline');
            $('#selhike').addClass('hidden');
            $('#hikelbl').removeClass('hilite');
            $('#hikelbl').addClass('normal');
        }
    });
    $('#hike').on('click', function() {
        if ($('#selhike').hasClass('hidden')) {
            $('#selhike').removeClass('hidden');
            $('#selhike').addClass('inline');
            $('#hikelbl').removeClass('normal');
            $('#hikelbl').addClass('hilite');
        } else {
            $('#selhike').removeClass('inline');
            $('#selhike').addClass('hidden');
            $('#hikelbl').removeClass('hilite');
            $('#hikelbl').addClass('normal');
            $(this).prop('checked', false);
        }
        if ($('#selloc').hasClass('inline')) {
            $('#selloc').removeClass('inline');
            $('#selloc').addClass('hidden');
            $('#loclbl').removeClass('hilite');
            $('#loclbl').addClasS('normal');
        }
        $('#loc').prop('checked', false);
    });
    $('a').on('click', function(ev) {
        if ($('#hike').prop('checked')) {
            ev.preventDefault();
            var thishike = $(this).text();
            $('#link').val(thishike);
        }
    });
    $('#apply').on('click', function() {
        var epsilon = $('#within').val();
        // validate
        if (!$.isNumeric(epsilon)) {
            alert("Non-numeric entry made in 'Hikes within' text box");
            return;
        }
        var center = "Fred";
        filterList(epsilon, center);
    });
    function filterList(range, pivot) {
        alert("Hikes within " + range + " miles of " + pivot);
    }
}