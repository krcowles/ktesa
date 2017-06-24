$( function () { // when page is loaded...

// id and save hike number selected for deletion
$('a').on('click', function(e) {
    e.preventDefault();
    var $containerCell = $(this).parent();
    var $containerRow = $containerCell.parent();
    var hikeName = $containerRow.children().eq(1).text();
    var page2remove = $containerRow.data('indx');
    var msg = 'You have marked "' + hikeName + '" for deletion' +
        "\n" + 'Only one hike may deleted at a time';
    $('#passhike').val(page2remove);
    window.alert(msg);
});

// global object used to define how table items get compared in a sort:
var noPart1;
var noPart2;
var compare = {
    std: function(a,b) {	// standard sorting - literal
        if ( a < b ) {
            return -1;
        } else {
            return a > b ? 1 : 0;
        }
    },
    lan: function(a,b) {    // "Like A Number": extract numeric portion for sort
        // commas allowed in numbers, so;
        var indx = a.indexOf(',');
        if ( indx < 0 ) {
            a = parseFloat(a);
        } else {
            noPart1 = parseFloat(a);
            msg = a.substring(indx + 1, indx + 4);
            noPart2 = msg.valueOf();
            a = noPart1 + noPart2;
        }
        indx = b.indexOf(',');
        if ( indx < 0 ) {
            b = parseFloat(b);
        } else {
            noPart1 = parseFloat(b);
            msg = b.substring(indx + 1, indx + 4);
            noPart2 = msg.valueOf();
            b = noPart1 + noPart2;
        }
        return a - b;
    } 
};  // end of COMPARE object

$('.sortable').each( function() {
    var $table = $(this);
    var $tbody = $table.find('tbody');
    var $controls = $table.find('th');
    var rows = $tbody.find('tr').toArray();

    $controls.on('click', function() {
        $header = $(this);
        var order = $header.data('sort');
        var column;
        if ($header.is('.ascending') || $header.is('descending')) {
            $header.toggleClass('ascending descending');
            $tbody.append(rows.reverse());
        } else {
            $header.addClass('ascending');
            $header.siblings().removeClass('ascending descending');
            if (compare.hasOwnProperty(order)) {  // compare object needs method for var order
                column =$controls.index(this);
                rows.sort( function(a,b) {
                    a = $(a).find('td').eq(column).text();
                    b = $(b).find('td').eq(column).text();
                    return compare[order](a,b);
                });
                $tbody.append(rows);
            }  // end compare
        }  // end else
    });
});  // end sortable each loop

}); // end of page is loaded...