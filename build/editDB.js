"use strict";
/**
 * @fileoverview This script handles all four tabs - presentation and data
 * validation, also initialization of settings for <select> boxes, etc.
 *
 * @author Ken Cowles
 *
 * @version 2.0 First release with Cluster Page editing
 * @version 2.1 Typescripted
 */
$(function () {
    /**
     * The framework/appearance of the edit page and buttons
     */
    var tabCnt = $('.tablist').length;
    // all tabs assumed to be the same width
    var tabWidth = $('#t1').css('width');
    var listwidth = tabCnt * parseInt(tabWidth); // fixed (no change w/resize)
    var linewidth = $('#main').width() - listwidth;
    $('#line').width(linewidth);
    // globals
    var tabstr;
    var subs = [];
    for (var j = 1; j <= 4; j++) {
        var btn = '<button id="ap' + j + '" class="subbtn">Apply</button>';
        var jqbtn = $(btn);
        subs[j] = jqbtn;
    }
    // initial button placed in order to establish global width
    $('#f1').prepend(subs[1]);
    var apwd = subs[1].width();
    // text string below which the apply button is placed
    var awd = $('#atxt').width();
    var aht = $('#atxt').height();
    // initial settings on page load
    var btop = 0;
    var blft = 0;
    var tabstr = $('#entry').text(); // tab # is saved in 'editDB.php' element
    var tabint = parseInt(tabstr);
    var lastA = tabint;
    var tabon = '#t' + tabstr;
    var issues = [];
    /**
     * This will place the tab's 'Apply' (submit) button appropriately
     */
    function positionApply(tab) {
        var atxt = $('#atxt').offset();
        var centerMarg = (awd - apwd) / 2 - 4; // 4: allow for right margin
        btop = atxt.top + aht + 6; // 6 for spacing
        blft = atxt.left + centerMarg;
        subs[tab].css({
            position: "fixed",
            top: btop,
            left: blft
        });
        var form = "#f" + tab;
        $(form).prepend(subs[tab]);
        return;
    }
    /**
     * Each 'Apply' button is added when the tab is displayed, so ensure that
     * the apply button re-establishes click behavior.
     */
    function prepareSubmit(elementId) {
        $(elementId).off('click').on('click', function (evt) {
            if (issues.length > 0) {
                var msg_1 = "There is one or more issues outstanding to resolve:\n";
                issues.forEach(function (issue) {
                    var okey = Object.keys(issue); // returns array
                    msg_1 += issue[okey[0]] + "\n";
                });
                alert(msg_1);
                evt.preventDefault();
                return;
            }
            else {
                // proceed w/submit
            }
        });
    }
    // clicking on tab buttons:
    $('button[id^=t]').on('click', function (ev) {
        ev.preventDefault();
        var tid = this.id;
        $('button').each(function () {
            if (this.id !== tid) {
                if ($(this).hasClass('active')) {
                    var old = this.id;
                    old = '#tab' + old.substring(1, 2);
                    $(old).css('display', 'none');
                    $(this).removeClass('active');
                }
            }
        });
        $(this).addClass('active');
        tabint = parseFloat(tid.substring(1, 2));
        var newtid = '#tab' + tabint;
        $(newtid).css('display', 'block');
        var currbtn = "#ap" + lastA;
        $(currbtn).remove();
        // change lastA to current tab no.
        lastA = tabint;
        var newid = '#ap' + lastA;
        positionApply(lastA); // position the apply button for this tab
        prepareSubmit(newid);
    });
    // place correct tab (and apply button) in foreground - on page load only
    $(tabon).trigger('click');
    // If there is a user alert to show, set the message text:
    var user_alert = '';
    if (tabstr == '1' && $('#ua1').text() !== '') {
        user_alert = $('#ua1').text();
    }
    else if (tabstr == '4' && $('#ua4').text() !== '') {
        user_alert = $('#ua4').text();
    }
    if (user_alert !== '') {
        alert(user_alert);
        $.get('resetAlerts.php');
    }
    // set max additional gpx files at 3
    var listItems = $("#addlist").children();
    var count = listItems.length;
    if (count > 0 && count < 3) {
        var addmore = 3 - count;
        if (addmore > 0) { // can only be 1 or 2...
            $('#addno').text(addmore);
            if (addmore === 1) {
                $('#li3').hide();
                $('#li2').hide();
            }
            else {
                $('#li3').hide();
            }
        }
    }
    else if (count === 3) {
        $('#addno').text('0');
        $('#li3').hide();
        $('#li2').hide();
        $('#li1').hide();
    }
    /**
     * If a main gpx file is checked for delete and there are additional files already
     * specified, AND no newmain is specified, alert user that a new main file must be
     * specified to keep the additional files.
     */
    $('input[name=dgpx]').on('change', function () {
        if ($(this).is(':checked')) {
            var gpxfile_selected = $('#gpxfile1').get(0);
            if (gpxfile_selected.files.length === 0) {
                if (count > 0) {
                    alert("You must specify a main file\n" +
                        "Otherwise additional files will be removed");
                }
            }
        }
    });
    // only allow additional file specs if there is a main
    $('input[name^=addgpx]').each(function () {
        $(this).on('change', function () {
            var inputel = document.getElementById("gpxfile1");
            var filedata = inputel.files[0];
            if (typeof filedata === 'undefined') {
                if ($('input[name=dgpx]').is(':checked') || $('#mgpx').text() === '') {
                    alert("You must first specify a main gpx file\n or have one" +
                        " already uploaded [not to be deleted]");
                    $(this).val('');
                }
            }
        });
    });
    /**
     * This section does data validation for the 'directions' URL, and the URL's on tab4.
     * In the case of tab1, php validation filtering doesn't work when applied to google maps
     * links, so this URL is tested via js. Tab4 uses php validation filters. In either case,
     * when the URL fails validation, the textarea blinks, and an alert pops up.
     */
    var blinkerItems = [];
    function tab1Url(uri) {
        var trial = /^(ftp|http|https):\/\//.test(uri);
        if (!trial && uri !== '') {
            activateBlink('murl', tabstr);
        }
    }
    $('#murl').on('change', function () {
        var murltxt = this;
        var testtxt = murltxt.value;
        // without a slight delay, the focus gets lost
        setTimeout(function () {
            tab1Url(testtxt);
        }, 200);
    });
    if (tabstr == '1') {
        var urltxt = $('#murl').val();
        if (urltxt !== '') {
            tab1Url(urltxt);
        }
    }
    if (tabstr == '4') {
        $('.urlbox').each(function () {
            var urlitem = $(this).val();
            if (urlitem.trim() == '--- INVALID URL DETECTED ---') {
                var urlid = $(this).attr('id');
                activateBlink(urlid, tabstr);
            }
        });
    }
    /**
     * This function cause an element to 'blink'
     */
    function activateBlink(elemId, tabstr) {
        var blink_el = document.getElementById(elemId);
        blink_el.focus({ preventScroll: false });
        if (tabstr == '1') {
            window.scrollTo(0, document.body.scrollHeight);
        }
        alert("This URL appears to be invalid");
        var $elem = $('#' + elemId);
        var blinkerObject = setInterval(function () {
            if ($elem.css('visibility') == 'hidden') {
                $elem.css('visibility', 'visible');
            }
            else {
                $elem.css('visibility', 'hidden');
            }
        }, 500);
        blinkerItems.push(blinkerObject);
        var ptr = blinkerItems.length - 1;
        $elem.on('mouseover', function () {
            clearInterval(blinkerItems[ptr]);
            $elem.css('visibility', 'visible');
        });
        return;
    }
    // Preview edit page button
    var hike = $('#hikeNo').text();
    $('#preview').on('click', function () {
        var prevPg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + hike;
        window.open(prevPg, "_blank");
    });
    // show/hide lat lng data entries
    $('#showll').on('click', function () {
        $('#lldisp').slideToggle();
    });
    // Pressing 'Return' while in textarea only adds newline chars, therefore:
    window.onkeydown = function (event) {
        var retval = true;
        if (event.key == 'Enter') {
            if (event.preventDefault)
                event.preventDefault();
            retval = false; // Just a workaround for old browsers
        }
        return retval;
    };
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
    var $gbox = $('[id^="selgicon"]');
    $gbox.each(function (indx) {
        if ($gicons[indx].innerText == '') {
            $(this).val('googlemini');
        }
        else {
            $(this).val($gicons[indx].innerText);
        }
    });
    // Waypoint icons when present in the database
    var $wicons = $('[id^="dicn"]');
    var $wbox = $('[id^="seldicon"]');
    $wbox.each(function (indx) {
        if ($wicons[indx].innerText == '') {
            $(this).val('googlemini');
        }
        else {
            $(this).val($wicons[indx].innerText);
        }
    });
    /**
     * Cluster operation
     */
    var orignme = $('#group').text();
    $('#clusters').val(orignme); // page load value
    if (orignme == '') { // no incoming assignment:
        $('#notclus').css('display', 'inline');
    }
    else {
        $('#showdel').css('display', 'block');
    }
    var clusnme = orignme; // clusnme can change later
    /**
     * This function determines whether or not the current selection in the
     * clusters <select> drop-down box is an unpublished group (which will
     * then display lat/lng for that group). Default value when there are no
     * unpublished groups is 'no display' (see editDB.css)
     * The 'var newgrps' is established via php in tab1display.php
     */
    var showClusCoords = function () {
        var match = false;
        var nglat = 0;
        var nglng = 0;
        if (newgrps.length > 0) {
            for (var k = 0; k < newgrps.length; k++) {
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
                window.scrollTo(0, document.body.scrollHeight);
            }
            else {
                $('#newcoords').hide();
            }
        }
        else {
            $('#newcoords').hide();
        }
        return;
    };
    showClusCoords();
    // Change Cluster selection:
    $('#clusters').on('change', function () {
        var clusinput = this;
        if (clusinput.value !== clusnme) {
            if (clusnme == '') {
                clusnme = "None assigned";
                $('#notclus').css('display', 'none');
                $('#showdel').css('display', 'block');
            }
            // let user know the existing cluster group assignment will change
            var msg = "Cluster type will be changed from:\n" + "Original setting: "
                + clusnme;
            msg += "\nTo: " + $(this).val();
            alert(msg);
        }
        clusnme = $(this).val();
        showClusCoords();
    });
    // Remove an existing cluster assignment:
    $('#deassign').on('change', function () {
        var cboxitem = this;
        if (cboxitem.checked) {
            $('#clusters').val('');
            $('#showdel').css('display', 'none');
            $('#notclus').css('display', 'inline');
            $('#newcoords').hide();
            clusnme = '';
            cboxitem.checked = false;
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
    /**
     * This function clears an issue when it has been corrected
     */
    var clearIssue = function (issuekey) {
        for (var i = 0; i < issues.length; i++) {
            if (issuekey in issues[i]) {
                // this is the i-th element, so the index is i
                issues.splice(i, 1);
            }
        }
        return;
    };
    /**
     * Check to make sure a message isn't getting repeated due to multiple
     * incorrect entries by a user on the same data
     */
    var repeatIssue = function (type, msg) {
        for (var j = 0; j < issues.length; j++) {
            if (type in issues[j]) {
                if (issues[j][type] == msg) {
                    return true;
                }
            }
        }
        return false;
    };
    // miles: numeric, and up to two decimal points
    var nan = "The value entered is not a number";
    var badmiles = "Please enter a number less than 50 for 'miles'\n" +
        "with a maximum of 2 decimal places";
    $('#miles').on('change', function () {
        var submit = true;
        var milesissue = { miles: "" };
        clearIssue("miles"); // any previous issue is no longer relevant
        var milesString = $('#miles').val();
        var milesEntry = Number(milesString);
        if (!isNaN(milesEntry)) {
            var regexp = /^\d+(\.\d{1,2})?$/;
            if (regexp.test(milesString)) {
                if (Math.abs(milesEntry) > 49.99) {
                    alert(badmiles);
                    submit = false;
                    milesissue.miles = "Miles value too large";
                }
                else {
                    $('input[name=usrmiles]').val("YES");
                }
            }
            else {
                alert(badmiles);
                submit = false;
                milesissue.miles = "Greater than two digits after the decimal";
            }
        }
        else {
            alert(nan);
            submit = false;
            milesissue.miles = "Miles data is not a number";
        }
        if (!submit) {
            issues.push(milesissue);
        }
    });
    // elevation: up to five digits, integer
    var badelev = "Only Integers less than 10,000 are allowed";
    $('#elev').on('change', function () {
        var submit = true;
        var elevissue = { feet: "" };
        clearIssue("feet");
        var elevString = $('#elev').val();
        var elev = Number(elevString);
        if (!isNaN(elev)) {
            var feet = elev;
            if (Number.isInteger(feet)) {
                if (Math.abs(feet) > 9999) {
                    alert(badelev);
                    submit = false;
                    elevissue.feet = "Elevation entry too large";
                }
                else {
                    $('input[name=usrfeet]').val("YES");
                }
            }
            else {
                alert(badelev);
                submit = false;
                elevissue.feet = "Elevation change must be an integer";
            }
        }
        else {
            alert(nan);
            submit = false;
            elevissue.feet = "Elevation data is not a number";
        }
        if (!submit) {
            if (!repeatIssue('feet', elevissue.feet)) {
                issues.push(elevissue);
            }
        }
    });
    // gpx: file name length 1024; NOTE: This also covers GPS Data uploads
    $('input[type=file]').each(function () {
        $(this).on('change', function () {
            var gpxitem = this;
            var newname = gpxitem.files[0].name;
            if (newname.length > 1024) {
                alert("Only 1024 Characters are allowed for file names");
                gpxitem.value = null;
            }
        });
    });
    // latitude & longitude checks:
    var notfloat = "The value is not a decimal number";
    var latneg = "Latitudes for New Mexico must be positive";
    var toolarge = "The entered value exceeds bounds for a gps coord";
    /**
     * This function checks the latitude entry for valid float value
     */
    var latCheck = function (loc, value) {
        var submit = true;
        var latissue = { lat: "" };
        clearIssue("lat");
        var locater = loc === 'hike' ? "For Hike Latitude: " :
            "For Cluster Group Latitude: ";
        var decimal = /^[-+]?[0-9]+\.[0-9]+$/;
        if (!decimal.test(value)) {
            alert(notfloat);
            submit = false;
            latissue.lat = locater + "Latitude is not a decimal number";
        }
        else {
            var lat = Number(value);
            if (!isNaN(lat)) {
                if (lat < 0) {
                    alert(latneg);
                    submit = false;
                    latissue.lat = locater + "Latitude must be positive number";
                }
                else if (Math.abs(lat) > 180) {
                    alert(toolarge);
                    submit = false;
                    latissue.lat = locater + "The latitude value is too large";
                }
            }
            else {
                alert(nan);
                submit = false;
                latissue.lat = locater + "Latitude is not a number";
            }
        }
        if (!submit) {
            issues.push(latissue);
        }
        return;
    };
    var lngvalue = "Longitudes must be negative values for New Mexico";
    /**
     * This function checks the longitude entry for valid float value
     */
    var lngCheck = function (loc, value) {
        var submit = true;
        var lngissue = { lng: "" };
        clearIssue("lng");
        var locater = loc === 'hike' ? "For Hike Longitude: " :
            "For Cluster Group Longitude: ";
        var decimal = /^[-+]?[0-9]+\.[0-9]+$/;
        if (!decimal.test(value)) {
            alert(notfloat);
            submit = false;
            lngissue.lng = locater + "Longitude is not a decimal number";
        }
        else {
            var lng = Number(value);
            if (!isNaN(lng)) {
                if (lng > 0) {
                    alert(lngvalue);
                    submit = false;
                    lngissue.lng = locater + "Longitude must be negative";
                }
                else {
                    var testval = Math.abs(lng);
                    if (testval > 180) {
                        alert(toolarge);
                        submit = false;
                        lngissue.lng = locater + "The longitude value is too large";
                    }
                }
            }
            else {
                alert(nan);
                submit = false;
                lngissue.lng = locater + "Longitude is not a number";
            }
        }
        if (!submit) {
            issues.push(lngissue);
        }
        return;
    };
    $('#lat').on('change', function () {
        var latentry = $(this).val();
        latCheck('hike', latentry);
    });
    $('#cluslat').on('change', function () {
        var latentry = $(this).val();
        latCheck('group', latentry);
    });
    $('#lon').on('change', function () {
        var lngentry = $(this).val();
        lngCheck('hike', lngentry);
    });
    $('#cluslng').on('change', function () {
        var lngentry = $(this).val();
        lngCheck('group', lngentry);
    });
    // GPS Data Section: 
    // click-on-text length
    $('textarea[name^=click]').each(function (j) {
        var submit = true;
        var key = 'cot' + j;
        var cotissue = {};
        cotissue[key] = "";
        $(this).on('change', function () {
            clearIssue(key);
            var ta = $(this).val();
            if (ta.length > 256) {
                alert("Only 256 characters are allowed");
                submit = false;
                cotissue[key] = "Too many characters in click-text #" + (j + 1);
            }
            if (!submit) {
                issues.push(cotissue);
            }
        });
    });
    $(window).on('resize', function () {
        $('.subbtn').remove();
        linewidth = $('#main').width() - listwidth;
        $('#line').width(linewidth);
        positionApply(tabint);
        var btn = "#ap" + tabint;
        prepareSubmit(btn);
    });
});
