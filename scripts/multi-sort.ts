/**
 * @fileoverview When clicked, the 'Sort' button invokes the user-selected sorting 
 * criteria which will reformat the table (class 'fsort'). The table is already present
 * on the page, as constructed by filter.js; Note: the 'COMPARE' object used in sorting is
 * already defined in 'tblOnlySort.js'
 * @author Ken Cowles
 * @version 2.0 Typescripted, with some type errors corrected
 */
var indx: number;
var compareType: string;   // may be 'std' or 'lan' (like a number)
var icon = false;  // true when <td> contains image for "exposure"
var ais: string | number;
var bis: string | number;
var level1 = '';
var level2 = '';
var key1 = '';
var key2 = '';
var level2rows: HTMLTableRowElement[] = []; // accumulated array of level2 sorted $rows
/**
 * This function id's the column header number of the search key. It sets the global
 * 'compareType' to "std" if the header is for "Exposure", which has no data-sort type
 */
function getHeaderColumn(jqHeaders: JQuery<HTMLTableHeaderCellElement>, key: string): number 
{
    let colno = -1;
    for (let i=0; i<jqHeaders.length; i++) {
        if ($(jqHeaders[i]).text() === key) {
            colno = i;
            compareType = $(jqHeaders[i]).data('sort');
            if (typeof(compareType == 'undefined')) {
                compareType = 'std';  // exposure has no data-sort type
            }
            break;
        }
    }
    return colno;
}
/**
 * Returns a simple string based on icon source in the "exposure" column.
 * This enables a simple sort to take place.
 */
function iconType(imgsrc: string): string {
    if (imgsrc.indexOf('fullSun') !== -1) {
        return 'Sun';
    } else if(imgsrc.indexOf('partShade') !== -1) {
        return 'Partial';
    } else {
        return 'Shady';
    }
}
/**
 * For each unique value in the key1 sort, a set of 'subrows' is
 * formed. That subrow set is then sorted by key2 and appended to
 * the level2rows. When the algorithm is finished the result is
 * appended to the table.
 */
 function l2sort(l2array: HTMLTableRowElement[], tdno: number) {
    l2array.sort(function(a, b) {
        let retval = 0;
        if (key2 === 'Exposure') {
            var imga = $(a).find('td').eq(tdno).children();
            var srca = <string>$(imga[0]).attr('src');
            ais = iconType(srca);
            var imgb = $(b).find('td').eq(tdno).children();
            var srcb = <string>$(imgb[0]).attr('src');
            bis = iconType(srcb);
            retval = compare["std"](ais, bis); 
        } else {
            ais = $(a).find('td').eq(tdno).text();
            bis = $(b).find('td').eq(tdno).text();
            if (level2 === 'Length' || level2 === 'Elev Chg') {
                retval = compare["lan"](ais, bis);
            } else {
                retval = compare["std"](ais, bis);
            }
        }
        return retval;
    });
    // push onto level2rows
    for (var j=0; j<l2array.length; j++) {
        level2rows.push(l2array[j]);
    }
}
$('#sort').on('click', function() {
    // validate sort and depth of sort
    key1 = <string>$('#sort1').val();
    key2 = <string>$('#sort2').val();
    if (key1 === 'No Sort' && key2 ==='No Sort') {
        alert("No sorting criteria have been entered");
        return;
    }
    level1 = key1;
    level2 = key2;
    if (key1 === key2) {
        alert("Both sort criteria are the same - sort will be\n" +
            "performed on the single criterion");
        level2 = '';
    } else if (key1 === 'No Sort' || key2 === 'No Sort') {
        if (key1 === 'No Sort') {
            level1 = key2;
        } 
        level2 = '';
    }
    var $tbody = $('#ftable').find('tbody');
    var $rows = $tbody.find('tr').toArray();
    if ($rows.length === 0) {
        alert("There is no table to sort");
        return;
    }
    var $headers = $('.fsort').find('th');  // contains sort type
    // level 1 sort:
    if (level1 === 'Exposure') {
        icon = true;
    }
    indx = getHeaderColumn($headers, level1);
    if (indx === -1) {
        alert("Sort key not located in table");
        return;
    }
    // level 1 sort:
    $rows.sort(function(a, b) {
        let retval = 0;
        if (icon) {
            var imga = $(a).find('td').eq(indx).children();
            var srca = <string>$(imga[0]).attr('src');
            ais = iconType(srca);
            var imgb = $(b).find('td').eq(indx).children();
            var srcb = <string>$(imgb[0]).attr('src');
            bis = iconType(srcb);
            retval = compare["std"](ais, bis);
        } else {
            ais = $(a).find('td').eq(indx).text();
            bis = $(b).find('td').eq(indx).text();
            if (level1 === "Length" || level1 === "Elev Chg") {
                retval = compare["lan"](ais, bis);
            } else {
                retval = compare["std"](ais, bis);
            }
        }
        return retval;
    });

    // level 2 sort:
    if (level2 !== '') {
        /**
         * For each unique value in the key1 sort, a set of 'subrows' is
         * formed. That subrow set is then sorted by key2 and appended to
         * the level2rows. When the algorithm is finished the result is
         * appended to the table.
         */
        level2rows = [];
        var subrows = [];       // temp array of $rows with common key1 values
        var lastkey = indx;  // this is the <td> on which level1 sorted
        // get a new indx for level 2:
        indx = getHeaderColumn($headers, level2);
        var key1val = '';
        var lastKey1Val = '';
        var exposure: string;
        var $sortCell: JQuery<HTMLTableCellElement>;
        // get the first level1 key value to compare against
        if (icon) {
            $sortCell = $($rows[0]).find('td').eq(lastkey);
            exposure = <string>$($sortCell[0]).attr('src');
            lastKey1Val = iconType(exposure);
        } else {
            $sortCell = $($rows[0]).find('td').eq(lastkey);
            lastKey1Val = $sortCell.text();
        }
        for (var k=0; k<$rows.length; k++) { // form a subset of $rows which have level1 key values in common
            // need to know if this is the last row
            var lastrow = (k === $rows.length -1) ? true : false;
            // what is this row's key1 value?
            if (icon) {
                $sortCell = $($rows[k]).find('td').eq(lastkey).children();
                exposure = <string>$($sortCell[0]).attr('src');
                key1val = iconType(exposure);
            } else {
                $sortCell = $($rows[k]).find('td').eq(lastkey);
                key1val = $sortCell.text();
            }
            // is this a new set of key1 values?
            if (lastKey1Val !== key1val) {
                // sort the current subset and append to level2rows
                l2sort(subrows, indx);
                if (lastrow) {
                    level2rows.push($rows[k]);
                } else {
                    lastKey1Val = key1val;
                    subrows = [];
                    subrows.push($rows[k]);
                }
            } else {
                subrows.push($rows[k]);
                if (lastrow) {
                    l2sort(subrows, indx);
                }
            }
        }
        $rows = level2rows.slice(0);
    }
    $tbody.append($rows);
    icon = false;  // for additional sorts...
});
