"use strict"
/**
 * @fileoverview This script handles all four tabs - presentation and data
 * validation, also initialization of settings for <select> boxes, etc.
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 First release with Cluster Page editing
 */
$( function () {

/**
 * The framework/appearance of the edit page and buttons
 */
var tabCnt = $('.tablist').length;
// all tabs must be (and are currently) same width
var tabWidth = $('#t1').css('width'); // seems to include padding & margin!
var listwidth = tabCnt * parseInt(tabWidth); // fixed (no change w/resize)
var linewidth = $('#main').width() - listwidth;
$('#line').width(linewidth);
// 'Apply' buttons: positions on page
var $apply = $('div[id^="d"]'); // the divs holding the input 'Apply' button
var apwd = $('#ap1').width();
var aht = $('#atxt').height();
var awd = $('#atxt').width();
var centerMarg;
var btop;
var blft;
var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
$(window).trigger('scroll', function() {
    scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
});
/**
 * This places the 'Apply' button appropriately
 * 
 * @return {null}
 */
function positionApply() {
    var atxt = $('#atxt').offset();  // dynamic w/resizing
    centerMarg  = (awd - apwd)/2 - 4; // 4: allow for right margin
    btop = atxt.top + aht + 6; // 6 for spacing
    blft = atxt.left + centerMarg;
    return;
}
positionApply();

var applyPos = [];
$apply.each(function(indx) {
    var $inp = $(this).children().eq(0);
    applyPos[indx] = $inp.detach();
});
// when window is resized:
$(window).on('resize', function() {
    linewidth = $('#main').width() - listwidth;
    $('#line').width(linewidth);
    positionApply();
    var divid = '#d' + lastA;
    var scrollPos = btop + scrollTop;
    $(divid).offset({top: scrollPos, left: blft});
});
// tab buttons:
$('button[id^=t]').on('click', function(ev) {
    ev.preventDefault();
    var tid = this.id;
    $('button').each( function() {
        if (this.id !== tid) {
            if ($(this).hasClass('active')) {
                var old = this.id;
                old = '#tab' + old.substring(1,2);
                $(old).css('display','none');
                $(this).removeClass('active');
            }
        }
    });
    $(this).addClass('active');
    var newtid = '#tab' + tid.substring(1,2);
    $(newtid).css('display','block');
    document.documentElement.scrollTop = document.body.scrollTop = 0;
    if (tid.substr(0, 1) === 't') {
        var oldid = '#ap' + lastA;
        $(oldid).remove();
        lastA = tid.substr(1, 1);
        var newid = '#d' + lastA;
        $(newid).prepend(applyPos[lastA - 1]); // use index number, not tab no.
        $(newid).show();
        $(newid).offset({top: btop, left: blft});
        prepareSubmit(newid);
    }
});
// place correct tab (and apply button) in foreground - on page load only
var tab = $('#entry').text(); // tab # is saved in editDB.php
var tabon = '#t' + tab;
$(tabon).trigger('click');
var applyAdd = '#d' + tab;
var posNo = tab - 1; // index number, not tab number
$(applyAdd).prepend(applyPos[posNo]);
$(applyAdd).show();
$(applyAdd).offset({top: btop, left: blft});
var lastA = tab;
prepareSubmit($(applyAdd));

// If there is a user alert to show, set the message text:
var user_alert = '';
if (tab == '1' && $('#ua1').text() !== '') {
    user_alert = $('#ua1').text();
} else if (tab == '4' && $('#ua4').text() !== '') {
    user_alert = $('#ua4').text();
}
if (user_alert !== '') {
    alert(user_alert);
    $.get('resetAlerts.php');
}

/**
 * This section does data validation for the 'directions' URL and highlights
 * the textarea, with focus, if the URL failed validation.
 */

// Look for 'bad URLs' to highlight immediately after saveTabX.php
var blinkerObject = { blinkHandle: {} };
/**
 * This function cause an element to 'blink'
 * 
 * @param {string} elemId 
 * @param {string} tab 
 * 
 * @return {null}
 */
function activateBlink(elemId, tab) {
    document.getElementById(elemId).focus();
    if (tab == '1') {
        window.scrollTo(0, document.body.scrollHeight);
    }
    var $elem = $('#' + elemId);
    var blinker = 'blink' + elemId;
    blinkerObject[blinker] = setInterval(function() {
        if ($elem.css('visibility') == 'hidden') {
            $elem.css('visibility', 'visible');
        } else {
            $elem.css('visibility', 'hidden');
        }    
    }, 500);
    //alert("Please correct the URL or leave blank");
    $elem.on('mouseover', function() {
        clearInterval(blinkerObject[blinker]);
        $elem.css('visibility', 'visible');
    });
    return;
}
if (tab == '1' && $('#murl').val().trim() == '--- INVALID URL DETECTED ---') {
    activateBlink('murl',tab);
}
if (tab == '4') {
    $('.urlbox').each(function() {
        if ($(this).val().trim() == '--- INVALID URL DETECTED ---') {
            activateBlink($(this).attr('id'), tab);
        }
    });
}

// Preview edit page button
var hike = $('#hikeNo').text();
$('#preview').on('click', function() {
    var prevPg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + hike;
    window.open(prevPg,"_blank");
});

// show/hide lat lng data entries
$('#showll').on('click', function() {
    $('#lldisp').slideToggle();
    $(this).prop('checked',false);
});

// Pressing 'Return' while in textarea only adds newline chars, therefore:
window.onkeydown = function(event) {
    if(event.keyCode == 13) {
        if(event.preventDefault) event.preventDefault();
        return false; // Just a workaround for old browsers
    }
}

/**
 * The remaining script handles several features of the editor:
 *  1. Initialization of text and numeric fields based on db entries
 *  2. Registering changes in html elements that the server will utilize
 *  3. Validating data to match assigned database data types.
 * All of the cluster and references actions are grouped together as
 * noted by commments; otherwise code is segmented per the list.
 */
/**
 *              ----------- INITIALIZATION -----------
 * Each drop-down field parameter is held in a hidden <p> element;
 * the data (text) in that hidden <p> element is the default that should 
 * appear in the drop-down box on page-load;
 * The drop-down element parameters are:
 *      - locale
 *      - cluster group name
 *      - hike type
 *      - difficulty
 *      - exposure
 *      - references
 *      - waypoint icons
 * Each is treated, with identifiers, below:
*/
// Locale:
var sel = $('#locality').text();
$('#area').val(sel);
// Hike type:
var htype = $('#ctype').text();
$('#type').val(htype);
// Difficulty:
var diffic = $('#dif').text();
$('#diff').val(diffic);
// Exposure:
var exposure = $('#expo').text();
$('#sun').val(exposure);
// Waypoint icons when present in the gpx file:
var $gicons = $('[id^="gicn"]');
var $gbox   = $('[id^="selgicon"]');
$gbox.each(function(indx) {
    if ($gicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($gicons[indx].innerText);
    }
});
// Waypoint icons when present in the database
var $wicons = $('[id^="dicn"]');
var $wbox   = $('[id^="seldicon"]');
$wbox.each(function(indx) {
    if ($wicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($wicons[indx].innerText);
    }
});

/**
 * Cluster operation
 * The 'var newgrps' (see function) is set in tab1display.php
 */
var orignme = $('#group').text();
$('#clusters').val(orignme);  // page load value
if (orignme == '') {  // no incoming assignment:
    $('#notclus').css('display','inline')
} else {
	$('#showdel').css('display','block');
}
var clusnme = orignme;  // clusnme can change later

/**
 * This function determines whether or not the current selection in the
 * clusters <select> drop-down box is an unpublished group (which will
 * then display lat/lng for that group). Default value when there are no
 * unpublished groups is 'no display' (see editDB.css)
 * 
 * @return {null}
 */
const showClusCoords = () => {
    let match = false;
    let nglat;
    let nglng;
    if (newgrps.length > 0) {
        for (let k=0; k<newgrps.length; k++) {
            if (newgrps[k].group == clusnme) {
                nglat = newgrps[k].loc.lat;
                nglng = newgrps[k].loc.lng;
                match = true;
                break;
            }
        }
        if (match) {
            $('#cluslat').val(nglat);
            $('#cluslng').val(nglng);
            $('#newcoords').show();
            window.scrollTo(0,document.body.scrollHeight);
        } else {
            $('#newcoords').hide();
        }
    } else {
            $('#newcoords').hide();
    }
    return;
};
showClusCoords();

// Change Cluster selection:
$('#clusters').on('change', function() {
    if (this.value !== clusnme) {
        if (clusnme == '') {
            clusnme = "None assigned";
            $('#notclus').css('display','none');
            $('#showdel').css('display','block');
        }
        // let user know the existing cluster group assignment will change
        let msg = "Cluster type will be changed from:\n" + "Original setting: "
                + clusnme;
        msg += "\nTo: " + $(this).val();
        alert(msg); 
    } 
    clusnme = $(this).val();
    showClusCoords();
});
// Remove an existing cluster assignment:
$('#deassign').on('change', function() {
    if (this.checked) {
        $('#clusters').val('');
        $('#showdel').css('display','none');
        $('#notclus').css('display','inline');
        $('#newcoords').hide();
        clusnme = '';
        this.checked = false;
        alert("No group will be assigned to this hike");
    }
});
// End of cluster processing

/**
 * Database data validation: does item conform to database data type?
 * Note: there is no 'range' validation for most numbers, neither is there a
 * test to see if < 0 (except lng). The main qualification is conformity to db spec.
 * In addition, if a user clicks 'Apply' while the user is entering invalid
 * data, form submission is halted. The user is advised to fix the identified
 * issue(s) prior to clicking 'Apply'
 */
var issues = [];
/**
 * This function clears an issue when it has been corrected
 * @param {string} issue The issue key 
 * 
 * @return {null}
 */
const clearIssue = (issue) => {
    for (let i=0; i<issues.length; i++) {
        if (issue in issues[i]) {
            // this is the i-th element, so the index is i
            issues.splice(i, 1);
        }
    }
    return;
};
/**
 * Check to make sure a message isn't getting repeated due to multiple
 * incorrect entries by a user on the same data
 * @param {string} type 
 * @param {string} msg 
 * 
 * @return {boolean}
 */
const repeatIssue = (type, msg) => {
    for (let j=0; j<issues.length; j++) {
        if (type in issues[j]) {
            if (issues[j][type] == msg) {
                return true;
            }
        }
    }
    return false;
};

// miles: numeric, and up to two decimal points
const nan  = "The value entered is not a number";
const badmiles = "Please enter a number less than 50 for 'miles'\n" +
    "with a maximum of 2 decimal places";
$('#miles').on('change', function() {
    let submit;
    let milesissue = {miles: ""};
    let milesEntry = $('#miles').val();
    if (!isNaN(milesEntry)) {
        let milesString = milesEntry; // textareas are strings
        milesEntry = Number(milesEntry); // numerical version
        let regexp = /^\d+(\.\d{1,2})?$/;
        if (regexp.test(milesString)) {
            if (Math.abs(milesEntry) > 49.99) {
                alert(badmiles);
                submit = false;
                milesissue.miles = "Miles value too large";
            } else {
                $('input[name=usrmiles]').val("YES");
                submit = true;
            }
        } else {
            $('input[name=usrmiles]').val("YES");
            submit = true;
        }
    } else {
        alert(nan);
        submit = false;
        milesissue.miles = "Please correct 'miles' data";
    }
    if (!submit) {
        if (!repeatIssue('miles', milesissue.miles)) {
            issues.push(milesissue);
        }
    } else {
        clearIssue('miles');
    }
});
// elevation: up to five digits, integer
let badelev = "Only Integers less than 20,000 are allowed";
$('#elev').on('change', function() {
    let submit;
    let elevissue = {feet: ""};
    let elev = $('#elev').val();
    if (!isNaN(elev)) {
        let feet = Number(elev);
        if (Number.isInteger(feet)) {
            if (Math.abs(feet) > 19999) {
                alert(badelev);
                submit = false;
                elevissue.feet = "Elevation entry too large";
            } else {
                $('input[name=usrfeet]').val("YES");
                submit = true;
            }
        } else {
            alert(badelev);
            submit = false;
            elevissue.feet = "Elevation change must be an integer";
        }
    } else {
        alert(nan);
        submit = false;
        elevissue.feet = "Elevation data is not a number";
    }
    if (!submit) {
        if (!repeatIssue('feet', elevissue.feet)) {
            issues.push(elevissue);
        }
    } else {
        clearIssue('feet');
    }
});
// gpx: file name length 1024; NOTE: This also covers GPS Data uploads
$('input[type=file]').on('change', function() {
    var newname = this.files[0].name;
    if (newname.length > 1024) {
        alert("Only 1024 Characters are allowed for file names");
        this.value = null;
    }
});
// latitude & longitude checks:
const notlatlng = "The value entered does not conform to a ";
const notfloat  = "The value is not a decimal number";
const latneg    = "Latitudes for New Mexico must be positive";
const toolarge  = "The entered value exceeds bounds for a gps coord";
/**
 * This function checks the latitude entry for valid float value
 * @param {string} value Latitude value
 * 
 * @return {null}
 */
const latCheck = (loc, value) => {
    let submit;
    let latissue = {lat: ""};
    let locater = loc === 'hike' ? "For Hike Latitude: " :
         "For Cluster Group Latitude: ";
    if (value.indexOf('.') === -1) {
        alert(notfloat);
        submit = false;
        latissue.lat = locater + "Latitude is not a decimal number";
    } else {
        if (!isNaN(value)) {
            let lat = Number(value);
            if (lat < 0) {
                alert(latneg);
                submit = false;
                latissue.lat = locater + "Latitude must be positive number";
            } else {
                let decimal = /^[-+]?[0-9]+\.[0-9]+$/;
                if (decimal.test(lat)) {
                    if (Math.abs(lat) > 180) {
                        alert(toolarge);
                        submit = false;
                        latissue.lat = locater + "The latitude value is too large";
                    } else {
                        submit = true;
                    }
                } else {
                    alert(notlatlng + 'latitude');
                    submit = false;
                    latissue.lat = locater + "Please correct latitude data";
                }
            }
        } else {
            alert(nan);
            submit = false;
            latissue.lat = locater + "Latitude is not a number";
        }
    }
    if (!submit) {
        if (!repeatIssue('lat', latissue.lat)) {
            issues.push(latissue);
        } 
    } else {
        clearIssue('lat');
    }
    return;
};
const lngvalue  = "Longitudes must be negative values for New Mexico";
/**
 * This function checks the longitude entry for valid float value
 * @param {string} value The longitude value
 * 
 * @return {null}
 */
const lngCheck = (loc, value) => {
    let submit;
    let lngissue = {lng: ""};
    let locater = loc === 'hike' ? "For Hike Longitude: " :
         "For Cluster Group Longitude: ";
    if (value.indexOf('.') === -1) {
        alert(notfloat);
        submit = false;
        lngissue.lng = locater + "Longitude is not a decimal number";
    } else {
        if (!isNaN(value)) {
            let lng = Number(value);
            if (lng > 0) {
                alert(lngvalue);
                submit = false;
                lngissue.lng = locater + "Longitude must be negative";
            } else {
                let testval = Math.abs(lng);
                let decimal = /^[-+]?[0-9]+\.[0-9]+$/;
                if (decimal.test(testval)) {
                    if (testval > 180) {
                        alert(toolarge);
                        submit = false;
                        lngissue.lng = locater + "The longitude value is too large";
                    } else {
                        submit = true;
                    }
                } else {
                    alert(notlatlng + 'longitude');
                    submit = false;
                    lngissue.lng = locater + "Please correct longitude data";
                }
            }
        } else {
            alert(nan);
            submit = false;
            lngissue.lng = locater + "Longitude is not a number";
        }
    }
    if (!submit) {
        if (!repeatIssue('lng', lngissue.lng)) {
            issues.push(lngissue);
        }
    } else {
        clearIssue('lng');
    }
    return;
}
$('#lat').on('change', function() {
    latCheck('hike', $(this).val());
});
$('#cluslat').on('change', function() {
    latCheck('group', $(this).val());
});
$('#lon').on('change', function() {
    lngCheck('hike', $(this).val());
});
$('#cluslng').on('change', function() {
    lngCheck('group', $(this).val());
});

// GPS Data Section: 

// click-on-text length
$('textarea[name^=click]').each(function(j) {
    let submit;
    let key = 'cot' + j;
    let cotissue = {};
    cotissue[key] = "";
    $(this).on('change', function() {
        if ($(this).val().length > 256) {
            alert("Only 256 characters are allowed");
            submit = false;
            cotissue[key] = "Too many characters in click-text #" + (j+1);
        } else {
            submit = true;
        }
        if (!submit) {
            if (!repeatIssue(key, cotissue[key])) {
                issues.push(cotissue);
            }
        } else {
            clearIssue(key);
        }
    });
});

/**
 * Even though jQuery can access SOME elements with display:none (e.g. 
 * <p> elements), it can't seem to access certain other elements, in
 * this case the input form 'submits'. Instead, every time a tab changes,
 * invoke this function to enable issue reporting during submit.
 * @param {node} jQnode The node representing the submit button
 * 
 * @return {boolean}
 */
function prepareSubmit(jQnode) {
    $(jQnode).off('click').on('click', function(evt) {
        if (issues.length > 0) {
            let msg = "Please resolve the following issue(s)\n";
            issues.forEach(function(issue) {
                let okey = Object.keys(issue); // returns array
                msg += issue[okey[0]] + "\n";
            });
            alert(msg);
            evt.preventDefault();
            return;
        } else {
            // proceed w/submit
        }
    });
}

});  // end of 'page (DOM) loading complete'
