/// <reference types="bootstrap" />
declare var regions: HikeNoSet[];
interface Geo {
    lat: number;
    lng: number;
}
interface HikeNoSet {
    [key: string]: number[];
}
type HikeNos = number[];
/**
 * @fileoverview Management of options button
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 First release of responsive design
 * @version 1.1 Typescripted
 */
$(function() {

/**
 * This function merely positions the options drop-down wrt/table
 */
const optsPosition = () => {
    let tblwidth = <number>$('table').width();
    $('#floater').width(tblwidth);
    return;
};
optsPosition();
$(window).on('resize', function() {
    optsPosition();
});
$('#areas').hide();

let title = $('#trail').text();
$('#ctr').text(title);

var appMode = $('#appMode').text() as string;
// table parms
var $tbody = $('table').find('tbody');
var rows = $tbody.find('tr').toArray();
// js modal
var near_modal = new bootstrap.Modal(<HTMLElement>document.getElementById('near'), {
    keyboard: false
});

// dropdown selections:
$('#all').on('click', function(ev) {
    ev.preventDefault();
    $('#areas').hide();
    $('tbody').empty();
    $('tbody').append(rows);
});
$('#reg').on('click', function(ev) { 
    ev.preventDefault();
    $('#areas').show();
});
$('#cls').on('click', function(ev) {
    ev.preventDefault();
    $('#areas').hide();
    near_modal.show();
});

// setup area links
let $loc_list = $('#alist').find('li a'); // drop-down list of locales
$loc_list.each(function() {
    $(this).on('click', function(ev) {
        ev.preventDefault();
        let lochikes = [] as HTMLTableRowElement[];
        let locarea = <any>$(this).text();
        let hikeset: any = regions[locarea]; // regions are objects whose key is a locale (string)
        for (let i=0; i<rows.length; i++) {
            let item = <string>$(rows[i]).data('indx');
            let hikeno = parseInt(item)
            for (let j=0; j<hikeset.length; j++) {
                if (hikeno == hikeset[j]) {
                    lochikes.push(rows[i]);
                }
            }
        }
        $('tbody').empty();
        $('tbody').append(lochikes);
    });
});

// show radius hikes
$('#show').on('click', function(ev) {
    ev.preventDefault();
    let retval = true;
    var locale = $('#regions').val();
    var miles_str = <string>$('#miles').val();
    var miles_no  = parseFloat(miles_str);
    if (locale == "") {
        alert("You have not selected a region");
        retval = false;
    } else if (miles_str == '' || isNaN(miles_no)) {
        alert("Please enter a numerical value for miles");
        retval = false;
    } else {
        // proceed with list of hikes...
        $.ajax({ // returns array of location centers on success
            url: '../json/areas.json',
            dataType: 'json',
            success: function(json_data) {
                var areaLocCenters = json_data.areas;
                var coords = <Geo>{lat: 0, lng: 0};
                for (var j=0; j<areaLocCenters.length; j++) {
                    if (areaLocCenters[j].loc == locale) {
                        coords = <Geo>{
                            "lat": areaLocCenters[j].lat, 
                            "lng": areaLocCenters[j].lng
                        };
                        break;
                    }
                }
                filterHikes(miles_no, coords);
            },
            error: function(_jqXHR, _textStatus, _errorThrown) {
                if (appMode === 'development') {
                    var newDoc = document.open();
                    newDoc.write(_jqXHR.responseText);
                    newDoc.close();
                }
                else { // production
                    var msg = "An error has occurred: " +
                        "We apologize for any inconvenience\n" +
                        "The webmaster has been notified; please try again later";
                    alert(msg);
                    var ajaxerr = "Trying to access areas.json;\nError text: " +
                        _textStatus + "; Error: " + _errorThrown + ";\njqXHR: " +
                        _jqXHR.responseText;
                    var errobj = { err: ajaxerr };
                    $.post('../php/ajaxError.php', errobj);
                }
                return false;
            }
        });
    }
    return retval;
});

/**
 * This function creates the rows for the results table based on the 
 * filter parameters (radius from center pt, center pt). After creating
 * the table, it displays the results
 */
function filterHikes(radius: number, geo: Geo) {
    var nearby = [] as HTMLTableRowElement[];
    for (let j=0; j<rows.length; j++) {
        let lat = $(rows[j]).data('lat');
        let lng = $(rows[j]).data('lon');
        let distance = radialDist(lat, lng, geo.lat, geo.lng, 'M');
        if (distance <= radius) {
            nearby.push(rows[j]);
        }
    }
    $('tbody').empty();
    $('tbody').append(nearby);
}
/**
 * This function will return the radial distance between two lat/lngs
 */
function radialDist(lat1: number, lon1: number, lat2: number, lon2: number, unit: string) {
    if (lat1 === lat2 && lon1 === lon2) { return 0; }
    var radlat1 = Math.PI * lat1/180;
    var radlat2 = Math.PI * lat2/180;
    var theta = lon1-lon2;
    var radtheta = Math.PI * theta/180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist);
    dist = dist * 180/Math.PI;
    dist = dist * 60 * 1.1515;
    if (unit === "K") { dist = dist * 1.609344; }
    if (unit === "N") { dist = dist * 0.8684; }  // else result is in miles "M"
    return dist;
}

});