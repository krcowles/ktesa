/// <reference path='./map.d.ts' />
declare var thumb: string;
declare var preview: string;
declare var favlist: string[];
declare var hikeSources: AutoItem[];
interface AutoItem {
    value: string;
    label: string;
}
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
var alltrails = new bootstrap.Modal(<HTMLElement>document.getElementById('alltrails'), {
    keyboard: false
});
$('#advantages').on('click', function() {
    alltrails.show();
});

// Clear searchbar contents when user clicks on the "X"
$('#clear').on('click', function() {
    $('#search').val("");
});
// Establish searchbar as jQueryUI widget
$(".search").autocomplete({
    source: hikeSources,
    minLength: 2
});
// When user selects item from dropdown:
$("#search").on("autocompleteselect", function(event, ui) {
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
var sortableHikes: NM[] = [];
var ascending = true;
var sort_diff = false;
var sort_dist = false;
/**
 * This function [coupled with infoWin()] 'clicks' the infoWin
 * for the corresponding hike
 */
function popupHikeName(hikename: string) {
    var found = false;
    if (pgnames.includes(hikename)) { // These are 'Cluster Pages', not hikes
            let indx = pgnames.indexOf(hikename);
            hiliteObj = {obj: CL[indx].hikes, type: 'cl'};
            infoWin(CL[indx].group, CL[indx].loc);
            found = true;
    } else {
        for (let i=0; i<CL.length; i++) {
            for (let j=0; j<CL[i].hikes.length; j++) {
                if (CL[i].hikes[j].name == hikename) {
                    hiliteObj = {obj: CL[i].hikes[j], type: 'nm'};
                    infoWin(CL[i].group, CL[i].loc);
                    found = true;
                    break;
                }
            }
        }
    }
    if (!found) {
        for (let k=0; k<NM.length; k++) {
            if (NM[k].name == hikename) {
                hiliteObj = {obj: NM[k], type: 'nm'};
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
const infoWin = (hike:string, loc:GPS_Coord)  => {
    // highlight track for either searchbar or zoom-to icon:
    applyHighlighting = true;
    // clicking marker sets zoom
    for (let k=0; k<locaters.length; k++) {
        if (locaters[k].hikeid == hike) {
            if (locaters[k].clicked === false) {                
                google.maps.event.trigger(locaters[k].pin, 'click');
            } else {
                window.newBounds = true;
                map.setCenter(loc);
            }
            break;
        }
    }
    return;
}  
/**
 * This function emphasizes the hike track(s) that have been zoomed to;
 * NOTE: A javascript anomaly: passing in a single object in an array
 * results in the function receiving the object, but not as an array.
 * Hence a 'type' identifier is used here
 */
function highlightTracks() {
    if (!$.isEmptyObject(hiliteObj)) {
        if (hiliteObj.type === 'cl') { // object is an array of objects
            let cluster = <Normals>hiliteObj.obj;
            cluster.forEach(function(track) {
                let polyno = track.indx;
                for (let k=0; k<drawnTracks.length; k++) {
                    if (drawnTracks[k].hike == polyno) {
                        let polyline = drawnTracks[k].track;
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
        } else { // mrkr === 'nm'; object is a single object
            let nmobj = <NM>hiliteObj.obj;
            let polyno = nmobj.indx;
            for (let k=0; k<drawnTracks.length; k++) {
                if (drawnTracks[k].hike == polyno) {
                    let polyline = drawnTracks[k].track;
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
    for (let n=0; n<hilited.length; n++) {
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
const subsize  = 10;
const waitTime = 80; // msec
var done = false;
/**
 * The html 'wrapper' for each item included in the side table
 */
 var tblItemHtml: string;
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
const sleep = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));
// NOTE: async function returns a Promise to the caller (map.ts/js)
async function formTbl(indxArray: NM[]) {
    $('#sideTable').empty();
    sortableHikes = indxArray;
    if (indxArray.length === 0) {
        let nohikes = '<p style="padding-left:12px;font-size:18px;">' +
            'There are no hikes in the viewing area</p>';
        $('#sideTable').html(nohikes);
        return;
    }
    var size = indxArray.length;
    if (size <= subsize) {
        appendSegment(indxArray);
        if (kill_table) {
            $('#sideTable').empty();
        }
    } else {
        // there are more than 'subsize' no. of elements
        var stItems = [];
        stItems[0] = indxArray.slice(0, subsize);
        var sliceStart = subsize;
        var end = sliceStart + subsize;
        var last = false;
        if (end >= size) {
            end = size;
            last = true;
        }
        var indx = 1;
        var done = false;
        while (!done) {
            stItems[indx++] = indxArray.slice(sliceStart, end);
            if (last) {
                done = true;
            } else {
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
        // start repeating load, if no new map events are queueing up
        for (let i = 1; i < indx ; i++) {
            if (kill_table) {
                console.log("loop: " + i);
                $('#sideTable').empty();
                break;
            } else {
                await sleep(waitTime);
                if (kill_table) {
                    console.log("during loop " + i);
                    $('#sideTable').empty();
                    break;
                } else {
                    appendSegment(stItems[i]);
                }
            }
        }
    }
    return;
}
/**
 * The DOM elements for the side table are created and attached in this function;
 * The effect of enlarging the preview on mouseover, and the enabling of the 
 * favorites and zoom icons are functions invoked after posting the elements.
 * This routine is a non-interruptable function that requires approx. 6 msec,
 * and can be invoked potentially multiple times by the formTbl async routine.  
 */
function appendSegment(subset: NM[]) {
    let jqSubset: JQuery<HTMLElement>[] = [];
    for (let m=0; m<subset.length; m++) {
        let obj = subset[m];
        let hno = obj.indx;
        let hike_no = hno.toString()
        var tbl;
        if (favlist.includes(hike_no)) {
            tbl = tblItemHtml.replace('Yellow', 'Red');
        } else {
            tbl = tblItemHtml;
        }   
        let lnk = '<a href="../pages/hikePageTemplate.php?hikeIndx=' + obj.indx + 
            '" class="stlinks" target="_blank">' + obj.name + '</a>';
        tbl += lnk;
        tbl += '<br /><span class="subtxt">Rating: ' + obj.diff + ' / '
            + obj.lgth + ' miles';
        tbl += '</span><br /><span class="subtxtb">Elev Change: ';
        tbl += obj.elev + ' feet</span><p id="sidelat" style="display:none">';
        tbl += obj.loc.lat  + '</p><p id="sidelng" style="display:none">';
        tbl += obj.loc.lng + '</p></div>';
        tbl += '<div class="thumbs"><img src="' + thumb + 
            obj.prev + '" alt="preview image" class="thmbpic" /></div>';
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
/**
 * This function allows the user an enlarged view of the thumb when moused over
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
        let hno = hikelink.substring(digitpos);  // this is the string version of hike no
        let hikeno = parseInt(hno);              // this is the integer version of hike no
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
                            favlist.push(hno);
                            newsrc = isrc.replace('Yellow', 'Red');
                            $tooltip.text('Unmark');
                            $that.attr('src', newsrc);
                        } else {
                            alert("You must be a registered user\n" +
                                "in order to save Favorites");
                        }
                    },
                    error: function() {
                        let msg = "A server error occurred\nYou will not be able " +
                        "to save Favorites at this time:\nThe admin has been " +
                        "notified";
                        alert(msg);
                        let ajxerr = {err: "Mark favorites php error: save"};
                        $.post('../php/ajaxError.php', ajxerr);
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
                            let key = favlist.indexOf(hno);
                            favlist.splice(key, 1);
                            newsrc = isrc.replace('Red', 'Yellow');
                            $tooltip.text('Add to Favorites');
                            $that.attr('src', newsrc);
                        } else {
                            alert("You must be a registered user\n" +
                                "in order to save Favorites");
                        }
                    },
                    error: function() {
                        let msg = "A server error occurred\nYou will not be able " +
                            "to unsave Favorites at this time:\nThe admin has been " +
                            "notified";
                        alert(msg);
                        let ajxerr = {err: "Mark favorites php error: unsave"};
                        $.post('../php/ajaxError.php', ajxerr);
                    }
                });
            }
        });
    }
    return;
};
/**
 * This function will zoom to the correct map location for the corresponding
 * hike, and popup its infoWin and highlight it. It also displays a tooltip on mouseover.
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
// Functions which process the set of hikes within a new map bounds
/**
 * The following function returns the appropriate hike object based on the
 * incoming object (obj) and the subject hike number (indx) in that object.
 * Note that CL objects can have an array of hikes in their corresponding objects.
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
 * Effectively, remove diacritical from (Latin1) character
 */
function normalize(pgtitle: string) {
    var eng_title
        = pgtitle.replace(/À|Á|Â|Ã|Ä|Å/g, "A")
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
 function compareObj(a: NM, b: NM) {
    var hikea = a.name;
    var hikeb = b.name;
    var cp: number;
    // render Latin1 chars as if no diacriticals...
    for (let j=0; j<hikea.length; j++) {
        cp = hikea.codePointAt(j) as number;
        if (cp > 127) {
            hikea = normalize(hikea)
        }
    }
    for (let k=0; k<hikeb.length; k++) {
        cp = hikeb.codePointAt(k) as number;
        if (cp > 127) {
           hikeb = normalize(hikeb);
        }
    }
    var comparison: number;
    if (ascending) {
        if (hikea > hikeb) {
            comparison = 1;
        } else {
            comparison = -1;
        }
    } else {
        if (hikea < hikeb) {
            comparison = 1;
        } else {
            comparison = -1;
        }
    }
    return comparison;
}
/**
 * A function to find elements within current map bounds and display them in
 * the side table. This is invoked by either a pan or a zoom on the map (see
 * map.js for listeners). This function also returns a set of hikenumbers for
 * making tracks when the map zoom >= 13. Clusters are 'segregated' so that the
 * entire set of hikes in the cluster can be drawn, each with a unique color.
 */
 const IdTableElements = (boundsStr:string, zoom:boolean): [NM[],number[],string[],string[]] => {
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
    var max_color = colors.length - 1;
    var color_indx = Math.floor(Math.random() * max_color);  // min is always 0
    CL.forEach(function(clus) {
        clus.hikes.forEach(function(hike) {
            let lat = hike.loc.lat;
            let lng = hike.loc.lng;
            if (lng <= east && lng >= west && lat <= north && lat >= south) {
                let hikeindx = allHikes.indexOf(hike.indx);   
                let hikeobj = locations[hikeindx];
                let data = idHike(allHikes[hikeindx], hikeobj);
                hikearr.push(data);
                if (zoom) {
                    let cliw = '<div id="iwCH"><a href="../pages/hikePageTemplate.php?hikeIndx=' + 
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
    NM.forEach(function(hike) {
        let lat = hike.loc.lat;
        let lng = hike.loc.lng;
        if (lng <= east && lng >= west && lat <= north && lat >= south) {
            let hikeindx = allHikes.indexOf(hike.indx);
            let hikeobj = locations[hikeindx];
            let data = idHike(allHikes[hikeindx], hikeobj);
            hikearr.push(data);
            if (zoom) {
                let nmiw = '<div id="iwNH"><a href="../pages/hikePageTemplate.php?hikeIndx=' +
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
    if (hikearr.length > 0) {
        hikearr.sort(compareObj);
        ascending = true;
    }
    // hikearr will be used in map.ts/js to invoke formTbl()
    return [hikearr, singles, hikeInfoWins, trackColors];
}
// Functions associated with moving the vertical side table bar
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
