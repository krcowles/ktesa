/**
 * @fileoverview Verify data for new pages: make sure hike title is unused
 * and if cluster page, selected group doesn't already have a page. Also
 * manage which items are displayed for chosen hike 'type'.
 * 
 * @author Ken Cowles
 * 
 * @version 2.0 First release with Cluster Page editing
 * @version 2.1 Typescripted
 */
$( function () { // DOM loaded

var error = $('#pgerror').text();
var grpnamedup = error.indexOf('Group') !== -1 ? true : false;
if (error !== 'clear' && error !== '') {
    alert("Error encountered: " + error);
}
// load lists of 'pgTitle's & 'clusters' for data validation 
var titleList: string[];
$.ajax({
    type: "POST",
    url: "getTitles.php",
    dataType: 'JSON',
    success: function(titles) {
        titleList = titles;
    },
    error: function(jqXHR) {
        var newDoc = document.open();
		newDoc.write(jqXHR.responseText);
		newDoc.close();
    }
});
/**
 * The following function collects the names of cluster groups
 */
var groups: string[];
const getClusters = (def: JQueryDeferred<void>) => {
    $.ajax({
        url: 'getGroups.php',
        method: 'get',
        dataType: 'json',
        success: function(data) {
            groups = data;
            def.resolve();
        },
        error: function(jqXHR) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
};
var initdef = $.Deferred();
getClusters(initdef);
// establish page load/refresh radio button states
$('#cluster').prop('checked', false);
$('#normal').prop('checked', true);

// Prevent submitting form when user hits 'Enter' key in input field
$('form').find('#hikename').on('keydown', function(ev) {
    let retval = true;
    if (ev.key == 'Enter') {
        retval = false;
    }
    return retval;
});

// radio button actions
$('#cluster').on('change', function() {
    if ($(this).prop('checked')) {
        $('#cls').css('display','block');
    }
});
if (grpnamedup) {
    $('#cluster').trigger('click');
}
$('#normal').on('change', function() {
    if ($(this).prop('checked')) {
        $('#cls').css('display','none');
    }
});

// styling when focus is on input
$('#hikename').on('focus', function() {
    $(this).css('background-color', 'blanchedalmond');
});
$('#hikename').trigger("focus");
$('.new').each(function() {
    $(this).on('focus', function() {
        $(this).css('background-color', 'blanchedalmond');
    });
    $(this).on('blur', function() {
        $(this).css('background-color', 'white');
    });
});

// validate user's choice for hikename
$('#hikename').on('change', function() {
    for (var i=0; i<titleList.length; i++) {
        if ($(this).val() == titleList[i]) {
            alert("This name already exists; Please try another");
            $(this).val('');
        }
    }
});

// validate user's choice for new group name
$('#newgroup').on('change', function() {
    for (let j=0; j<groups.length; j++) {
        if ($(this).val() == groups[j]) {
            alert("This name already exists; Please try another");
            $(this).val('');
        }
    }
});

/**
 * Instantiate the Cluster Page Editor instead of the Hike Page Editor:
 */ 
const submittable = (group: string) => {
    if (groups.indexOf(group) !== -1) {
        alert("This cluster group already has a page assigned\n" +
            "Please select a new group");
        return false;
    } else {
        return true;
    }
}
// get select value in drop-down
var selectVal = <string>$('#cpages').val();
$('#cpages').on('change', function() {
    selectVal = <string>$(this).val();
});

// validate user's choice for new group name
var dup = false;
$('#newclusgrp').on('change', function() {
    let newgrp = <string>$(this).val();
    if (groups.indexOf(newgrp) !== -1) {
        dup = true;
    } else {
        dup = false;
    }
});

$('#createcpg').on('click', function() {
    let returnbool = true;
    let grpinput = <string>$('#newclusgrp').val();
    let newgrp = grpinput.trim()
    if (newgrp == '') {
        let clusDef = $.Deferred();
        getClusters(clusDef); // prevent user from creating dup if returning to this page
        $.when(clusDef).then(function() {
            let retval = true;
            if (submittable(selectVal)) {
                let item = <string>selectVal;
                let choice = item.replace(/ /g, '+');
                let newpg = "submitClusterPg.php?choice=" + choice;
                window.open(newpg, "_blank");
            } else {
                retval = false;
            }
            return retval;
        }); 
    } else {
        if (dup) {
            alert("This group already exists; Please try another");
            returnbool = false;
        } else {
            let choice = newgrp.replace(/ /g, '+');
            let newpg = "submitClusterPg.php?new=y&choice=" + choice;
            window.open(newpg, "_blank");
        }
    }
    return returnbool;
});

});
