$( function() {  // wait until document is loaded...

var current_state = $('#currstate').text();
$('#switchstate').on('click', function() {
    window.open('changeSiteMode.php?mode=' + current_state);
    window.close();
});
$('#chgs').on('click', function() {
    window.open('export_all_tables.php?dwnld=C');
});
$('#site').on('click', function() {
    window.open('export_all_tables.php?dwnld=S');
});
$('#npix').on('click', function() {
    window.open('list_new_files.php?request=pictures', "_self");
});
var picfile = '';
var pselLoc = $('#psel').offset();
var dselLoc = $('#dsel').offset();
var dselCoord = {top: dselLoc.top, left: pselLoc.left};
$('#dsel').offset(dselCoord);
$('#cmppic').on('change', function(ev) {
    picfile = ev.target.files[0].name;
});
$('#rel2pic').on('click', function() {
    picloc = '';
    var dateSelected = $('#datepicker').val();
    //var dateSelected = $('#datepicker').datepicker("getDate");
    //$.datepicker.formatDate('yy-mm-dd', dateSelected);
    //dateSelected = dateSelected.toString();
    if (picfile === '' && dateSelected === '') {
        alert("No image or date has been selected");
    } else {
        if (picfile !== '') {
            var extPos = picfile.lastIndexOf(".");
            var sizedir = picfile.substring(extPos - 1, extPos);
            if (sizedir === 'n') {
                var picloc = "pictures/nsize/" + picfile;
            } else {
                var picloc = "pictures/zsize/" + picfile;
            }
        } 
        window.open("list_new_files.php?request=pictures&dtFile=" + picloc +
            "&dtTime=" + dateSelected, "_self");
    }
});
$('#reload').on('click', function() {
    if (confirm("Do you really want to drop all tables and reload them?")) {
        window.open('drop_all_tables.php', "_blank");
    }
});
$('#drall').on('click', function() {
    if (confirm("Do you really want to drop all tables?")) {
        window.open('drop_all_tables.php?no=all', "_blank");
    }
});
$('#ldall').on('click', function() {
    window.open('load_all_tables.php', "_blank");
});
$('#exall').on('click', function() {
    window.open('export_all_tables.php?dwnld=N', "_blank");
});
$('#swdb').on('click', function() {
    window.open('switchDb.php');
    window.close();
});
$('#emode').on('click', function() {
    var butnTxt = $('#emode').text();
    $.ajax({
        url: 'siteEdit.php',
        data: {button: butnTxt},
        dataType: "text",
        success: function(resp) {
            $('#emode').text(resp);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Edit mode change script failed: " +
                textStatus + ": " + errorThrown);
        }
    });
});
$('#commit').on('click', function() {
    $.ajax({
        url: 'commit_number.txt',
        dataType: 'text',
        success: function(resp) {
            alert("The current commit number\nassociated" +
                " with this site is:\n\n\t" + resp);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("The following error resulted in admintools.js:\n" 
                + "Error: " + textStatus + ": " + errorThrown);
        }
    });
});
$('#cleanPix').on('click', function() {
    window.open('cleanPix.php', "_blank");
});
$('#pinfo').on('click', function() {
    window.open('phpInfo.php', "_blank");
});
$('#pub').on('click', function() {
    window.open("reldel.php?act=rel", "_blank");
});
$('#lst').on('click', function() {
    window.open("list_new_files.php?request=files", "_blank")
});
$('#ehdel').on('click', function() {
    window.open("reldel.php?act=del","_blank");
});
$('#show').on('click', function()  {
    window.open('show_tables.php', "_blank_");
});
$('#drop').on('click', function() {
    var dtarg = 'drop_table.php?tbl=' + $('#dtbl').val();
    window.open(dtarg, "_blank");
});
$('#create').on('click', function() {
    var ctype = $('#ctbl').val(); // the table name to create
    ctarg = "create_table.php?tbl=" + ctype;
    window.open(ctarg, "_blank");
});
$('#sgls').on('click', function() {
    // not yet implemented
});
$('#dret').on('click', function() {
    // currently obsolete
    //window.open('drop_all_tables.php?no=ets',"_blank");
});
var disp = $('#dstat').text();
if (disp === 'Open') {
    $('#modeopt').css('display','block');
} else {
    $('#modeopt').css('display','none');
}
var j = 0;
$('input[type=checkbox]').each( function() {
    if (cbs[j] === 'Y') {
        $(this).attr('checked','checked');
    }
    j++;
});
$('#mode').on('click', function() {
    $('#modeopt').slideToggle();
});
if (typeof(nopix) !== 'undefined') {
    alert(nopix);
}
$('#ldet').on('click', function() {
    alert("Not yet implemented");
});
$('#addbk').on('click', function() {
    window.open("addBook.html", "_blank");
})
$('#rowdel').on('click', function() {
    var trow = prompt("Are you sure you want to delete this row?","Row " + $('#drow').val());
    if (trow !== null) {
        var qstr;
        var plgth = trow.length;
        var rno = trow.substring(4,plgth);
        if (rno == 0) {
            alert("There is no row 0; Please specify an existing row");
            $('#drow').val('');
        } else {
            var tbl = $('#rdel').val();
            switch(tbl) {
                case 'USERS':
                    qstr = 'u&indx=' + rno;
                    break;
                case 'EHIKES':
                    qstr = 'eh&indx=' + rno;
                    break;
                case 'HIKES':
                    alert("Are you sure you want to delete from HIKES?");
                    qstr = 'h&indx=' + rno;
                    break;
                case 'ETSV':
                    qstr = 'et&indx=' + rno;
                    break;
                case 'TSV':
                    alert ("Are you sure you want to delete photos for a live hike?");
                    qstr = 't&indx=' + rno;
                    break;
                case 'EREFS':
                    qstr = 'er&indx=' + rno;
                    break;
                case 'REFS':
                    alert("Are you sure you want to delete refs for a live hike?");
                    qstr = 'r&indx=' + rno;
                    break;
                case 'EGPSDAT':
                    qstr = 'eg&indx=' + rno;
                    break;
                case 'GPSDAT':
                    alert("Are you sure you want to delete gps data for a live hike?");
                    qstr = 'g&indx=' + rno;
                    break;
            }
            var rowtarg = 'delete_tbl_row.php?tbl=' + qstr;
            $('#drow').val('');
            window.open(rowtarg,"_target");
        }
    } else {
        alert("Nothing deleted");
    }
});

});  // end of doc loaded
