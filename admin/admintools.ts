declare var hostIs: string;
declare var server_loc: string;
declare var auth: string;
declare var nopix: string;
interface InputFiles extends EventTarget {
    files: IFile[];
}
interface IFile {
    name: string;
}
/**
 * @fileoverview This script executes all buttons on the admintools.php page
 * 
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
$( function() {  // doc ready

// If any alerts were set by scripts
let admin_alert = $('#admin_alert').text();
if (admin_alert !== '') {
    alert(admin_alert);
}
if (typeof(nopix) !== 'undefined') {
    alert(nopix);
}

var chksum_results = new bootstrap.Modal(<HTMLElement>document.getElementById('chksum_results'), {
    keyboard: false
});
/**
 * Site Modes
 */
var current_state = $('#currstate').text();
$('#switchstate').on('click', function() {
    window.open('changeSiteMode.php?mode=' + current_state);
    window.close(); // window must have been opened via javascript: see panelMenu.ts/js
});
$('#swdb').on('click', function() {
    window.open('switchDb.php');
    window.close(); // window must have been opened via javascript: see panelMenu.ts/js
});

/**
 * Upload to main site and install
 */
// uploading of git branch to server
$('#upld').on('click', function() {
    let branch = $('#ubranch').val() == '' ? 'master' : $('#ubranch').val();
    let commit = $('#ucomm').val();
    if (commit == '') {
        alert("Please specify a commit number");
        return;
    }
    let postdata = {branch: branch, commit: commit};
    let ans = confirm("Proceed to upload '" + branch + "'?");
    if (ans) {
        $('#loading').show();
        $.ajax({
            url: '../php/ftp.php',
            method: "post",
            data: postdata,
            success: function(result) {
                $('#loading').hide();
                if (result !== "\nDone") {
                    alert("Error: " + result)
                } else {
                    alert("Test site successfully uploaded via ftp");
                }
            },
            error: function(jqXHR) {
                var newDoc = document.open();
                newDoc.write(jqXHR.responseText);
                newDoc.close();
            }
        });
    }
});
// main site installation
$('#install').on('click', function() {
    if (hostIs !== 'nmhikes.com' || server_loc !== 'main') {
        alert("This tool only works on the server docroot");
        return;
    }
    if (typeof auth !== 'undefined') {
        alert("There is no authorization to run this utility");
        return;
    }
    let deletions:string[] = [];
    let deleters = <string>$('#sites').val();
    let copyloc = <string>$('#copyloc').val();
    if (copyloc == '') {
        alert("Please specify a location from which to install files");
        return;
    }
    /**
     * Check first to make sure new files currently residing on main
     * won't be deleted by writing in install files. Example: user uploads
     * a new gpx file, but it is not populated in the install site.
     */
    var proceed = false;
    $.post('installChecks.php', {site: copyloc}, function(result) {
        let diffs = result;
        let output = '';
        if (diffs[0] !== 'none') {
            if (diffs.indexOf('gpx') !== -1) {
                output += "Mismatch in gpx file count\n";
            }
            if (diffs.indexOf('json') !== -1) {
                output += "Mismatch in json file count\n";
            }
            output += "Proceed?";
            let ans = confirm(output);
            if (ans) {
                proceed = true;
            }
        } else {
            proceed = true;
        }
        if (proceed) {
            let ajax = false;
            if (deleters == '') {
                let ans = confirm("No additional test site files will be deleted");
                if (ans) {
                    ajax = true; 
                    deletions = [];  
                } else {
                    return;
                }
            } else {
                let userspec: string[] = deleters.split(",");
                for (let i=0; i<userspec.length; i++) {
                    let item = userspec[i].trim();
                    deletions.push(item);
                }
                ajax = true;
            }
            if (ajax) {
                $('#loading').show();
                let postdata = {install: copyloc, delete: deletions};
                $.ajax({
                    url: 'install.php',
                    method: "post",
                    data: postdata,
                    success: function(result) {
                        $('#loading').hide();
                        alert(result);
                    },
                    error: function(jqXHR) {
                        var newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            }
        }
        return;
    }, 'json');
});

/**
 * Routine to check if hike page links in 'References' still work...
 * This should only need to be invoked infrequently
 */
$('#lnk_test').on('click', function() {
    window.open("linkValidate.php", "_blank");
});

/**
 * Download Actions
 */
// Changes only
$('#chgs').on('click', function() {
    window.open('export_all_tables.php?dwnld=C');
});
// New pictures
$('#npix').on('click', function() {
    window.open('list_new_files.php?request=pictures', "_self");
});
// Pictures newer than
var picfile = '';
var pselLoc = <JQuery.Coordinates>$('#psel').offset();
var dselLoc = <JQuery.Coordinates>$('#dsel').offset();
var dselCoord = {top: dselLoc.top, left: pselLoc.left};
$('#dsel').offset(dselCoord);
$('#cmppic').on('change', function(ev: Event) { // input select file
    let targ = <InputFiles>ev.target;
    let filearray = targ.files;
    picfile = filearray[0].name;
});
$('#rel2pic').on('click', function() {
    picloc = '';
    var dateSelected = $('#datepicker').val();
    if (picfile === '' && dateSelected === '') {
        alert("No image or date has been selected");
    } else {
        if (picfile !== '') {
            var picloc = "pictures/zsize/" + picfile;
            $('#cmppic').val('');
            picfile = '';
        }
        $('#datepicker').val('');
        window.open("list_new_files.php?request=pictures&dtFile=" + picloc +
            "&dtTime=" + dateSelected, "_self");
    }
});

/**
 * Listings
 */
// List new files
$('#lst').on('click', function() {
    let list = $('#skipsites').text();
    if (list === '') {
        window.open("list_new_files.php?request=files", "_blank")
    } else {
        window.open("list_new_files.php?request=files&tsites=" + list, "_blank")
    }
});

/**
 * Database management tools
 */

/**
 * "Reload Database" is a special case requiring special attention to 
 * circumstances and state of the db. If the db tables have been dropped
 * separately (not a part of "Reload Database"), or because the reload
 * failed to complete after already dropping tables, then the Checksums
 * table won't be present; therefore the existence of that table is verified,
 * and if not present, the admin is notified, the deferred promise is
 * resolved, and the  the admin may choose to continue with the reload or not.
 * Also, when the  "Reload Database" is performed on the server, the extra
 * precaution is taken to save the current database before reloading (not so
 * for local machine). The function 'retrieveDwnldCookie' is related to that case.
 * 
 * NOTE: In order to prevent accidentally over-writing of new USERS or user
 * hikes-in-edit (not admin hikes) various tests are made, and results
 * presented to the admin in the form of a modal (id=chksum_results).
 * 1. The current db may have changed since the last reload
 * 2. The new db may have critical differnces that need to be communicated
 */
// -------------- reload functions ------------
function checkChecksums(deferred:JQueryDeferred<void>) {
    $.ajax({
        url: 'manageChecksums.php?action=cmp',
        method: 'get',
        dataType: 'json',
        success: function(result) {
            let obs = result.obs;
            let missing = result.missing;
            let nomatch = result.nomatch;
            let alerts = result.alerts;
            if (obs[0] === 'none' && missing[0] === 'none' && nomatch[0] === 'none'
            && alerts['newuser'] === 'no' && alerts['ehikes'] === 'no') {
                // don't display modal
                deferred.resolve();
            } else {
                setChksumModal(obs, missing, nomatch, alerts['newuser'], alerts['ehikes']);             
                deferred.resolve();
            }
        },
        error: function(jqXHR) {
            deferred.reject();
            var newDoc = document.open();
		    newDoc.write(jqXHR.responseText);
		    newDoc.close();
        }
    });
    return;
}
function setChksumModal(obs:string[], missing:string[], nomatch:string[], newuser:string, newehike:string) {
    var cklist = '';
    cklist += "<h5><em style='color:brown;'>Changes to the resident database since the " +
        "last reload</em></h5>";
    if (obs[0] !== 'none') {
        cklist += '<h5>The Checksums table no longer contains</h5><ul>';
        for (let i=0; i<obs.length; i++) {
            cklist += '<li>' + obs[i] + '</li>';
        }
        cklist += '</ul>';
    }
    if (missing[0] !== 'none') {
        cklist += '<h5>The following tables had no previous Checksums entry</h5><ul>';
        for (let j=0; j<missing.length; j++) {
            cklist += '<li>' + missing[j] + '</li>';
        }
        cklist += '</ul>';
    }
    if (nomatch[0] !== 'none') {
        cklist += '<h5>The following table checksums have changed</h5><ul>';
        for (let k=0; k<nomatch.length; k++) {
            cklist += '<li>' + nomatch[k] + '</li>';
        }
        cklist += '</ul>';
    }
    if (newuser === 'yes') {
        cklist += '<h5>The USERS table has changed</h5>';
    }
    if (newehike  === 'yes') {
        cklist += '<h5>A NEW nonadmin user has a hike in Edit</h5>';
    }
    cklist += "<h5>Above items may be regenerated or lost during reload</h5>\n<hr />";
    $('#last_load').empty();
    $('#last_load').append(cklist);
    return;
}
function checkAgainstNewDB (deferred:JQueryDeferred<void>) {
    /**
     * If the new db has a different USERS table than the resident db, 
     * alert the admin. Also, if EHIKES tables differ, alert the admin.
     * Use the test db to load the new sql file and then compare the test
     * db against the resident db
     */
    $.ajax({
        url: 'compareToSql.php',
        method: 'post',
        dataType: 'json',
        success: function(results) {
            // append messages to the modal, then display
            if (results !== '') {
                var cklist = "<h5><em style='color:brown;'>The sql file used for " +
                    "reloading differs from the resident database</em></h5>";
                let mismatch   = results.mismatch;
                let not_in_new = results.not_in_new;
                let not_in_old = results.not_in_old;
                let new_users  = results.new_users;
                let del_users  = results.del_users;
                let new_hikes  = results.new_hikes;
                let del_hikes  = results.del_hikes;
                if (mismatch[0] === 'none' && not_in_new[0] === 'none' 
                && not_in_old[0] === 'none' && new_users[0] === 'none'
                && del_users[0] === 'none' && new_hikes[0] === 'none'
                && del_hikes[0] === 'none') {
                    if ($('#last_load').children().length !== 0) {
                        chksum_results.show();
                        // Modal hidden event fired
                        $('#chksum_results').on('hidden.bs.modal', function () {
                        deferred.resolve();
                    });
                    } else {
                        deferred.resolve();
                    }  
                } else {
                    if (mismatch[0] !== 'none') {
                        cklist += '<h5>Checksums for the following tables differ</h5><ul>';
                        for (let i=0; i<mismatch.length; i++) {
                            cklist += '<li>' + mismatch[i] + '</li>';
                        }
                        cklist += '</ul>';
                    }
                    if (not_in_new[0] !== 'none') {
                        cklist += '<h5>The following tables will no longer exist</h5><ul>';
                        for (let i=0; i<not_in_new.length; i++) {
                            cklist += '<li>' + not_in_new[i] + '</li>';
                        }
                        cklist += '</ul>';
                    }
                    if (not_in_old[0] !== 'none') {
                        cklist += '<h5>The following tables will be added</h5><ul>';
                        for (let i=0; i<not_in_old.length; i++) {
                            cklist += '<li>' + not_in_old[i] + '</li>';
                        }
                        cklist += '</ul>';
                    }
                    if (new_users[0] !== 'none') {
                        cklist += '<h5>The following users will be added</h5><ul>';
                        for (let i=0; i<new_users.length; i++) {
                            cklist += '<li>' + new_users[i] + '</li>';
                        }
                        cklist += '</ul>';
                    }
                    if (del_users[0] !== 'none') {
                        cklist += '<h5>The following users will be deleted</h5><ul>';
                        for (let i=0; i<del_users.length; i++) {
                            cklist += '<li>' + del_users[i] + '</li>';
                        }
                        cklist += '</ul>';
                    }
                    if (new_hikes[0] !== 'none') {
                        cklist += '<h5>The following EHIKES will be added</h5><ul>';
                        for (let i=0; i<new_hikes.length; i++) {
                            cklist += '<li>' + new_hikes[i] + '</li>';
                        }
                        cklist += '</ul>';
                    }
                    if (del_hikes[0] !== 'none') {
                        cklist += '<h5>The following EHIKES will be deleted</h5><ul>';
                        for (let i=0; i<del_hikes.length; i++) {
                            cklist += '<li>' + del_hikes[i] + '</li>';
                        }
                        cklist += "</ul>";
                    }
                    $('#next_load').empty();
                    $('#next_load').append(cklist);
                    chksum_results.show();
                    // Modal hidden event fired
                    $('#chksum_results').on('hidden.bs.modal', function () {
                        deferred.resolve();
                    });
                }
            } else {
                deferred.reject();
            } 
        },
        error: function(jqXHR) {
            deferred.reject();
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
    return;
}
function retrieveDwnldCookie(dcname: string): string {
    var parts = <string[]>document.cookie.split(dcname + "=");
    let returnitem: string = '';
    if (parts.length == 2) {
        let lastitem =  <string>parts.pop();
        let itemarray = lastitem.split(";");   // .split(";").shift();
        returnitem = <string>itemarray.shift();
    }
    return returnitem;
}
// -------------- end reload functions ------------

// ---------------- click on reload ---------------
$('#reload').on('click', function() {
    // check for the existence of a Checksums table
    let checksumsDef = $.Deferred();
    let newdbDef     = $.Deferred();
    $.get("checksumTest.php", function(result) {
        if (result === 'no') {
            alert("No Checksum table currently exists");
            checksumsDef.resolve();
        } else {
            checkChecksums(checksumsDef);
        }
    });
    // after validating Checksums table exists (or not), check against new db (only if main)
    $.when(checksumsDef).then(function() {
        checkAgainstNewDB(newdbDef);
    });
    $.when(newdbDef).then(function() {
        if (confirm("Do you really want to drop all tables and reload them?")) {
            if (hostIs !== 'localhost') {
                window.open('export_all_tables.php?dwnld=N', "_blank");
                var dwnldResult;
                var downloadTimer = setInterval(function() {
                    dwnldResult = retrieveDwnldCookie('DownloadDisplayed');
                    if (dwnldResult === '1234') {
                        clearInterval(downloadTimer);
                        if (confirm("Proceed with reload?")) {
                            window.open('drop_all_tables.php', "_blank");
                        }
                    }
                }, 1000)
            } else {
                window.open('drop_all_tables.php', "_blank");
            }
        }
    });
});

// Drop All Tables (only - not a part of "Reload Database")
$('#drall').on('click', function() {
    var testSums = $.Deferred();
    checkChecksums(testSums);
    $.when(testSums).then(function() {
        if ($('#last_load').children().length !== 0) {
            chksum_results.show();
        }
        if (confirm("Do you really want to drop all tables?")) {
            window.open('drop_all_tables.php?no=all', "_blank");
        }
    });
});
// Load All Tables
$('#ldall').on('click', function() {
    window.open('load_all_tables.php', "_blank");
});
// Export All Tables
$('#exall').on('click', function() {
    window.open('export_all_tables.php?dwnld=N', "_blank");
});
// Check for DB Changes
$('#dbchanges').on('click', function() {
    $.get('manageChecksums.php', {action: 'cmp'}, function(result) {
        let obs = result.obs;
        let missing = result.missing;
        let nomatch = result.nomatch;
        let alerts = result.alerts;
        if (obs[0] !== 'none' || missing[0] !== 'none' || nomatch[0] !== 'none'
        || alerts['newuser'] !== 'no' || alerts['ehikes'] !== 'no') {
            setChksumModal(obs, missing, nomatch, alerts['newuser'], alerts['ehikes']);
            chksum_results.show();
        } else {
            alert("No differences found since last reload/install");
        }
    }, 'json');
});
// Generate New Checksums
$('#gensums').on('click', function() {
    $.get('manageChecksums.php', {action: 'gen'}, function() {
        alert("Checksums regenerated");
    });
});
// Show All Tables
$('#show').on('click', function()  {
    window.open('show_tables.php', "_blank_");
});

/**
 * Miscellaneous Tools
 */
// Change Edit Mode
$('#editmode').on('click', function() {
    var emode = $('#emode').text();
    $.ajax({
        url: 'siteEdit.php',
        data: {button: emode},
        dataType: "text",
        success: function(resp) {
            $('#emode').text(resp);
        },
        error: function(jqXHR) {
            var newDoc = document.open();
		    newDoc.write(jqXHR.responseText);
		    newDoc.close();
        }
    });
});
// Display commit
$('#commit').on('click', function() {
    $.ajax({
        url: 'commit_number.txt',
        dataType: 'text',
        success: function(resp) {
            alert("The current commit number\nassociated" +
                " with this site is:\n\n\t" + resp);
        },
        error: function(_jqXHR, textStatus, errorThrown) {
            var msg = "Ajax call in admintools.js line 105 has failed " +
                "with error code: " + errorThrown + "\nSystem error message: "
                + textStatus;
            alert(msg);
        }
    });
});
// Cleanup Pictures
$('#cleanPix').on('click', function() {
    window.open('cleanPix.php', "_blank");
});
// Cleanup extraneous gpx/json files
$('#gpxClean').on('click', function() {
    window.open('cleanGpxJson.php', "_blank");
});
// PHP Info
$('#pinfo').on('click', function() {
    window.open('phpInfo.php', "_blank");
});
// Add Book to BOOKS Table
$('#addbk').on('click', function() {
    window.open("addBook.php", "_blank");
});

/**
 * Hike Management
 */
// Publish a hike
$('#pub').on('click', function() {
    window.open("reldel.php?act=rel", "_blank");
});
// Delete a hike
$('#ehdel').on('click', function() {
    window.open("reldel.php?act=del","_blank");
});

/**
 * Display of visitation data
 */
 $('#vdat').on('click', function() {
    window.open("visitor_data.php", "_blank");
});

/**
 * GPX File Management
 */
// Reverse all tracks
$('#revall').on('click', function(ev) {
    ev.preventDefault();
    $('input[name=revtype]').val("gpxall");
    $('#revgpx').trigger('submit');
});
// Reverse single track
$('#revsgl').on('click', function(ev) {
    ev.preventDefault();
    $('input[name=revtype]').val("gpxsgl");
    $('#revgpx').trigger('submit');
});

});  // end of docloaded
