$(function () { // when page is loaded...

$('#delrow').on('click', function() {
    var trow = prompt("Are you sure you want to delete this row?","Row " + $('#drow').val());
    if (trow !== null) {
        var plgth = trow.length;
        var rno = trow.substring(4,plgth);
        window.open("delete_HIKE_row.php?indx=" + rno,"_target");
    } else {
        alert("Nothing deleted");
    }
});



});


