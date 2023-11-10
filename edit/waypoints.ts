declare var gpxLatDeg: string[];
declare var gpxLatDM: string[];
declare var gpxLatDMS: string[];
declare var gpxLngDeg: string[];
declare var gpxLngDM: string[];
declare var gpxLngDMS: string[];
declare var wLatDeg: string[];
declare var wLatDM: string[];
declare var wLatDMS: string[];
declare var wLngDeg: string[];
declare var wLngDM: string[];
declare var wLngDMS: string[];
/**
 * @fileoverview This script was separated from editDB.ts to simplify maintenance
 * and pertains only to waypoint editing on tab2.
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 Handling multiple formats for lat/lngs; first release
 */
$( function () {

// Display user-selected waypoint format
const showFractionalDegrees = () => {
    $('.show_deg').show();
    $('.show_dm').hide();
    $('.show_dms').hide();
}
const showFractionalMinutes = () => {
    $('.show_deg').hide();
    $('.show_dm').show();
    $('.show_dms').hide();
}
const showFractionalSeconds = () => {
    $('.show_deg').hide();
    $('.show_dm').hide();
    $('.show_dms').show();
}
// Integrity check
const checkForFractionalEntry = (entry: number) => {
    var result = false;
    if ((entry % 1) > 0) {
        alert("Only whole numbers are allowed in this field;\n"
            + "NOTE: Other formats will not be recalculated!");
        result = true;
    }
    return result;
}

/**
 * The following functions update all formats of waypoint displays;
 * The posted value is held in a hidden input, and siblings of that
 * input contain the textareas for displaying/editing waypoints in
 * the various formats: degrees, degrees/decimal minutes, and
 * degrees/minutes/decimal seconds.
 */
const getDegreeData = ( 
    $span: JQuery<HTMLTextAreaElement>, // the changed <span>
    format: string
) => {
    var $kids = $span.children(); // either 2 or 3 <textarea> children
    var degs  = $kids.eq(0).val() === '' ? 0 : parseFloat(<string>$kids.eq(0).val());
    var mins  = $kids.eq(1).val() === '' ? 0 : parseFloat(<string>$kids.eq(1).val());
    var val: number;
    if (format === 'dm') {
        val = Math.abs(degs) + mins/60;
    } else {
        // dms
        var secs = $kids.eq(2).val() === '' ? 0 : parseFloat(<string>$kids.eq(2).val());
        val = Math.abs(degs) + (mins + secs/60)/60;
    }
    return degs < 0 ? -1 * val : val;
}
const updatePostInput = (
    // use hidden input to store posted value
    $inp: JQuery<HTMLTextAreaElement>, 
    val: number
) => {
    $inp.val(val.toFixed(7));
    return;
}
const updateDegrees = (
    $d: JQuery<HTMLTextAreaElement>,
    val: number
) => {
    // use span holding class 'deg'
    $d.children().eq(0).val(val.toFixed(7));
    return;
}
const updateDM = (
    $dm_span: JQuery<HTMLTextAreaElement>, // span holding class 'dm'
    degrees: number
) => {
    var act = Math.abs(degrees);
    degrees = Math.trunc(degrees); // retains negative sign if present
    var mant = act - Math.abs(degrees);
    var mins = mant * 60;
    var tmin = mins.toFixed(5);
    var $els = $dm_span.children();
    $els.eq(0).val(degrees);
    $els.eq(1).val(tmin);
    return Number(tmin);  // due to rounding/math, mins can be marginally 'off'
}
const updateDMS = (
    // use span holding class 'dms'
    $dms_span: JQuery<HTMLTextAreaElement>,
    degrees: number,
    minutes: number
) => {
    var dmin = Math.floor(minutes);
    var mant = minutes - dmin
    var secs = mant * 60;
    var tsec = secs.toFixed(3);
    var $els = $dms_span.children();
    $els.eq(0).val(Math.trunc(degrees));
    $els.eq(1).val(dmin);
    $els.eq(2).val(tsec);
    return;
}
const recalculateFormats = (
    format: string,  // 'deg', 'dm', or 'dms'
    target: JQuery<HTMLTextAreaElement> // the <textarea> that changed
) => {
    var new_degrees = 0.00;
    var minutes = 0.00;
    switch(format) {
        case 'deg':
            // variables assigned primarily for readability...
            var $deg_span = target.parent();
            var $hidden_input = $deg_span.prev();
            var $dm_span  = $deg_span.next();
            var $dms_span = $deg_span.next().next();
            new_degrees = parseFloat(<string>target.val());
            updatePostInput($hidden_input, new_degrees);
            // no need to update degrees, as it is the target
            minutes = updateDM($dm_span, new_degrees);
            updateDMS($dms_span, new_degrees, minutes);
            break;
        case 'dm':
            var deg_blank = false; // blank-field boolean
            var min_blank = false; // blank-field boolean
            // variables assigned primarily for readability...
            var $dm_span = target.parent();  
            var $els = $dm_span.children(); // deg, min
            var $hidden_input = $dm_span.prev().prev();
            var $dm_deg = $dm_span.prev();
            var $dm_sec = $dm_span.next();
            var dm_input = $hidden_input.attr('name') as string;
            // if this is a new waypoint:
            if (dm_input.substring(0, 1) === 'n') {
                // id not-yet-filled fields
                deg_blank = $els.eq(0).val() === "" ? true : false;
                min_blank = $els.eq(1).val() === "" ? true : false;
            } 
            new_degrees = getDegreeData($dm_span, 'dm');
            updatePostInput($hidden_input, new_degrees);
            updateDegrees($dm_deg, new_degrees);
            minutes = updateDM($dm_span, new_degrees);
            updateDMS($dm_sec, new_degrees, minutes);
            // default blanks instead of filling w/zero
            if (deg_blank) {
                $els.eq(0).val("");
            } 
            if (min_blank) {
                $els.eq(1).val("");
            }
            break;
        case 'dms':
            var deg_blank = false; // blank field boolean
            var min_blank = false;
            var sec_blank = false;
            var $dms_span = target.parent(); // <span> regardless which of the three changed
            var $els  = $dms_span.children();
            var $hidden_input = $dms_span.prev().prev().prev();
            var $dms_deg  = $dms_span.prev().prev(); // <span>> contaning display deg
            var $dms_min  = $dms_span.prev(); // <span> containing display minutes
            var dms_input = $hidden_input.attr('name') as string;
            // if this is a new waypoint:
            if (dms_input.substring(0, 1) === 'n') {
                // id not-yet-filled fields
                deg_blank = $els.eq(0).val() === "" ? true : false;
                min_blank = $els.eq(1).val() === "" ? true : false;
                sec_blank = $els.eq(2).val() === "" ? true : false;
            }
            new_degrees = getDegreeData($dms_span, 'dms');
            updatePostInput($hidden_input, new_degrees);
            updateDegrees($dms_deg, new_degrees);
            var minutes = updateDM($dms_min, new_degrees);
            updateDMS($dms_span, new_degrees, minutes);
            // default blanks instead of filling w/zero
            if (deg_blank) {
                $els.eq(0).val("");
            }
            if (min_blank) {
                $els.eq(1).val("");
            }
            if (sec_blank) {
                $els.eq(2).val("");
            }
    }
    return;
};

/**
 * MAIN ROUTINE
 */
const non_num_entry = /[^\-\+0-9\.]/;
// Display waypoint format:
var wpt_format = $('#wpt_format').text();
if (wpt_format == "") {
    showFractionalDegrees();
} else {
    if (wpt_format === 'deg') {
        showFractionalDegrees();
    } else if (wpt_format === 'dm') {
        showFractionalMinutes();
    } else {
        showFractionalSeconds();
    }
}
// Check for presence of div's requiring initialization
var gpxwpts  = $('#gpts').length ? true : false;
var dbwpts   = $('#wpts').length ? true : false;
/**
 * Initialize states;
 * If there are gpx and/or existing (stored) database waypts
 */
if (gpxwpts) { // there are gpx wpts on page...
    var $gLatDeg = $('.glat_deg') as JQuery<HTMLSpanElement>;
    var $gLatDM  = $('.glat_dm')  as JQuery<HTMLSpanElement>;
    var $gLatDMS = $('.glat_dms') as JQuery<HTMLSpanElement>;
    var $gLngDeg = $('.glng_deg') as JQuery<HTMLSpanElement>;
    var $gLngDM  = $('.glng_dm')  as JQuery<HTMLSpanElement>;
    var $gLngDMS = $('.glng_dms') as JQuery<HTMLSpanElement>;
    gpxLatDeg.forEach(function(lat:string, i:number) {
        $($gLatDeg[i]).children().eq(0).val(lat);
        var dm = gpxLatDM[i].split("|");
        $($gLatDM[i]).children().eq(0).val(dm[0]);
        $($gLatDM[i]).children().eq(1).val(dm[1]);
        var dms = gpxLatDMS[i].split("|");
        $($gLatDMS[i]).children().eq(0).val(dms[0]);
        $($gLatDMS[i]).children().eq(1).val(dms[1]);
        $($gLatDMS[i]).children().eq(2).val(dms[2]);
    });
    gpxLngDeg.forEach(function(lng:string, i:number) {
        $($gLngDeg[i]).children().eq(0).val(lng);
        var dm = gpxLngDM[i].split("|");
        $($gLngDM[i]).children().eq(0).val(dm[0]);
        $($gLngDM[i]).children().eq(1).val(dm[1]);
        var dms = gpxLngDMS[i].split("|");
        $($gLngDMS[i]).children().eq(0).val(dms[0]);
        $($gLngDMS[i]).children().eq(1).val(dms[1]);
        $($gLngDMS[i]).children().eq(2).val(dms[2]);      
    });
}
if (dbwpts) { // there are wpts from the database on page...
    var $wLatDeg = $('.dlat_deg') as JQuery<HTMLSpanElement>;
    var $wLatDM  = $('.dlat_dm')  as JQuery<HTMLSpanElement>;
    var $wLatDMS = $('.dlat_dms') as JQuery<HTMLSpanElement>;
    var $wLngDeg = $('.dlng_deg') as JQuery<HTMLSpanElement>;
    var $wLngDM  = $('.dlng_dm')  as JQuery<HTMLSpanElement>;
    var $wLngDMS = $('.dlng_dms') as JQuery<HTMLSpanElement>;
    wLatDeg.forEach(function(lat:string, i:number) {
        $($wLatDeg[i]).children().eq(0).val(lat);
        var dm = wLatDM[i].split("|");
        $($wLatDM[i]).children().eq(0).val(dm[0]);
        $($wLatDM[i]).children().eq(1).val(dm[1]);
        var dms = wLatDMS[i].split("|");
        $($wLatDMS[i]).children().eq(0).val(dms[0]);
        $($wLatDMS[i]).children().eq(1).val(dms[1]);
        $($wLatDMS[i]).children().eq(2).val(dms[2]);
    });
    wLngDeg.forEach(function(lng:string, i:number) {
        $($wLngDeg[i]).children().eq(0).val(lng);
        var dm = wLngDM[i].split("|");
        $($wLngDM[i]).children().eq(0).val(dm[0]);
        $($wLngDM[i]).children().eq(1).val(dm[1]);
        var dms = wLngDMS[i].split("|");
        $($wLngDMS[i]).children().eq(0).val(dms[0]);
        $($wLngDMS[i]).children().eq(1).val(dms[1]);
        $($wLngDMS[i]).children().eq(2).val(dms[2]);
    });
}
/**
 * When a lat/lng value changes, in any format, all formats are recalculated for that wpt;
 * Also, fields are checked for legitimate values
 */
$(".deg, .dm, .dms").on('focusout', function() {
    var type = 'none';
    var $jqTA = $(this) as JQuery<HTMLTextAreaElement>;
    var raw_value = $jqTA.val() as string | number;
    /**
     * Multiple integrity checks are made on entry:
     * Some test cases may be missing, e.g. point too large or too small,
     * but the following should suffice for most user entry errors.
     */
    // Tests non-numeric values
    if (non_num_entry.test(raw_value as string)) {
        alert("Please enter only numbers:\nNOTE: Other formats will not be recalculated!");
        $jqTA.val("");
        return;
    }
    // Will find '-' or '.' if not at the beginning
    if (raw_value !== "") {
        if (isNaN(raw_value as number)) {
            alert("This is not a number:\nNOTE: Other formats will not be recalculated!");
            $jqTA.val("");
            return;
        }
    }
    // eliminate any '+' signs
    var interim_value = raw_value as string === "" ? "" : parseFloat(raw_value as string);
    $jqTA.val(interim_value); 
    var value = interim_value as string === "" ? 0 : interim_value as number;
    // no fractions on certain fields
    var nofract = $jqTA.hasClass('tstyle1') ? true : false;
    if (nofract) {
        if (checkForFractionalEntry(value)) {
            $jqTA.val("");
            return;
        }
    }
    // certain fields cannot be negative or have a value greater than 60
    if ($jqTA.hasClass('noneg')) {
        if (value < 0) {
            alert("This field cannot be negative:\n" +
                "NOTE: Other formats will not be recalculated");
                $jqTA.val("");
            return;
        } else if (value > 60) {
            alert("Degrees/minutes/seconds can have a max value of 60:\n" +
                +"NOTE: Other formats will not be recalculated");
            $jqTA.val("");
            return;
        }
    }
    // lng values must be negative
    if ($jqTA.hasClass('lng_neg')) {
        if (value > 0) {
            alert("Longitude values must be negative\nNOTE: Other formats " +
                "will not be recalculated");
            $jqTA.val("");
        }
        return;
    }
    // Finished integrity tests, proceed to recalculate data for all formats
    if (raw_value !== "") {
        if ($jqTA.hasClass('deg')) {
            type = 'deg';
        } else if ($jqTA.hasClass('dm')) {
            type = 'dm';
        } else {
            type = 'dms';
        }
        recalculateFormats(type, $jqTA);
    }   
    return;
});

$('#wptstyle').on('change', function() {
    var format = $(this).val() as string;
    switch (format) {
        case "deg":
            showFractionalDegrees();
            break;
        case "dm":
            showFractionalMinutes();
            break;
        case "dms":
            showFractionalSeconds();
    }
});
// Waypoint icons when present in the gpx file:
var $gicons = $('[id^="gicn"]');
var $gbox   = $('[id^="gselicon"]');
$gbox.each(function(indx) {
    if ($gicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($gicons[indx].innerText);
    }
});
// Waypoint icons when present in the database
var $wicons = $('[id^="dicn"]');
var $wbox   = $('[id^="dselicon"]');
$wbox.each(function(indx) {
    if ($wicons[indx].innerText == '') {
        $(this).val('googlemini');
    } else {
        $(this).val($wicons[indx].innerText);
    }
});
$('#wpteds textarea').addClass('wpticonshift');

});