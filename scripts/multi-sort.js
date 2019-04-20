/*
 * When clicked, the 'Sort' button invokes the user-selected sorting 
 * criteria which will reformat the table (class 'fsort'). The table
 * is already present on the page, as constructed by filter.js;
 * Note: the 'COMPARE' object used in sorting is already defined in 
 * both 'tblOnlySort.js' ('Table Only' page) and in 'phpDynamicTbls.js'
 * ('Map + Table' page), and does not need to be re-defined here.
 */
var indx;          // index of column on which to sort
var compareType;   // may be 'std' or 'lan' (like a number)
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
    if (key1 === key2) {
        alert("Both sort criteria are the same - sort will be\n" +
            "performed on the single criterion");
        level2 = false;
    } else if (key1 === 'No Sort' || key2 === 'No Sort') {
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
    // level 1 sort:
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
            if (level1 === "Length" || level1 === "Elev Chg") {
                a = parseFloat(a);
                b = parseFloat(b);
            }
        }
        return compare[compareType](a, b);
    });
    // for debug tracking:
    for (var i=0; i<rows.length; i++) {
        $(rows[i]).attr('id', i);
    }
    // level 2 sort:
    if (level2) {
        /**
         * For each unique value in the key1 sort, a set of 'subrows' is
         * formed. That subrow set is then sorted by key2 and appended to
         * the level2rows. When the algorithm is finished the result is
         * appended to the table.
         */
        function l2sort(l2array, tdno) {
            l2array.sort(function(a, b) {
                if (key2 === 'Exposure') {
                    var imga = $(a).find('td').eq(tdno).children();
                    var imgb = $(b).find('td').eq(tdno).children();
                    var srca = $(imga[0]).attr('src');
                    var srcb = $(imgb[0]).attr('src');
                    a = iconType(srca);
                    b = iconType(srcb);
                } else {
                    a = $(a).find('td').eq(tdno).text();
                    b = $(b).find('td').eq(tdno).text();
                    if (level2 === 'Length' || level2 === 'Elev Chg') {
                        a = parseFloat(a);
                        b = parseFloat(b);
                    }
                }
                return compare[compareType](a, b);
            });
            // push onto level2rows
            for (var j=0; j<l2array.length; j++) {
                level2rows.push(l2array[j]);
            }
        }

        var subrows = [];       // temp array of rows with common key1 values
        var level2rows = [];    // accumulated array of level2 sorted rows
        var lastkey = indx;  // this is the <td> on which level1 sorted
        // get a new indx for level 2:
        $headers.each(function(index) {
            if ($(this).text() === level2) {
                indx = index;
                compareType = $(this).data('sort');
                if (typeof(compareType == 'undefined')) {
                    compareType = 'std';  // exposure has no data-sort type
                }
                return true;
            }
        });
        var lastKey1Val;
        var exposure;
        // get the first level1 key value to compare against
        var $sortCell = $(rows[0]).find('td').eq(lastkey).children();
        if (icon) {
            $sortCell = $(rows[0]).find('td').eq(lastkey).children();
            exposure = $sortCell[0].src;
            lastKey1Val = iconType(exposure);
        } else {
            $sortCell = $(rows[0]).find('td').eq(lastkey);
            lastKey1Val = $sortCell.text();
        }
        for (var k=0; k<rows.length; k++) { // form a subset of rows which have level1 key values in common
            // need to know if this is the last row
            lastrow = (k === rows.length -1) ? true : false;
            // what is this row's key1 value?
            if (icon) {
                $sortCell = $(rows[k]).find('td').eq(lastkey).children();
                exposure = $sortCell[k].src;
                key1val = iconType(exposure);
            } else {
                $sortCell = $(rows[k]).find('td').eq(lastkey);
                key1val = $sortCell.text();
            }
            // is this a new set of key1 values?
            if (lastKey1Val !== key1val) {
                // sort the current subset and append to level2rows
                l2sort(subrows, indx);
                if (lastrow) {
                    level2rows.push(rows[k]);
                } else {
                    lastKey1Val = key1val;
                    subrows = [];
                    subrows.push(rows[k]);
                }
            } else {
                subrows.push(rows[k]);
                if (lastrow) {
                    l2sort(subrows, indx);
                }
            }
        }
        rows = level2rows.slice(0);
    }
    $tbody.append(rows);
});
