"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
/// <reference path='./map.d.ts' />
/**
 * @file This file creates and places the html for the side table, as well as providing
 * a search bar capability synchronized to the side table. Note that any globals
 * needed for map.js are either supplied via home.php, or have already been
 * declared via map.js, which is called first.
 * @author Ken Cowles
 
 * @version 8.0 Removed old method of handling Latin1 characters in strings;
 *      simplified search process by using autocomplete labels w/o diacritical marks.
 *      Added 'clear' method for searchbar.
 * @version 9.0 Major mods to improve side table formation when multiple map events
 *      occur
 *\

/**
 * The 'AllTrails' button listing some advantages using nmhikes.com
 */
var alltrails = new bootstrap.Modal(document.getElementById('alltrails'), {
    keyboard: false
});
$('#advantages').on('click', function () {
    alltrails.show();
});
// Globals for IdTableElements
var singles; // all hike numbers to be included in side table
var hikeInfoWins; // info window content for each hikeno in singles
// Clear searchbar contents when user clicks on the "X"
$('#clear').on('click', function () {
    $('#search').val("");
    var searchbox = document.getElementById('search');
    searchbox.focus();
});
// Establish searchbar as jQueryUI widget
$(".search").autocomplete({
    source: hikeSources,
    minLength: 2
});
// When user selects item from dropdown:
$("#search").on("autocompleteselect", function (event, ui) {
    // the searchbar dropdown uses 'label', but place 'value' in box & use that
    event.preventDefault();
    var entry = ui.item.value;
    $(this).val(entry);
    popupHikeName(entry);
});
/**
 * This global of 'sortableHikes' is updated everytime a side table is formed
 * and can then be used by the sort routines
 */
var sortableHikes = [];
var ascending = true;
var sort_diff = false;
var sort_dist = false;
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
                var custom_mrkr = locaters[k].pin;
                google.maps.event.trigger(custom_mrkr, 'click');
            }
            else {
                window.newBounds = true;
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
// constants and variables used when creating a subset of side table items periodically
var subsize = 10;
var waitTime = 80; // msec
var done = false;
/**
 * The html 'wrapper' for each item included in the side table
 */
var tblItemHtml;
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
 * To reduce the impact of the thumb image load times, the table is created 'subsize'
 * elements at a time, per interval. The table will populate the topmost items
 * first with no wait. Due to the possibility of multiple conflicting map events
 * (pan, center_change, zoom), the routine is invoked from the map.ts/js handlers.
 */
var sleep = function (ms) { return new Promise(function (resolve) { return setTimeout(resolve, ms); }); };
// NOTE: async function returns a Promise to the caller (map.ts/js)
function formTbl(indxArray) {
    return __awaiter(this, void 0, void 0, function () {
        var nohikes, size, stItems, sliceStart, end, last, indx, done, i;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    $('#sideTable').empty();
                    sortableHikes = indxArray;
                    if (indxArray.length === 0) {
                        nohikes = '<p style="padding-left:12px;font-size:18px;">' +
                            'There are no hikes in the viewing area</p>';
                        $('#sideTable').html(nohikes);
                        return [2 /*return*/];
                    }
                    size = indxArray.length;
                    if (!(size <= subsize)) return [3 /*break*/, 1];
                    appendSegment(indxArray);
                    if (kill_table) {
                        $('#sideTable').empty();
                    }
                    return [3 /*break*/, 6];
                case 1:
                    stItems = [];
                    stItems[0] = indxArray.slice(0, subsize);
                    sliceStart = subsize;
                    end = sliceStart + subsize;
                    last = false;
                    if (end >= size) {
                        end = size;
                        last = true;
                    }
                    indx = 1;
                    done = false;
                    while (!done) {
                        stItems[indx++] = indxArray.slice(sliceStart, end);
                        if (last) {
                            done = true;
                        }
                        else {
                            sliceStart += subsize;
                            end = sliceStart + subsize;
                            if (end >= size) {
                                end = size;
                                last = true;
                            }
                        }
                    }
                    // this one gets written regardless, when size > subsize
                    appendSegment(stItems[0]);
                    i = 1;
                    _a.label = 2;
                case 2:
                    if (!(i < indx)) return [3 /*break*/, 6];
                    if (!kill_table) return [3 /*break*/, 3];
                    console.log("loop: " + i);
                    $('#sideTable').empty();
                    return [3 /*break*/, 6];
                case 3: return [4 /*yield*/, sleep(waitTime)];
                case 4:
                    _a.sent();
                    if (kill_table) {
                        console.log("during loop " + i);
                        $('#sideTable').empty();
                        return [3 /*break*/, 6];
                    }
                    else {
                        appendSegment(stItems[i]);
                    }
                    _a.label = 5;
                case 5:
                    i++;
                    return [3 /*break*/, 2];
                case 6: return [2 /*return*/];
            }
        });
    });
}
/**
 * The DOM elements for the side table are created and attached in this function;
 * The effect of enlarging the preview on mouseover, and the enabling of the
 * favorites and zoom icons are functions invoked after posting the elements.
 * This routine is a non-interruptable function that requires approx. 6 msec,
 * and can be invoked potentially multiple times by the formTbl async routine.
 */
function appendSegment(subset) {
    var jqSubset = [];
    for (var m = 0; m < subset.length; m++) {
        var obj = subset[m];
        var hno = obj.indx;
        //let hike_no = hno.toString()
        var tbl;
        if (favlist.includes(hno)) {
            tbl = tblItemHtml.replace('Yellow', 'Red');
        }
        else {
            tbl = tblItemHtml;
        }
        var lnk = '<a href="../pages/hikePageTemplate.php?hikeIndx=' + obj.indx +
            '" class="stlinks" target="_blank">' + obj.name + '</a>';
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
 * This function will track events on the favorites icons;
 * Note: update any 'favorites' [default from ]
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
                            favlist.push(hikeno);
                            newsrc = isrc.replace('Yellow', 'Red');
                            $tooltip.text('Unmark');
                            $that.attr('src', newsrc);
                        }
                        else {
                            alert("You must be a registered user\n" +
                                "in order to save Favorites");
                        }
                    },
                    error: function (_jqXHR, _textStatus, _errorThrown) {
                        var msg = "sideTables.js: attempting to mark user " +
                            "favorite (markFavorites.php)";
                        ajaxError(appMode, _jqXHR, _textStatus, msg);
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
                            var key = favlist.indexOf(hikeno);
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
                    error: function (_jqXHR, _textStatus, _errorThrown) {
                        var msg = "sideTracks.js: attempting to unmark " +
                            "a user favorite (markFavorites.php)";
                        ajaxError(appMode, _jqXHR, _textStatus, msg);
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
// Functions which process the set of hikes within a new map bounds
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
 * Effectively, remove diacritical from (Latin1) character
 */
function normalize(pgtitle) {
    var eng_title = pgtitle.replace(/À|Á|Â|Ã|Ä|Å/g, "A")
        .replace(/à|á|â|ã|ä|å/g, "a")
        .replace(/Ñ/g, "N")
        .replace(/ñ/g, "n")
        .replace(/Ò|Ó|Ô|Õ|Õ|Ö|Ø/g, "O")
        .replace(/ò|ó|ô|õ|ö|ø/g, "o")
        .replace(/È|É|Ê|Ë/g, "E")
        .replace(/è|é|ê|ë/g, "e")
        .replace(/Ç/g, "C")
        .replace(/ç/g, "c")
        .replace(/Ì|Í|Î|Ï/g, "I")
        .replace(/ì|í|î|ï/g, "i")
        .replace(/Ù|Ú|Û|Ü/g, "U")
        .replace(/ù|ú|û|ü/g, "u");
    return eng_title;
}
/**
 * This compare function is used to sort objects alphabetically
 */
function compareObj(a, b) {
    var hikea = a.name;
    var hikeb = b.name;
    var cp;
    // render Latin1 chars as if no diacriticals...
    for (var j = 0; j < hikea.length; j++) {
        cp = hikea.codePointAt(j);
        if (cp > 127) {
            hikea = normalize(hikea);
        }
    }
    for (var k = 0; k < hikeb.length; k++) {
        cp = hikeb.codePointAt(k);
        if (cp > 127) {
            hikeb = normalize(hikeb);
        }
    }
    var comparison;
    if (ascending) {
        if (hikea > hikeb) {
            comparison = 1;
        }
        else {
            comparison = -1;
        }
    }
    else {
        if (hikea < hikeb) {
            comparison = 1;
        }
        else {
            comparison = -1;
        }
    }
    return comparison;
}
/**
 * Functions to find elements within current map bounds and display them in
 * the side table. This is invoked by either a pan or a zoom on the map (see
 * map.js for listeners). This function also returns a set of hikenumbers for
 * making tracks when the map zoom >= 13. Clusters are 'segregated' so that the
 * entire set of hikes in the cluster can be drawn, each with a unique color.
 */
var IncludedHike = function (hike, zoom, north, south, east, west) {
    var isInBounds = false;
    if (zoom >= zoomThresh) {
        // test if displayed track's corner is in bounds
        var map_box = hike.bounds;
        var nw_corner = { lat: map_box[0], lng: map_box[3] };
        var ne_corner = { lat: map_box[0], lng: map_box[2] };
        var sw_corner = { lat: map_box[1], lng: map_box[3] };
        var se_corner = { lat: map_box[1], lng: map_box[2] };
        if (nw_corner.lat <= north && nw_corner.lat >= south &&
            nw_corner.lng <= east && nw_corner.lng >= west ||
            ne_corner.lat <= north && ne_corner.lat >= south &&
                ne_corner.lng <= east && ne_corner.lng >= west ||
            sw_corner.lat <= north && sw_corner.lat >= south &&
                sw_corner.lng <= east && sw_corner.lng >= west ||
            se_corner.lat <= north && se_corner.lat >= south &&
                se_corner.lng <= east && se_corner.lng >= west) {
            isInBounds = true;
        }
    }
    else {
        // tracks not displayed
        var lat = hike.loc.lat;
        var lng = hike.loc.lng;
        if (lng <= east && lng >= west && lat <= north && lat >= south) {
            isInBounds = true;
        }
    }
    return isInBounds;
};
var addHikeToTable = function (zoom, hike, type) {
    var hikeindx = allHikes.indexOf(hike.indx);
    var hikeobj = locations[hikeindx];
    var data = idHike(allHikes[hikeindx], hikeobj);
    hikearr.push(data);
    var iw = type === 'CL' ? '<div id="iwCH">' : '<div id="iwNH">';
    if (zoom) {
        var iw_data = iw + '<a href="../pages/hikePageTemplate.php?hikeIndx=' +
            hike.indx + '" target="_blank">' + hike.name + '</a><br />Length: ' +
            hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
            '<br />Difficulty: ' + hike.diff + '</div>';
        singles.push(hike.indx);
        hikeInfoWins.push(iw_data);
    }
};
var IdTableElements = function (boundsStr, zoom, zoom_level) {
    // initialize globals for each invocation
    hikearr = [];
    singles = [];
    hikeInfoWins = [];
    var trackColors = []; // for clusters, tracks get unique colors
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
    var max_color = colors.length - 1;
    var color_indx = Math.floor(Math.random() * max_color); // min is always 0
    // bounds are: north, south, east, west...
    CL.forEach(function (clus) {
        clus.hikes.forEach(function (hike) {
            if (IncludedHike(hike, zoom_level, north, south, east, west)) {
                addHikeToTable(zoom, hike, 'CL');
                trackColors.push(colors[color_indx++]);
                if (color_indx > max_color) { // rotate through colors
                    color_indx = 0;
                }
            }
        });
    });
    NM.forEach(function (hike) {
        if (IncludedHike(hike, zoom_level, north, south, east, west)) {
            addHikeToTable(zoom, hike, 'NM');
            trackColors.push(colors[color_indx++]);
            if (color_indx > max_color) { // rotate through colors
                color_indx = 0;
            }
        }
    });
    if (hikearr.length > 0) {
        hikearr.sort(compareObj);
        ascending = true;
    }
    // hikearr will be used in map.ts/js to invoke formTbl()
    return [hikearr, singles, hikeInfoWins, trackColors];
};
// Functions associated with moving the vertical side table bar
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
