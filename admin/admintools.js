var mode_script = "site_mode.php";
var dev_mode = mode_script + "?mode=development";
var run_mode = mode_script + "?mode=production";
$('#dev').on('click', function() {
    $.ajax({
        type: "GET",
        url: dev_mode,
        dataType: 'text',
        success: function(result) {
            if (result.substr(0,3) == "Cou") {
                alert(result);
            } else {
                $('#currmode').text(result);
            }
        },
        error: function(jq, errmsg, stat) {
            alert("unable to read site mode file: " + errmsg + "; " + stat);
        }
    });
});
$('#run').on('click', function() {
    $.ajax({
        type: "GET",
        url: run_mode,
        dataType: 'text',
        success: function(result) {
            if (result.substr(0, 3) == "Cou") {
                alert(result);
            } else {
                $('#currmode').text(result);
            }
        },
        error: function(jq, errmsg, stat) {
            alert("unable to read site mode file: " + errmsg + "; " + stat);
        }
    });
});
$('#chgs').on('click', function() {
    window.open('export_all_tables.php?dwnld=C');
});
$('#site').on('click', function() {
    window.open('export_all_tables.php?dwnld=S');
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
})
$('#pinfo').on('click', function() {
    window.open('phpInfo.php', "_blank");
});
$('#pub').on('click', function() {
    window.open("reldel.php?act=rel", "_blank");
});
$('#lst').on('click', function() {
    window.open("list_new_files.php", "_blank")
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
    if (ctype.substring(0,1) !== 'E') {
        var ctarg = 'create_' + ctype + '.php';
    } else { // this is an E-Table
        if (ctype === 'EHIKES') {
            var ctarg = 'create_EHIKES_parent.php';
        } else {
            var ctarg = 'create_E_table.php?tbl=' + ctype; // has foreign key defs
        }
    }
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
