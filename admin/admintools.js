$(function () { // when page is loaded...

$('#cru').on('click', function() {
    window.open('create_USERS.php',"_blank");
});
$('#du').on('click', function() {
    window.open('drop_USERS.php',"_blank");
});
$('#cre').on('click', function() {
    window.open('create_EHIKES.php','_blank');
});
$('#de').on('click', function() {
    window.open('drop_EHIKES.php',"_blank");
});
$('#crh').on('click', function() {
    window.open('create_HIKES.php',"_blank");
});
$('#dh').on('click', function() {
    window.open('drop_HIKES.php',"_blank");
});
$('#ia').on('click', function() {
    window.open('insert_admins.php',"_blank");
});
$('#ldh').on('click', function() {
    window.open('load_HIKES.php',"_blank");
})

var del_target = 'delete_tbl_row.php?tbl=';
$('#drh').on('click', function() {
    var trow = prompt("Are you sure you want to delete this row?","Row " + $('#drow').val());
    if (trow !== null) {
        var plgth = trow.length;
        var rno = trow.substring(4,plgth);
        var drow = del_target + 'h&indx=' + rno;
        window.open(drow,"_target");
    } else {
        alert("Nothing deleted");
    }
});
$('#dre').on('click', function() {
    var trow = prompt("Are you sure you want to delete this row?","Row " + $('#derow').val());
    if (trow !== null) {
        var plgth = trow.length;
        var rno = trow.substring(4,plgth);
        var drow = del_target + 'e&indx=' + rno;
        window.open(drow,"_target");
    } else {
        alert("Nothing deleted");
    }
});


});


