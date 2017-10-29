$(function () { // when page is loaded...

$('#show').on('click', function()  {
    window.open('show_tables.php',"_blank_");
});
$('#create').on('click', function() {
    var ctype = $('#ctbl').val();
    if (ctype.substring(0,1) !== 'E') {
        var ctarg = 'create_' + ctype + '.php';
    } else {
        var ctarg = 'create_E_table.php?tbl=' + $('#ctbl').val();
    }
    window.open(ctarg,"_blank");
});
$('#drop').on('click', function() {
    var dtarg = 'drop_table.php?tbl=' + $('#dtbl').val();
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
$('#ldr').on('click', function() {
    window.open('load_REFS.php',"_blank");
});
$('#ldg').on('click', function() {
    window.open('load_GPSDAT.php',"_blank");
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
$('#ehdel').on('click', function() {
    var hrow = prompt("Are you sure you want to delete this hike?","Hike " + 
        $('#hdel').val());
    if (hrow !== null) {
        var hlgth = hrow.length;
        var hno = hrow.substring(5,hlgth);
        if (hno == 0) {
            alert("There is no hike 0; Please specify an existing hike");
            $('#hdel').val('');
        } else {
            window.open('delete_EHIKE.php?drow=' + hno,"_blank");
        }
    }
});

});


