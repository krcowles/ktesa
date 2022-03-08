"use strict";
/// <reference path='./map.d.ts' />
/**
 * @file This file creates and places the html for the side table, as well as providing
 *       a search bar capability synchronized to the side table. Note that any globals
 *       needed for map.js are either supplied via home.php, or have already been
 *       declared via map.js, which is called first.
 * @author Ken Cowles
 * @version 4.0 Adds track highlighting
 * @version 5.0 Typescripted, with some previous type errors corrected
 * @version 6.0 Added thumbnail images to side panel; see 'appendSegment()' notes
 * @version 6.1 Utilize random number to assign colors for tracks to increase diversity on map
 */
/**
 * Searchbar Functionality (html datalist element)
 */
$('#searchbar').val('');
$('#searchbar').on('input', function () {
    var $input = $(this), val = $input.val(), list = $input.attr('list'), match = $('#' + list + ' option').filter(function () {
        return ($(this).val() === val);
    });
    if (match.length > 0) {
        popupHikeName(val);
    }
    return;
});
/**
 * This function [coupled with infoWin()] 'clicks' the infoWin
 * for the corresponding hike
 */
function popupHikeName(hikename) {
    var found = false;
    if (pgnames.includes(hikename)) { // These are 'Cluster Pages', not hikes
        var indx_1 = pgnames.indexOf(hikename);
        hiliteObj = { obj: CL[indx_1].hikes, type: 'cl' };
        infoWin(CL[indx_1].group, CL[indx_1].loc);
        found = true;
    }
    else {
        for (var i = 0; i < CL.length; i++) {
            for (var j = 0; j < CL[i].hikes.length; j++) {
                if (CL[i].hikes[j].name == hikename) {
                    hiliteObj = { obj: CL[i].hikes[j], type: 'nm' };
                    infoWin(CL[i].group, CL[i].loc);
                    found = true;
                    break;
                }
            }
        }
    }
    if (!found) {
        for (var k = 0; k < NM.length; k++) {
            if (NM[k].name == hikename) {
                hiliteObj = { obj: NM[k], type: 'nm' };
                infoWin(NM[k].name, NM[k].loc);
                found = true;
                break;
            }
        }
    }
    if (!found) {
        alert("This hike cannot be located in the list of hikes");
    }
    return;
}
/**
 * This function will click the argument's infoWindow:
 * Note the use of 'setCenter': if the marker has already been clicked,
 * setCenter() simply restores it to the center of the map.
 */
var infoWin = function (hike, loc) {
    // highlight track for either searchbar or zoom-to icon:
    applyHighlighting = true;
    // clicking marker sets zoom
    for (var k = 0; k < locaters.length; k++) {
        if (locaters[k].hikeid == hike) {
            if (locaters[k].clicked === false) {
                google.maps.event.trigger(locaters[k].pin, 'click');
            }
            else {
                map.setCenter(loc);
            }
            break;
        }
    }
    return;
};
/**
 * This function emphasizes the hike track(s) that have been zoomed to;
 * NOTE: A javascript anomaly: passing in a single object in an array
 * results in the function receiving the object, but not as an array.
 * Hence a 'type' identifier is used here
 */
function highlightTracks() {
    if (!$.isEmptyObject(hiliteObj)) {
        if (hiliteObj.type === 'cl') { // object is an array of objects
            var cluster = hiliteObj.obj;
            cluster.forEach(function (track) {
                var polyno = track.indx;
                for (var k = 0; k < drawnTracks.length; k++) {
                    if (drawnTracks[k].hike == polyno) {
                        var polyline = drawnTracks[k].track;
                        polyline.setOptions({
                            strokeWeight: 4,
                            strokeColor: '#FFFF00',
                            strokeOpacity: 1,
                            zIndex: 10
                        });
                        hilited.push(polyline);
                        break;
                    }
                }
            });
        }
        else { // mrkr === 'nm'; object is a single object
            var nmobj = hiliteObj.obj;
            var polyno = nmobj.indx;
            for (var k = 0; k < drawnTracks.length; k++) {
                if (drawnTracks[k].hike == polyno) {
                    var polyline = drawnTracks[k].track;
                    polyline.setOptions({
                        strokeWeight: 4,
                        strokeColor: '#FFFF00',
                        strokeOpacity: 1,
                        zIndex: 10
                    });
                    hilited.push(polyline);
                    break;
                }
            }
        }
        hiliteObj = {};
    }
    return;
}
/**
 * Restore stroke weight and reduce opacity for tracks no longer being chosen for highlighting
 */
function restoreTracks() {
    for (var n = 0; n < hilited.length; n++) {
        hilited[n].setOptions({
            strokeOpacity: 0.60,
            strokeWeight: 3,
            zIndex: 1
        });
    }
    return;
}
/**
 * The side table includes all hikes on page load; on pan/zoom it will include only those
 * hikes within the map bounds. In the following code, the variables 'allHikes' and
 * 'locations' are declared on home.php (and created by mapJsData.php):
 * allHikes:  an array of every hike in the database;
 * locations: a one-to-one correspondence to allHikes; an array of objects containing
 * the object type of the hike (CL, or NM) and its index in that array.
 */
function compareObj(a, b) {
    var hikea = a.name;
    var hikeb = b.name;
    var comparison;
    if (hikea > hikeb) {
        comparison = 1;
    }
    else {
        comparison = -1;
    }
    return comparison;
}
/**
 * The html 'wrapper' for each item included in the side table
 */
var subsize = 10;
var indexer;
var done = false;
var tblItemHtml;
// one tableItem div for each side table hike
tblItemHtml = '<div class="tableItem"><div class="tip">Add to Favorites</div>';
// the div holding the favorites icon and the zoom-to-map icon
tblItemHtml += '<div class="icons">';
tblItemHtml += '<img class="like" src="../images/favoritesYellow.png" alt="favorites icon" />';
tblItemHtml += '<br /><img class="zoomers" src="../images/mapZoom.png" alt="zoom symbol" />';
tblItemHtml += '<span class="zpop">Zoom to Hike</span>';
tblItemHtml += '</div>';
// the div holding the hike-specific data
tblItemHtml += '<div class="content">';
/**
 * The DOM elements for the side table are created and attached in this function;
 * To reduce apparent 'thumb image' load times, the table is created 'subsize'
 * elements at a time, per interval. The table will populate the topmost items
 * first with no wait.
 */
function appendSegment(subset) {
    var jqSubset = [];
    for (var m = 0; m < subset.length; m++) {
        var obj = subset[m];
        var hno = obj.indx;
        var hike_no = hno.toString();
        var tbl;
        if (favlist.includes(hike_no)) {
            tbl = tblItemHtml.replace('Yellow', 'Red');
        }
        else {
            tbl = tblItemHtml;
        }
        var lnk = '<a href="../pages/hikePageTemplate.php?hikeIndx=' + obj.indx +
            '" class="stlinks">' + obj.name + '</a>';
        tbl += lnk;
        tbl += '<br /><span class="subtxt">Rating: ' + obj.diff + ' / '
            + obj.lgth + ' miles';
        tbl += '</span><br /><span class="subtxtb">Elev Change: ';
        tbl += obj.elev + ' feet</span><p id="sidelat" style="display:none">';
        tbl += obj.loc.lat + '</p><p id="sidelng" style="display:none">';
        tbl += obj.loc.lng + '</p></div>';
        tbl += '<div class="thumbs"><img src="' + thumb +
            obj.prev + '" alt="preview image" class="thmbpic" /></div>';
        tbl += '</div>';
        var $tbl = $(tbl);
        $('#sideTable').append($tbl);
        // Note: $tbl must be appended before adding to array!!
        jqSubset.push($tbl);
    }
    enlargePreview(jqSubset);
    enableFavorites(jqSubset);
    enableZoom(jqSubset);
    return;
}
function formTbl(indxArray) {
    $('#sideTable').empty();
    var primeArray = indxArray.slice(0, subsize);
    appendSegment(primeArray);
    indexer = subsize;
    loadSpreader = setInterval(function () {
        var end = indexer + subsize;
        if (end >= indxArray.length) {
            end = indxArray.length;
            done = true;
        }
        var nextArray = indxArray.slice(indexer, end);
        appendSegment(nextArray);
        indexer += subsize;
        if (done) {
            clearInterval(loadSpreader);
            loadSpreader = undefined;
            done = false;
        }
    }, 500);
    return;
}
/**
 * This function allows the user an enlarged view of the thumb when moused over
 */
function enlargePreview(items) {
    for (var i = 0; i < items.length; i++) {
        // setup mouse behavior on thumb
        var idiv = items[i].find('.thumbs');
        var $image = idiv.children().eq(0);
        $image.on('mouseover', function () {
            var ipos = $(this).offset();
            var left = (ipos.left - 280) + 'px';
            var top = (ipos.top - 60) + 'px';
            var isrc = $(this).attr('src');
            isrc = isrc.replace("thumbs", "previews");
            var expand = '<img class="bigger" src="' + isrc + '" />';
            var $img = $(expand);
            $img.css({
                top: top,
                left: left,
                zIndex: 100
            });
            $('body').append($img);
        });
        $image.on('mouseout', function () {
            $('.bigger').remove();
        });
        // position tooltip
        var $ttdiv = items[i].children().eq(0); // div holding tooltip
        var $icndiv = $ttdiv.next().children().eq(0); // <img holding 'Like' symbol
        positionFavToolTip($ttdiv, $icndiv);
    }
    return;
}
/**
 * This function is required in order to reposition the like popups after
 * resizing
 */
function positionFavToolTip(tipdiv, icon) {
    var likeSym = icon.attr('src');
    if (likeSym.indexOf('Yellow') === -1) {
        tipdiv[0].innerHTML = 'Unmark Favorite';
    }
    icon.on('mouseover', function () {
        var pos = $(this).offset();
        var left = pos.left - 128 + 'px'; // width of tip is 120px
        var top = pos.top + 'px';
        tipdiv[0].style.top = top;
        tipdiv[0].style.left = left;
        tipdiv[0].style.display = 'block';
    });
    icon.on('mouseout', function () {
        tipdiv[0].style.display = 'none';
    });
    return;
}
/**
 * This function will track events on the favorites icons
 */
function enableFavorites(items) {
    var _loop_1 = function (k) {
        var $icndiv = items[k].children().eq(1); // icons div
        var $favicn = $icndiv.children().eq(0); // 'like' <img> element
        // retrieve hike no from content div
        var hikelink = $icndiv.next().children().eq(0).attr('href');
        var digitpos = hikelink.indexOf('=') + 1;
        var hno = hikelink.substring(digitpos); // this is the string version of hike no
        var hikeno = parseInt(hno); // this is the integer version of hike no
        $favicn.off('click').on('click', function () {
            var ajaxdata = { no: hikeno };
            var isrc = $(this).attr('src');
            var newsrc;
            var $tooltip = $(this).parent().prev();
            var $that = $(this);
            if (isrc.indexOf('Yellow') !== -1) { // currently a not favorite
                ajaxdata.action = 'add';
                $.ajax({
                    url: "markFavorites.php",
                    method: "post",
                    data: ajaxdata,
                    dataType: "text",
                    success: function (results) {
                        if (results === "OK") {
                            favlist.push(hno);
                            newsrc = isrc.replace('Yellow', 'Red');
                            $tooltip.text('Unmark');
                            $that.attr('src', newsrc);
                        }
                        else {
                            alert("You must be a registered user\n" +
                                "in order to save Favorites");
                        }
                    },
                    error: function () {
                        var msg = "A server error occurred\nYou will not be able " +
                            "to save Favorites at this time:\nThe admin has been " +
                            "notified";
                        alert(msg);
                        var ajxerr = { err: "Mark favorites php error: save" };
                        $.post('../php/ajaxError.php', ajxerr);
                    }
                });
            }
            else { // currently a favorite
                ajaxdata.action = 'delete';
                $.ajax({
                    url: "markFavorites.php",
                    method: "post",
                    data: ajaxdata,
                    dataType: "text",
                    success: function (results) {
                        if (results === 'OK') {
                            var key = favlist.indexOf(hno);
                            favlist.splice(key, 1);
                            newsrc = isrc.replace('Red', 'Yellow');
                            $tooltip.text('Add to Favorites');
                            $that.attr('src', newsrc);
                        }
                        else {
                            alert("You must be a registered user\n" +
                                "in order to save Favorites");
                        }
                    },
                    error: function () {
                        var msg = "A server error occurred\nYou will not be able " +
                            "to unsave Favorites at this time:\nThe admin has been " +
                            "notified";
                        alert(msg);
                        var ajxerr = { err: "Mark favorites php error: unsave" };
                        $.post('../php/ajaxError.php', ajxerr);
                    }
                });
            }
        });
    };
    for (var k = 0; k < items.length; k++) {
        _loop_1(k);
    }
    return;
}
;
/**
 * This function will zoom to the correct map location for the corresponding
 * hike, and popup its infoWin and highlight it. It also displays a tooltip on mouseover.
 */
function enableZoom(items) {
    for (var j = 0; j < items.length; j++) {
        var $mag = items[j].find('.zoomers');
        $mag.on('click', function () {
            var hikename = $(this).parent().next().children().eq(0).text();
            popupHikeName(hikename);
        });
        $mag.on('mouseover', function () {
            var zpos = $(this).offset();
            var hpos = zpos.left - 108;
            var vpos = zpos.top;
            $(this).next().css('left', hpos);
            $(this).next().css('top', vpos);
            $(this).next().css('display', 'block');
        });
        $mag.on('mouseout', function () {
            $(this).next().css('display', 'none');
        });
    }
    return;
}
/**
 * The following function returns the appropriate hike object based on the
 * incoming object (obj) and the subject hike number (indx) in that object.
 * Note that CL objects can have an array of hikes in their corresponding objects.
 * It is invoked by the IdTableElements function.
 */
function idHike(indx, obj) {
    if (obj.type === 'cl') {
        var clobj = CL[obj.group];
        var clhikes = clobj.hikes;
        for (var m = 0; m < clhikes.length; m++) {
            if (clhikes[m].indx === indx) {
                return clhikes[m];
            }
        }
    }
    else if (obj.type === 'nm') {
        return NM[obj.group];
    }
    return '';
}
/**
 * A function to find elements within current map bounds and display them in
 * the side table. This is invoked by either a pan or a zoom on the map (see
 * map.js for listeners). This function also returns a set of hikenumbers for
 * making tracks when the map zoom >= 13. Clusters are 'segregated' so that the
 * entire set of hikes in the cluster can be drawn, each with a unique color.
 */
var IdTableElements = function (boundsStr, zoom) {
    var singles = []; // individual hike nos
    var trackColors = []; // for clusters, tracks get unique colors
    var hikeInfoWins = []; // info window content for each hikeno in singles
    // ESTABLISH CURRENT VIEWPORT BOUNDS:
    var beginA = boundsStr.indexOf('((') + 2;
    var leftParm = boundsStr.substring(beginA, boundsStr.length);
    var beginB = leftParm.indexOf('(') + 1;
    var rightParm = leftParm.substring(beginB, leftParm.length);
    var south = parseFloat(leftParm);
    var north = parseFloat(rightParm);
    var westIndx = leftParm.indexOf(',') + 1;
    var westStr = leftParm.substring(westIndx, leftParm.length);
    var west = parseFloat(westStr);
    var eastIndx = rightParm.indexOf(',') + 1;
    var eastStr = rightParm.substring(eastIndx, rightParm.length);
    var east = parseFloat(eastStr);
    /* FIND HIKES WITHIN THE CURRENT VIEWPORT BOUNDS */
    var hikearr = [];
    var max_color = colors.length - 1;
    var color_indx = Math.floor(Math.random() * max_color); // min is always 0
    CL.forEach(function (clus) {
        clus.hikes.forEach(function (hike) {
            var lat = hike.loc.lat;
            var lng = hike.loc.lng;
            if (lng <= east && lng >= west && lat <= north && lat >= south) {
                var hikeindx = allHikes.indexOf(hike.indx);
                var hikeobj = locations[hikeindx];
                var data_1 = idHike(allHikes[hikeindx], hikeobj);
                hikearr.push(data_1);
                if (zoom) {
                    var cliw = '<div id="iwCH"><a href="../pages/hikePageTemplate.php?hikeIndx=' +
                        hike.indx + '" target="_blank">' + hike.name + '</a><br />Length: ' +
                        hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
                        '<br />Difficulty: ' + hike.diff + '</div>';
                    singles.push(hike.indx);
                    hikeInfoWins.push(cliw);
                    trackColors.push(colors[color_indx++]);
                    if (color_indx > max_color) { // rotate through colors
                        color_indx = 0;
                    }
                }
            }
        });
    });
    NM.forEach(function (hike) {
        var lat = hike.loc.lat;
        var lng = hike.loc.lng;
        if (lng <= east && lng >= west && lat <= north && lat >= south) {
            var hikeindx = allHikes.indexOf(hike.indx);
            var hikeobj = locations[hikeindx];
            var data_2 = idHike(allHikes[hikeindx], hikeobj);
            hikearr.push(data_2);
            if (zoom) {
                var nmiw = '<div id="iwNH"><a href="../pages/hikePageTemplate.php?hikeIndx=' +
                    hike.indx + '" target="_blank">' + hike.name + '</a><br />Length: ' +
                    hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
                    '<br />Difficulty: ' + hike.diff + '</div>';
                singles.push(hike.indx);
                hikeInfoWins.push(nmiw);
                trackColors.push(colors[color_indx++]);
                if (color_indx > max_color) { // rotate through colors
                    color_indx = 0;
                }
            }
        }
    });
    if (hikearr.length === 0) {
        $('#sideTable').empty();
        var nohikes = '<p style="padding-left:12px;font-size:18px;">' +
            'There are no hikes in the viewing area</p>';
        $('#sideTable').html(nohikes);
    }
    else {
        hikearr.sort(compareObj);
        formTbl(hikearr);
    }
    return [singles, hikeInfoWins, trackColors];
};
var grabber = document.getElementById('adjustWidth');
grabber.addEventListener('mousedown', changeWidth, false);
/**
 * Function to change div widths when mousedown on 'grabber' (#adjustWidth)
 * Thie function adds a mousemove listener to track the mouse location
 */
function changeWidth(ev) {
    ev.preventDefault(); // prevents selecting other elements while mousedown
    document.addEventListener('mousemove', widthSizer, false);
    return;
}
/**
 * The function is called by the mousemove event listener. It is necessary
 * not to use anonymous functions here as those listeners cannot be removed.
 * When the mouse moves, a listener is add to detect when the mouse is released.
 */
function widthSizer(evt) {
    document.addEventListener('mouseup', stopMoving, false);
    var viewport = window.innerWidth;
    var sideWidth = viewport - evt.clientX - 3;
    $('#map').width(evt.clientX);
    $('#sideTable').width(sideWidth);
    $('.like').each(function () {
        var $icon = $(this);
        var $tooldiv = $icon.parent().prev();
        positionFavToolTip($tooldiv, $icon);
    });
    locateGeoSym();
    return;
}
/**
 * This function removes both the mousemove listener and the mouseup listener
 * so that widthSizer ceases to function, and the mousdedown can be re-invoked
 */
function stopMoving() {
    document.removeEventListener('mousemove', widthSizer, false);
    document.removeEventListener('mouseup', stopMoving, false);
    return;
}
