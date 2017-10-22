$(function () { // when page is loaded...

$('#show').on('click', function()  {
    window.open('show_tables.php',"_blank_");
});
$('#create').on('click', function() {
    var ctarg = 'create_' + $('#ctbl').val() + '.php';
    window.open(ctarg,"_blank");
});
$('#drop').on('click', function() {
    var dtarg = 'drop_' + $('#dtbl').val() + '.php';
    window.open(dtarg,"_blank");
});
$('#ia').on('click', function() {
    window.open('insert_admins.php',"_blank");
});
$('#ldh').on('click', function() {
    window.open('load_HIKES.php',"_blank");
});
$('#ldt').on('click', function() {
    window.open('load_TSV.php',"_blank");
});

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
            if (tbl === 'USERS') {
                qstr = 'u&indx=' + rno;
            } else if (tbl === 'EHIKES') {
                qstr = 'e&indx=' + rno;
            } else if (tbl === 'HIKES') {
                qstr = 'h&indx=' + rno;
            } else if (tbl === 'TSV') {
                qstr = 't&indx=' + rno;
            } else {
                alert ("Unidentified Table");
            }
            var rowtarg = 'delete_tbl_row.php?tbl=' + qstr;
            $('#drow').val('');
            window.open(rowtarg,"_target");
        }
    } else {
        alert("Nothing deleted");
    }
});

});


