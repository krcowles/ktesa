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
var icon = false;  // true when <td> contains image for exposure
function iconType(imgsrc) {
    if (imgsrc.indexOf('fullSun') !== -1) {
        return 'Sun';
    } else if(imgsrc.indexOf('partShade') !== -1) {
        return 'Partial';
    } else {
        return 'Shady';
    }
}
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
    if (level1 === 'Exposure') {
        icon = true;
    }
    $headers.each(function(index) {
        if ($(this).text() === level1) {
            indx = index;
            compareType = $(this).data('sort');
            if (typeof(compareType == 'undefined')) {
                compareType = 'std';  // exposure has no data-sort type
            }
            return true;
        }
    });
    rows.sort(function(a, b) {
        if (icon) {
            var imga = $(a).find('td').eq(indx).children();
            var imgb = $(b).find('td').eq(indx).children();
            var srca = $(imga[0]).attr('src');
            var srcb = $(imgb[0]).attr('src');
            a = iconType(srca);
            b = iconType(srcb);
        } else {
            a = $(a).find('td').eq(indx).text();
            b = $(b).find('td').eq(indx).text();
        }
        return compare[compareType](a, b);
    });
    // level 2 sort:
    if (level2) {
        var subrows = [];
        var lastkey = indx;  // this is the <td> on which level1 sorted
        var subsort = $(rows[0]).find('td').eq(lastkey).text();
        $headers.each(function(index) {
            if ($(this).text() === level2) {
                indx = index;
                compareType = $(this).data('sort');
            }
        });
        var k = 0;
        while (k<rows.length) {
            if($(rows[k]).find('td').eq(lastkey).text() === subsort) {
                subrows.push(rows[k]);
                k++;
            } else {
                subsort = $(rows[k]).find('td').eq(lastkey).text();
                if (k === rows.length -1) {  // if the final entry
                    subrows.push(rows[k]);
                }
            }
        }
        subrows.sort(function(a, b) {
            if (icon) {
                var imga = $(a).find('td').eq(indx).children();
                var imgb = $(b).find('td').eq(indx).children();
                var srca = $(imga[0]).attr('src');
                var srcb = $(imgb[0]).attr('src');
                a = iconType(srca);
                b = iconType(srcb);
            } else {
                a = $(a).find('td').eq(indx).text();
                b = $(b).find('td').eq(indx).text();
            }
            return compare[compareType](a, b);
        });
        rows = subrows.slice(0);
    }
    $tbody.append(rows);
});
