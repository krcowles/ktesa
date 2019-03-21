/*
 * When clicked, the 'Sort' button invokes the user-selected sorting 
 * criteria which will reformat the table (class 'fsort'). The table
 * is already present on the page, as constructed by filter.js;
 * Note: the 'COMPARE' object used in sorting is already constructed in 
 * both 'tblOnlySort.js' ('Table Only' page) and in 'phpDynamicTbls.js'
 * ('Map + Table' page), and does not need to be re-defined here.
 */
var indx;
var compareType;
$('#sort').on('click', function() {
    // validate sort and depth of sort
    var key1 = $('#sort1').val();
    var key2 = $('#sort2').val();
    if (key1 === 'No Sort' && key2 ==='No Sort') {
        alert("No sorting criteria have been entered");
        return;
    }
    var level1 = key1;
    var level2 = key2;
    if (key1 === 'No Sort' || key2 === 'No Sort') {
        if (key1 === 'No Sort') {
            level1 = key2;
        } 
        level2 = false;
    }
    var $tbody = $('.fsort').find('tbody');
    var rows = $tbody.find('tr').toArray();
    if (rows.length === 0) {
        alert("There is no table to sort");
        return;
    }
    var $headers = $('.fsort').find('th');  // contains sort type
    // level 1 sort:
    $headers.each(function(index) {
        if ($(this).text() === level1) {
            indx = index;
            compareType = $(this).data('sort');
            return true;
        }
    });
    rows.sort(function(a, b) {
        a = $(a).find('td').eq(indx).text();
        b = $(b).find('td').eq(indx).text();
        return compare[compareType](a, b);
    });
    $tbody.append(rows);
    // level 2 sort:
    if (level2) {

    }
});
