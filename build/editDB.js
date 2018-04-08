$( function () { // when page is loaded...
    
/* Each drop-down field parameter is held in a hidden <p> element;
 * the data (text) in that hidden <p> element is the default that should 
 * appear in the drop-down box on page-load;
 * The drop-down element parameters are:
 *      - locale
 *      - cluster group name
 *      - hike type
 *      - difficult
 *      - exposure
 *      - references
*/
// Locale:
var sel = $('#locality').text();
$('#area').val(sel);
// Marker is hike type (At VC, Cluster, Other)
var mrkr = $('#mrkr').text();
/* 
 * THE FOLLOWING CODE ADDRESSES EDITS TO CLUSTER ASSIGNMENTS
 *   - Refer to the design documentation for behavior assessment
 */
var msg;
var rule = "The specified new group will be added;" + "\n" + 
	"Current Cluster assignment will be ignored" + "\n" + 
	"Uncheck box to use currently available groups";
var clusnme = $('#group').text();  // incoming cluster assgnmnt for edited hike (may be empty)
$('#ctip').val(clusnme);  // show above in the select box on page load
if ($('#greq').text() === 'YES') {
    $('#newg').attr('checked', true);
    $('#newg').val("YES");
    $('#newt').focus();
    $('#notclus').css('display','inline');
    alert("Enter your requested new group name in the highlighted text box below;\n" +
        "If you don't want a new group, uncheck the box.");
} else if (clusnme == '') {  // no incoming assignment; display info:
	$('#notclus').css('display','inline');
} else {
	$('#showdel').css('display','block');
}
/* To correctly process changes involving cluster types, the following state information
   needs to be passed to the server:
        1. Restore original assignments?  ignore
        2. Is a totally new cluster group being assigned?  (#newg) nxtg
           (Whether or not the previous type was cluster, new info will be extracted at server,
            unless "ignore" is checked to restore original state)
        3. Was a group different from the original group selected in 
            the drop-down #ctip?  (#grpchg) chgd
        4. Remove an existing cluster assignment?  (#deassign) rmclus
*/
var fieldflag = false;  // validation: make sure newt gets entered when newg is checked
// RESTORE INCOMING DEFAULTS:
$('#ignore').change(function() {
    if (this.checked) {
        $('#ctip').val(clusnme);
        if (clusnme == '') {
            $('#notclus').css('display','inline');
        }
        $('input:checkbox[name=nxtg]').attr('checked',false);
        $('#newg').val("NO");
        $('#newt').val("");
        $('#grpchg').val("NO");
        $('#deassign').val("NO");
        $('input:checkbox[name=rmclus]').attr('checked',false);
        fieldflag = false;
        window.alert("Original state restored" + "\n" + "No edits at this time to clusters");
        this.checked = false;
    }
});
// TELL SERVER TO REMOVE THE CLUSTER ASSIGNMENT AND CHANGE MARKER TO NORMAL
$('#deassign').change(function() {
    if (this.checked) {
        $('#deassign').val("YES");
    } else {
        $('#deassign').val("NO");
    }
});
// ASSIGN BRAND NEW GROUP:
$('#newg').change(function() {
    if (this.checked) {
        this.value = "YES";
        if (clusnme == '') {
                $('#notclus').css('display','none');
        }
        fieldflag = true;
        $('#grpchg').val("NO");
        window.alert(rule);
    } else {  // newg is unchecked
        this.value = "NO";
        if (clusnme == '') {
                $('#notclus').css('display','inline');
                $('#ctip').val(clusnme);
        }
        $('#newt').val("");
        fieldflag = false;				
    }
    $('#ctip').val(clusnme); // go back to original group assignment
});
// GROUP ASSIGNMENT DROP_DOWN VALUE IS CHANGED:
$('#ctip').change(function() {  // record any changes to the cluster assignment
    if ( $('#newg').val() === 'NO' ) {
        if (this.value !== clusnme) {
            /* If marker was not originally a cluster type, prepare to change to cluster group: */
            if (mrkr !== 'Cluster') {
                window.alert("Marker will be changed to cluster type");
                $('#notclus').css('display','none');
            } else {  // otherwise, let user know the existing cluster group assignment will change
                msg = "Cluster type will be changed from " + "\n" + "Original setting of: " + clusnme + "\n";
                msg += "To: " + $('#ctip').val();
                window.alert(msg);
            }
            $('#grpchg').val("YES");
        } 
    } else {  // when/if the box gets unchecked, the ctip val will be restored to original state;
        // deactivate grpchg to align with restored original cluster assignment
        $('#grpchg').val("NO");
        window.alert("Changes ignored while New Group Box is checked");
    }
});
// --------- end of cluster processing

// Hike type:
var htype = $('#ctype').text();
$('#type').val(htype);
// Difficulty:
var diffic = $('#dif').text();
$('#diff').val(diffic);
// Exposure:
var exposure = $('#expo').text();
$('#sun').val(exposure);

// References section:
// A: This code refers to existing refs (in database), not new ones...
var refCnt = parseInt($('#refcnt').text());
var refid;
var rid;
var refname;
var rit2;
var boxid;
var box;
// initialize (pre-populate) the boxes:
for (var i=0; i<refCnt; i++) {
    refid = '#rid' + i;
    rid = $(refid).text();  // get the rtype for this reference item
    r1id = '#r1' + i;
    rit1 = $(r1id).text();  // get the rit1 for this item (numeric for a book)
    r2id = '#r2' + i;
    rit2 = $(r2id).text();  // get the rit2 for this item
    refname = '#ref' + i;
    $(refname).val(rid); // pre-populate reference type drop-down
    boxid = 'ref' + i;
    if (rid === 'Book:' || rid === 'Photo Essay:') {
        indx = parseInt(rit1) - 1;
        var rsel = '#rttl' + i;                    
        $(rsel).val(rit1);
        var r2 = '#rr2' + i;
        $(r2).val(authors[indx]);
        box = document.getElementById(boxid);
        for (var u=2; u<box.options.length; u++) {
            box.options[u].disabled = true;
        }
    } else if (rid === 'Text:') {
        var trit2 = '#tr' + i;
        $(trit2).val('');
        $(trit2).attr('placeholder','THIS BOX IGNORED');
        box = document.getElementById(boxid).options[0].disabled = true;
        box = document.getElementById(boxid).options[1].disabled = true;
    } else {
        box = document.getElementById(boxid).options[0].disabled = true;
        box = document.getElementById(boxid).options[1].disabled = true;
    }
}
// B: This code refers to the new refs (if any) which can be added by the user
/*
 * This code detects when the user selects a reference type other than
 * book/photo essay and displays a different set of boxes with appropriate
 * placeholder text. 
 */
$reftags = $('select[id^="href"]');
$reftags.each( function() {
    $(this).change( function() {
        var refno = this.id;
        var elementNo = refno.substr(4,1);
        var bkid = '#bk' + elementNo;
        var nbkid = '#nbk' + elementNo;
        var box1 = '#nr1' + elementNo;
        var box2 = '#nr2' + elementNo;
        var bkbox = '#usebk' + elementNo;
        var notbk = '#notbk' + elementNo;
        if ($(this).val() === 'Book:' || $(this).val() === 'Photo Essay:') {
            $(bkid).css('display','inline');
            $(nbkid).css('display','none');
            var ttl = '#bkttl' + elementNo;
            var auth = '#bkauth' + elementNo;
            for (var n=0; n<titles.length; n++) {
                if (titles[n] === $(ttl).val()) {
                    $(auth).val(authors[n]);
                    break;
                }
            }
            $(bkbox).val('yes');
            $(notbk).val('no');
        } else if ($(this).val() !== 'Text:') {
            $(bkid).css('display','none');
            $(nbkid).css('display','inline');
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','URL');
            }
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','Clickable text');
            }
            $(bkbox).val('no');
            $(notbk).val('yes');
        } else {
            $(bkid).css('display','none');
            $(nbkid).css('display','inline');
            if ($(box1).val() === '') {
                $(box1).attr('placeholder','Enter Text Here');
            } 
            if ($(box2).val() === '') {
                $(box2).attr('placeholder','THIS BOX IGNORED');
            }
            $(bkbox).val('no');
            $(notbk).val('yes');
        }
    });
});
var $bktags = $('select[id^="bkttl"]');
$bktags.each( function() {
    $(this).val(''); // initialize to show no selection:
    $(this).on('change', function() {
        var bkid = this.id;
        bkid = bkid.substr(bkid.length-1, 1);
        var authid = '#bkauth' + bkid;
        var authindx = $(this).val() - 1;
        $(authid).val(authors[authindx]);
    });
});

var winWidth = $(document).width();
// NOTE: innerWidth provides the dimension inside the border
var bodySurplus = winWidth - $('body').innerWidth(); // Default browser margin + body border width:
if (bodySurplus < 24) {
    bodySurplus = 24;
}   // var can actually be negative on initial load if frame is smaller than body min-width
var tablist = $('#t5').offset();
var down = Math.floor(tablist.top) + $('#t5').height();
// For reasons not understood, $('#pos').width() gets the incorrect value...
var gettabprop = $('.tablist').css('width');
var px = gettabprop.indexOf('px');
var tabwidth = gettabprop.substring(0,px);
var listwidth = 5 * tabwidth;
var linewidth = winWidth - bodySurplus - listwidth - 9; // padding
//alert("lw:" + linewidth + ", dn:" + down + ", list:" + listwidth);
$('#line').width(linewidth);
$(window).resize( function() {
    winWidth = $(document).width();
    linewidth = winWidth - bodySurplus - listwidth - 9;
    $('#line').width(linewidth);
});
function highLight() {
    $('button').on('mouseover', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background-color','blanchedalmond');
            $(this).css('color','brown');
        }
    });
    $('button').on('mouseout', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background-color','honeydew');
            $(this).css('color','darkgray');
        }
    });
}
highLight();
$('button:not(#preview)').on('click', function(ev) {
    ev.preventDefault();
    var tid = this.id;
    $('button').each( function() {
        if (this.id !== tid) {
            $(this).css('background-color','honeydew');
            $(this).css('color','darkgray');
            if ($(this).hasClass('active')) {
                var old = this.id;
                old = '#tab' + old.substring(1,2);
                $(old).css('display','none');
                $(this).removeClass('active');
            }
        }
    });
    $(this).css('background-color','#DADADA');
    $(this).css('color','black');
    $(this).addClass('active');
    var newtid = '#tab' + tid.substring(1,2);
    $(newtid).css('display','block');
    highLight();
});

$('#preview').on('click', function() {
    var hno = $('#hikeNo').text();
    var prevPg = '../pages/hikePageTemplate.php?age=new&hikeIndx=' + hno;
    window.open(prevPg,"_blank");
});

var tab = $('#entry').text();
var tabon = '#t' + tab;
$(tabon).trigger('click');

$('.phurl').each( function() {
    $(this).change( function() {
        $(this).css('color','blue');
        $(this).css('font-weight','bold');
        $(this).css('border-color','black');
    });
});

$('#newalbs').on('click', function(ev) {
    var proceed = true;
    var allEmpty = true;
    var albumLinks = $('.phurl');
    var checkBoxes = $('.uplbox');
    for (var k=0; k<checkBoxes.length; k++) {      
        if (checkBoxes[k].checked) {
            if (albumLinks[k].value == '') {
                alert("You have checked an album link upload box\n" +
                    "without specifying an album");
                proceed = false;
            } else {
                allEmpty = false;
            }
        }
        var alb = albumLinks[k]; // to simplify coding the following 'if'
        if (alb.value !== '' && alb.name !== 'lnk1' && alb.name !== 'lnk2') {
            if (!checkBoxes[k].checked) {
                alert("You have supplied an album link\n" +
                    "without checking its corresponding box");
                proceed = false;
            } else {
                allEmpty = false;
            }
        }
    }
    if (!proceed || allEmpty) {
        ev.preventDefault();
        $('#tab2').css('display','block');
        $('#newalbs').on('mouseover', function() {
            $(this).css('background-color','papayawhip');
            $(this).css('color','brown');
        });
        $('#newalbs').on('mouseout', function() {
            $(this).css('background-color', 'honeydew');
            $(this).css('color', 'black');
        });
    } else {
        $('#part1').submit();
    }
});

$('#showll').on('click', function() {
    $('#lldisp').slideToggle();
    $(this).prop('checked',false);
});

});  // end of 'page (DOM) loading complete'








