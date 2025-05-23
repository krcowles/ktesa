/// <reference path='./map.d.ts' />
declare var NM: NM[];      // favTable.php
declare var thumb: string; // favTable.php
declare var map: google.maps.Map;      // fmap.js
declare var map_bounds: google.maps.LatLngBounds; // fmap.js
declare var colors: string[];          // fmap.js
declare function locateGeoSym(): void; // fmap.js
declare function ajaxError(mode: string, xhrObj: object, status: string, attempting: string): void;
/**
 * @file This file was created as a simplification of sideTables.ts/js code,
 * which contains a significant amount of code not required by the Favorites page.
 * favTable.php calls favSideTable.js instead of sideTables.js
 * 
 * @author Ken Cowles
 * @version 1.1 Change <a> links to open new tab
 * @version 2.0 Allow user to select which favorites are displayed on the page (some may
 * be far apart, making it difficult to view the desired hikes)
 * @version 3.0 Switch to new google maps Marker, general reorg of code and cleanup.
 */
// Modal allowing user to select which favorites are displayed when there are multiple
var subset_modal = new bootstrap.Modal(<HTMLElement>document.getElementById('favlimit'), {
    keyboard: false
});
/**
 * NOTE: If there is more than one favorite, no side table elements are displayed
 *       until after modal selection; Map & page display are controlled by fmap.js
 */
// GLOBALS:
const subsize = 10;
var display_table_items = NM.length > 1  && $('#favmode').text() === 'no' ? false : true;
var appMode = $('#appMode').text() as string;
var init_fload = true;
/**
 * The html 'wrapper' for each item included in the side table
 */
var indexer: number;
var done = false;
var tblItemHtml: string;
// one tableItem div for each side table hike
tblItemHtml = '<div class="tableItem"><div class="tip">Add to Favorites</div>';
// the div holding the favorites icon and the zoom-to-map icon
tblItemHtml += '<div class="icons">';
tblItemHtml += '<img class="like" src="../images/favoritesRed.png" alt="favorites icon" />';
tblItemHtml += '<br /><img class="zoomers" src="../images/mapZoom.png" alt="zoom symbol" />';
tblItemHtml += '<span class="zpop">Zoom to Hike</span>';
tblItemHtml += '</div>';
// the div holding the hike-specific data
tblItemHtml += '<div class="content">';
// User clicks on choice in modal...
$('body').on('click', '#show_limited', function() { // modal button accepting user track choice
    var keepAll = false;
    var items = $('input.mod_chk');
    if ($(items[0]).prop("checked")) {
        keepAll = true;
    }
    var showHikes = [] as string[];
    items.each(function(indx, hike) {
        if (keepAll && indx !== 0) {
            showHikes.push(hike.id);
        } else if (!keepAll) {
            if ($(hike).prop("checked")) {
                showHikes.push(hike.id);
            }
        }
    });
    if (showHikes.length === 0) {
        alert("You have not checked any boxes...");
        return false;
    } else {
        var qstring: string[] = [];
        var query: string;
        showHikes.forEach(function(hike) {
            qstring.push("modal_hikes[]=" + hike);
        });
        if (qstring.length > 1) {
            query = qstring.join("&");
        } else {
            query = qstring[0];
        }
        var redo = "../pages/favTable.php?" + query;
        window.open(redo, "_self");
    }

});
// If modal has not yet been displayed and there are multiple favorites...
if (!display_table_items) {  // modal not previously displayed
    // create list
    var modalHikes = '<li><input id="0" class="mod_chk" type="checkbox" />&nbsp;&nbsp;' +
        'Keep All Hikes</li>';
    NM.forEach(function(hikeobj) {
        modalHikes += '<li><input id="' + hikeobj.indx + '" type="checkbox" class="mod_chk"' + 
            '<scan>&nbsp;&nbsp;' + hikeobj.name + '</scan></li>';
    });
    $('#show_only').append(modalHikes);
    subset_modal.show();
} 
/**
 * This function centers on the hike, zooms as needed, and clicks 'infoWin'
 * for the corresponding hike
 */
function popupHikeName(hikename: string) {
    var found = false;
    for (let k=0; k<NM.length; k++) {
        if (NM[k].name == hikename) {
            map.setCenter(NM[k].loc);
            let czoom = map.getZoom() as number;
            if (czoom <= 13) {
                map.setZoom(13);
            }
            found = true;
            break;
        }
    }
    if (!found) {
        alert("This hike cannot be located in the list of hikes");
    }
    return;
}
/**
 * The side table includes all favorites hikes on page load; on pan/zoom it will
 * include only those hikes within the map bounds.
 */
function compareObj(a: NM, b: NM) {
    var hikea = a.name;
    var hikeb = b.name;
    var comparison: number;
    if (hikea > hikeb) {
        comparison = 1;
    } else {
        comparison = -1;
    }
    return comparison;
}

/**
 * The DOM elements for the side table are created and attached in this function;
 * To reduce apparent 'thumb image' load times, the table is created 'subsize'
 * elements at a time, per interval. The table will populate the topmost items
 * first with no wait.    
 */
function appendSegment(subset: NM[]) {
    let jqSubset: JQuery<HTMLElement>[] = [];
    for (let m=0; m<subset.length; m++) {
        let obj = subset[m];
        let hno = obj.indx;
        var tbl = tblItemHtml;
        let lnk = '<a class="stlinks" href="../pages/hikePageTemplate.php?hikeIndx=' + 
            hno + '" target="_blank">' + obj.name + '</a>';
        tbl += lnk;
        tbl += '<br /><span class="subtxt">Rating: ' + obj.diff + ' / '
            + obj.lgth + ' miles';
        tbl += '</span><br /><span class="subtxt">Elev Change: ';
        tbl += obj.elev + ' feet</span><p id="sidelat" style="display:none">';
        tbl += obj.loc.lat  + '</p><p id="sidelng" style="display:none">';
        tbl += obj.loc.lng + '</p></div>';
        tbl += '<div class="thumbs"><img src="' + thumb + 
            obj.prev + '" alt="preview image" /></div>';
        tbl += '</div>';
        let $tbl = $(tbl);
        $('#sideTable').append($tbl);
        // Note: $tbl must be appended before adding to array!!
        jqSubset.push($tbl);
    }
    enlargePreview(jqSubset);
    enableFavorites(jqSubset);
    enableZoom(jqSubset);
    return;
}
function formTbl(indxArray: NM[]) {
    $('#sideTable').empty();
    appendSegment(indxArray);
    return;
}

/**
 * This function allows the user an enlarged view of the preview when moused over
 */
function enlargePreview(items: JQuery<HTMLElement>[]) {
    for (let i=0; i<items.length; i++) {
        // setup mouse behavior on thumb
        let idiv = items[i].find('.thumbs');
        let $image = idiv.children().eq(0);
        $image.on('mouseover', function() {
            let ipos = <JQuery.Coordinates>$(this).offset();
            let left = (ipos.left - 280) + 'px'; 
            let top  = (ipos.top  - 60) + 'px';
            let isrc = <string>$(this).attr('src');
            isrc = isrc.replace("thumbs", "previews")
            let expand = '<img class="bigger" src="' + isrc + '" />';
            let $img = $(expand);
            $img.css({
                top: top,
                left: left,
                zIndex: 100
            });
            $('body').append($img);
        });
        $image.on('mouseout', function() {
            $('.bigger').remove();
        });
        // position tooltip
        let $ttdiv = items[i].children().eq(0);  // div holding tooltip
        let $icndiv = $ttdiv.next().children().eq(0); // <img holding 'Like' symbol
        positionFavToolTip($ttdiv, $icndiv);
    }
    return;
}
/**
 * This function is required in order to reposition the like popups after
 * resizing
 */
function positionFavToolTip(tipdiv: JQuery<HTMLElement>, icon: JQuery<HTMLElement>) {
    let likeSym = <string>icon.attr('src');
    if (likeSym.indexOf('Yellow') === -1) {
        tipdiv[0].innerHTML = 'Unmark Favorite';
    }
    icon.on('mouseover', function() {
        let pos = <HTMLPosition>$(this).offset();
        let left = pos.left - 128 + 'px'; // width of tip is 120px
        let top = pos.top + 'px';
        tipdiv[0].style.top = top;
        tipdiv[0].style.left = left;
        tipdiv[0].style.display = 'block';
    });
    icon.on('mouseout', function() {
        tipdiv[0].style.display = 'none';
    });
    return;
}
/**
 * This function will track events on the favorites icons
 */
function enableFavorites(items: JQuery<HTMLElement>[]) {
    for (let k=0; k<items.length; k++) {
        let $icndiv = items[k].children().eq(1);  // icons div
        let $favicn = $icndiv.children().eq(0);   // 'like' <img> element
        // retrieve hike no from content div
        let hikelink = <string>$icndiv.next().children().eq(0).attr('href');
        let digitpos = hikelink.indexOf('=') + 1;
        let hno = hikelink.substr(digitpos);
        let hikeno = parseInt(hno);
        $favicn.off('click').on('click', function() {    
            let ajaxdata:AjaxData = {no: hikeno};
            let isrc = <string>$(this).attr('src');
            let newsrc;
            let $tooltip = $(this).parent().prev();
            let $that = $(this);
            if (isrc.indexOf('Yellow') !== -1) { // currently a not favorite
                ajaxdata.action = 'add';
                $.ajax({
                    url: "markFavorites.php",
                    method: "post",
                    data: ajaxdata,
                    dataType: "text",
                    success: function(results) {
                        if (results === "OK") {
                            newsrc = isrc.replace('Yellow', 'Red');
                            $tooltip.text('Unmark');
                            $that.attr('src', newsrc);
                        } else {
                            alert("You must be a registered user\n" +
                                "in order to save Favorites");
                        }
                    },
                    error: function(_jqXHR, _textStatus, _errorThrown) {
                        let msg = "favSideTable.js: attempting to mark user " +
                            "favorite (markFavorites.php)";
                        ajaxError(appMode, _jqXHR, _textStatus, msg);
                    }
                });
            } else { // currently a favorite
                ajaxdata.action = 'delete';
                $.ajax({
                    url: "markFavorites.php",
                    method: "post",
                    data: ajaxdata,
                    dataType: "text",
                    success: function(results) {
                        if (results === 'OK') {
                            newsrc = isrc.replace('Red', 'Yellow');
                            $tooltip.text('Add to Favorites');
                            $that.attr('src', newsrc);
                        } else {
                            alert("You must be a registered user\n" +
                                "in order to save Favorites");
                        }
                    },
                    error: function(_jqXHR, _textStatus, _errorThrown) {
                        let msg = "sideTracks.js: attempting to unmark " +
                            "a user favorite (markFavorites.php)";
                        ajaxError(appMode, _jqXHR, _textStatus, msg);
                    }
                });
            }
        });
    }
    return;
};

/**
 * This function will zoom to the correct map location for the corresponding
 * hike, and popup its infoWin. It also displays a tooltip on mouseover.
 */
function enableZoom(items: JQuery<HTMLElement>[]) {
    for (let j=0; j<items.length; j++) {
        let $mag = items[j].find('.zoomers');
        $mag.on('click', function() {
            let hikename = $(this).parent().next().children().eq(0).text();
            popupHikeName(hikename);
        });
        $mag.on('mouseover', function() {
            let zpos = <HTMLPosition>$(this).offset();
            let hpos = zpos.left - 108;
            let vpos = zpos.top;
            $(this).next().css('left', hpos);
            $(this).next().css('top', vpos);
            $(this).next().css('display', 'block');
        });
        $mag.on('mouseout', function() {
            $(this).next().css('display', 'none');
        });
    }
    return;
}

/**
 * The following function returns the appropriate hike object based on the
 * incoming object (obj) and the subject hike number (indx) in that object.
 * It is invoked by the IdTableElements function.
 */
 function idHike(indx:number, obj:HikeObjectLocation):NM {
    if (obj.type === 'cl') {
        let clobj = CL[obj.group];
        let clhikes = clobj.hikes;
        for (let m=0; m<clhikes.length; m++) {
            if (clhikes[m].indx === indx) {
                return clhikes[m];
            }
        }
    } else if (obj.type === 'nm') {
       return NM[obj.group];
    }
    return <never>'';
}
/**
 * A function to find elements within current map bounds and display them in
 * the side table. The function is invoked in fmap.ts when map is idle.
 * This function returns a set of hikenumbers for making tracks when the
 *  map zoom >= 13. Clusters are 'segregated' so that the
 * entire set of hikes in the cluster can be drawn, each with a unique color.
 */
 function IdTableElements (boundsStr:string, zoom:boolean):[number[],string[],string[]]
 {
    var singles: number[] = [];       // individual hike nos
    var trackColors: string[] = [];   // for clusters, tracks get unique colors
    var hikeInfoWins: string[] = [];  // info window content for each hikeno in singles
    // ESTABLISH CURRENT VIEWPORT BOUNDS:
    var beginA = boundsStr.indexOf('((') + 2;
    var leftParm = boundsStr.substring(beginA,boundsStr.length);
    var beginB = leftParm.indexOf('(') + 1;
    var rightParm = leftParm.substring(beginB,leftParm.length);
    var south = parseFloat(leftParm);
    var north = parseFloat(rightParm);
    var westIndx = leftParm.indexOf(',') + 1;
    var westStr = leftParm.substring(westIndx,leftParm.length);
    var west = parseFloat(westStr);
    var eastIndx = rightParm.indexOf(',') + 1;
    var eastStr = rightParm.substring(eastIndx,rightParm.length);
    var east = parseFloat(eastStr);
    /* FIND HIKES WITHIN THE CURRENT VIEWPORT BOUNDS */
    var hikearr: NM[] = [];
    NM.forEach(function(hike) {
        let lat = hike.loc.lat;
        let lng = hike.loc.lng;
        if (lng <= east && lng >= west && lat <= north && lat >= south) {
            hikearr.push(hike);
            if (zoom) {
                let nmiw = '<div id="iwNH"><a href="../pages/hikePageTemplate.php?hikeIndx=' +
                    hike.indx + '" target="_blank">' + hike.name + '</a><br />Length: ' +
                    hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
                    '<br />Difficulty: ' + hike.diff + '</div>';
                singles.push(hike.indx);
                hikeInfoWins.push(nmiw);
                trackColors.push(colors[0]);
            }
        }
    });
    if ( hikearr.length === 0 ) {
        $('#sideTable').empty();
        let nohikes = '<p style="padding-left:12px;font-size:18px;">' +
            'There are no hikes in the viewing area</p>';
        $('#sideTable').html(nohikes);
    } else {
        if (display_table_items) {
            hikearr.sort(compareObj);
            formTbl(hikearr);
            if (init_fload) {
                map.fitBounds(map_bounds); // after initial load, don't reset!
                init_fload = false;
            }
        }
    }
    return [singles, hikeInfoWins, trackColors];
 
}

var grabber = <HTMLElement>document.getElementById('adjustWidth');
grabber.addEventListener('mousedown', changeWidth, false);
/**
 * Function to change div widths when mousedown on 'grabber' (#adjustWidth)
 * Thie function adds a mousemove listener to track the mouse location
 */
function changeWidth(ev:MouseEvent) {
    ev.preventDefault(); // prevents selecting other elements while mousedown
    document.addEventListener('mousemove', widthSizer, false);
    return;
}

/**
 * The function is called by the mousemove event listener. It is necessary
 * not to use anonymous functions here as those listeners cannot be removed.
 * When the mouse moves, a listener is add to detect when the mouse is released.
 */
function widthSizer(evt:MouseEvent) {
    document.addEventListener('mouseup', stopMoving, false);
    let viewport = window.innerWidth;
    let sideWidth = viewport - evt.clientX - 3;
    $('#map').width(evt.clientX);
    $('#sideTable').width(sideWidth);
    $('.like').each(function() {
        let $icon = $(this);
        let $tooldiv = $icon.parent().prev();
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
