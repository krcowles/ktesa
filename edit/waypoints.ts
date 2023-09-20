declare var gpxLatDeg: string[];
declare var gpxLngDeg: string[];
declare var gpxLatDM: string[];
declare var gpxLatDMS: string[];
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
 * @version 1.0 Handling multiple formats for lat/lngs
 */
$( function () {

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
    $span: JQuery<HTMLTextAreaElement>,
    format: string
) => {
    var $kids = $span.children();
    var degs  = parseFloat(<string>$kids.eq(0).val());
    var mins  = parseFloat(<string>$kids.eq(1).val());
    var val: number;
    if (format === 'dm') {
        val = Math.abs(degs) + mins/60;
    } else {
        // dms
        var secs = parseFloat(<string>$kids.eq(2).val());
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
    // use span holding class 'dm'
    $dm_span: JQuery<HTMLTextAreaElement>,
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
    return mins;
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
            new_degrees = parseFloat(<string>target.val());
            updatePostInput(target.parent().prev(), new_degrees);
            // no need to update degrees, as it is the target
            minutes = updateDM(target.parent().next(), new_degrees);
            updateDMS(target.parent().next().next(), new_degrees, minutes);
            break;
        case 'dm':
            var $parent = target.parent();  // <span> regardless which of the two changed
            var $els = $parent.children();
            var dm_input = $parent.prev().prev().attr('name') as string;
            // if this is a new waypoint, the first char of the input name is 'n'
            if (dm_input.substring(0, 1) === 'n') {
                /**
                 * For new entries, don't calculate until all fields are specified
                 */
                if ($els.eq(0).val() !== "" && $els.eq(1).val() !== "") {
                    new_degrees = getDegreeData($parent, 'dm');
                    updatePostInput($parent.prev().prev(), new_degrees);
                    updateDegrees($parent.prev(), new_degrees)
                    minutes = updateDM($parent, new_degrees);
                    updateDMS($parent.next(), new_degrees, minutes);
                } else {
                    alert("Waypt not completely specified yet: other formats not calculated")
                }
            }  else {
                // use new data in target to update all formats
                new_degrees = getDegreeData($parent, 'dm');
                updatePostInput($parent.prev().prev(), new_degrees);
                updateDegrees($parent.prev(), new_degrees);
                minutes = updateDM($parent, new_degrees);
                updateDMS($parent.next(), new_degrees, minutes);
            }     
            break;
        case 'dms':
            var $dms_parent = target.parent(); // <span> regardless which of the three changed
            var $dms_els = $dms_parent.children();
            var dms_input = $dms_parent.prev().prev().prev().attr('name') as string;
            // if this is a new waypoint, the first char of the input name is 'n'
            if (dms_input.substring(0, 1) === 'n') {
                /**
                 * For new entries, don't calculate until all fields are specified
                 */
                if ($dms_els.eq(0).val() !== "" && $dms_els.eq(1).val() !== ""
                && $dms_els.eq(2).val() !== ""
                ) {
                    new_degrees = getDegreeData($dms_parent, 'dms');
                    updatePostInput($dms_parent.prev().prev().prev(), new_degrees);
                    updateDegrees($dms_parent.prev().prev(), new_degrees);
                    minutes = updateDM($dms_parent.prev(), new_degrees);
                    updateDMS($dms_parent, new_degrees, minutes);        
                }  else {
                    alert("Waypt not completely specified yet: other formats not calculated")
                }
            } else {
                // use new data in target to update all formats
                var new_degrees = getDegreeData($dms_parent, 'dms');
                updatePostInput($dms_parent.prev().prev().prev(), new_degrees);
                updateDegrees($dms_parent.prev().prev(), new_degrees);
                var minutes = updateDM($dms_parent.prev(), new_degrees);
                updateDMS($dms_parent, new_degrees, minutes);
            }
    }
    return;
};

/**
 * MAIN ROUTINE
 */
const non_num_entry = /[^\-\+0-9\.]/;

// Display default waypoint format:
$('.show_deg').show();
$('.show_dm').hide();
$('.show_dms').hide();
// Check for presence of div's requiring initialization
var gpxwpts  = $('#gpts').length ? true : false;
var dbwpts   = $('#wpts').length ? true : false;
/**
 * Initialize states;
 * Only the gpx waypts and existing (stored) database waypts need be initialized
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
$('.deg, .dm, .dms').on('change', function() {
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
        return false;
    }
    // Will find '-' or '.' if not at the beginning
    if (raw_value !== "") {
        if (isNaN(raw_value as number)) {
            alert("This is not a number:\nNOTE: Other formats will not be recalculated!");
            $jqTA.val("");
            return false;
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
            return false;
        }
    }
    // certain fields cannot be negative or have a value greater than 60
    if ($jqTA.hasClass('tstyle5') || ($jqTA.hasClass('tstyle1') && $jqTA.hasClass('noneg'))) {
        if (value < 0) {
            alert("This field cannot be negative:\n" +
                "NOTE: Other formats will not be recalculated");
                $jqTA.val("");
            return false;
        } else if (value > 60) {
            alert("Minutes/degrees can have a max value of 60:\n" +
                +"NOTE: Other formats will not be recalculated");
            $jqTA.val("");
            return false;
        }
    }
    // Finished integrity tests, proceed to recalculate data for all formats
    
    if ($jqTA.hasClass('deg')) {
        type = 'deg';
    } else if ($jqTA.hasClass('dm')) {
        type = 'dm';
    } else {
        type = 'dms';
    }
    recalculateFormats(type, $jqTA);
    return;
});

$('#wptstyle').on('change', function() {
    var format = $(this).val() as string;
    switch (format) {
        case "deg":
            $('.show_deg').show(); // defaults
            $('.show_dm').hide();
            $('.show_dms').hide();
            break;
        case "dm":
            $('.show_deg').hide(); // defaults
            $('.show_dm').show();
            $('.show_dms').hide();
            break;
        case "dms":
            $('.show_deg').hide(); // defaults
            $('.show_dm').hide();
            $('.show_dms').show();
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