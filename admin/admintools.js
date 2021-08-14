"use strict";
/**
 * @fileoverview This script executes all buttons on the admintools.php page
 *
 * @author Ken Cowles
 * @version 2.0 Typescripted
 */
$(function () {
    // If any alerts were set by scripts
    var admin_alert = $('#admin_alert').text();
    if (admin_alert !== '') {
        alert(admin_alert);
    }
    if (typeof (nopix) !== 'undefined') {
        alert(nopix);
    }
    /**
     * Site Modes
     */
    var current_state = $('#currstate').text();
    $('#switchstate').on('click', function () {
        window.open('changeSiteMode.php?mode=' + current_state);
        window.close();
    });
    $('#swdb').on('click', function () {
        window.open('switchDb.php');
        window.close();
    });
    /**
     * Upload to main site and install
     */
    // uploading of git branch to server
    $('#upld').on('click', function () {
        var branch = $('#ubranch').val() == '' ? 'master' : $('#ubranch').val();
        var commit = $('#ucomm').val();
        if (commit == '') {
            alert("Please specify a commit number");
            return;
        }
        var postdata = { branch: branch, commit: commit };
        var ans = confirm("Proceed to upload '" + branch + "'?");
        if (ans) {
            $('#loading').show();
            $.ajax({
                url: '../php/ftp.php',
                method: "post",
                data: postdata,
                success: function (result) {
                    $('#loading').hide();
                    if (result !== "\nDone") {
                        alert("Error: " + result);
                    }
                    else {
                        alert("Test site successfully uploaded via ftp");
                    }
                },
                error: function (jqXHR) {
                    var newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
        }
    });
    // main site installation
    $('#install').on('click', function () {
        if (hostIs !== 'nmhikes.com' || server_loc !== 'main') {
            alert("This tool only works on the server docroot");
            return;
        }
        if (typeof auth !== 'undefined') {
            alert("There is no authorization to run this utility");
            return;
        }
        var deletions = [];
        var deleters = $('#sites').val();
        var copyloc = $('#copyloc').val();
        if (copyloc == '') {
            alert("Please specify a location from which to install files");
            return;
        }
        var ajax = false;
        if (deleters == '') {
            var ans = confirm("No additional test site files will be deleted");
            if (ans) {
                ajax = true;
                deletions = [];
            }
            else {
                return;
            }
        }
        else {
            var userspec = deleters.split(",");
            for (var i = 0; i < userspec.length; i++) {
                var item = userspec[i].trim();
                deletions.push(item);
            }
            ajax = true;
        }
        if (ajax) {
            $('#loading').show();
            var postdata = { install: copyloc, delete: deletions };
            $.ajax({
                url: 'install.php',
                method: "post",
                data: postdata,
                success: function (result) {
                    $('#loading').hide();
                    alert(result);
                },
                error: function (jqXHR) {
                    var newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
        }
        return;
    });
    /**
     * Download Actions
     */
    // Changes only
    $('#chgs').on('click', function () {
        window.open('export_all_tables.php?dwnld=C');
    });
    // New pictures
    $('#npix').on('click', function () {
        window.open('list_new_files.php?request=pictures', "_self");
    });
    // Pictures newer than
    var picfile = '';
    var pselLoc = $('#psel').offset();
    var dselLoc = $('#dsel').offset();
    var dselCoord = { top: dselLoc.top, left: pselLoc.left };
    $('#dsel').offset(dselCoord);
    $('#cmppic').on('change', function (ev) {
        var targ = ev.target;
        var filearray = targ.files;
        picfile = filearray[0].name;
    });
    $('#rel2pic').on('click', function () {
        picloc = '';
        var dateSelected = $('#datepicker').val();
        if (picfile === '' && dateSelected === '') {
            alert("No image or date has been selected");
        }
        else {
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
    $('#lst').on('click', function () {
        window.open("list_new_files.php?request=files", "_blank");
    });
    /**
     * Databse management tools
     */
    // Reload Database
    function retrieveDwnldCookie(dcname) {
        var parts = document.cookie.split(dcname + "=");
        var returnitem = '';
        if (parts.length == 2) {
            var lastitem = parts.pop();
            var itemarray = lastitem.split(";"); // .split(";").shift();
            returnitem = itemarray.shift();
        }
        return returnitem;
    }
    function checkChecksums(deferred) {
        $.ajax({
            url: 'manageChecksums.php?act=ajax',
            method: 'get',
            dataType: 'text',
            success: function (result) {
                if (result !== '') {
                    var mismatches = result.split("|");
                    var output = "Note: the following table items have changed checksums\n";
                    for (var i = 0; i < mismatches.length; i++) {
                        output += mismatches[i] + "\n";
                    }
                    alert(output);
                    deferred.resolve();
                }
                else {
                    deferred.resolve();
                }
            },
            error: function (jqXHR) {
                var newDoc = document.open();
                newDoc.write(jqXHR.responseText);
                newDoc.close();
            }
        });
    }
    $('#reload').on('click', function () {
        // first look for db changes of importance:
        var checksumsDef = $.Deferred();
        checkChecksums(checksumsDef);
        $.when(checksumsDef).then(function () {
            if (confirm("Do you really want to drop all tables and reload them?")) {
                if (hostIs !== 'localhost') {
                    window.open('export_all_tables.php?dwnld=N', "_blank");
                    var dwnldResult;
                    var downloadTimer = setInterval(function () {
                        dwnldResult = retrieveDwnldCookie('DownloadDisplayed');
                        if (dwnldResult === '1234') {
                            clearInterval(downloadTimer);
                            if (confirm("Proceed with reload?")) {
                                window.open('drop_all_tables.php', "_blank");
                            }
                        }
                    }, 1000);
                }
                else {
                    window.open('drop_all_tables.php', "_blank");
                }
            }
        });
    });
    // Drop All Tables
    $('#drall').on('click', function () {
        var testSums = $.Deferred();
        checkChecksums(testSums);
        $.when(testSums).then(function () {
            if (confirm("Do you really want to drop all tables?")) {
                window.open('drop_all_tables.php?no=all', "_blank");
            }
        });
    });
    // Load All Tables
    $('#ldall').on('click', function () {
        window.open('load_all_tables.php', "_blank");
    });
    // Export All Tables
    $('#exall').on('click', function () {
        window.open('export_all_tables.php?dwnld=N', "_blank");
    });
    // Check for DB Changes
    $('#dbchanges').on('click', function () {
        window.open('manageChecksums.php?act=exam');
    });
    // Generate New Checksums
    $('#gensums').on('click', function () {
        window.open('manageChecksums.php?act=updte');
    });
    // Show All Tables
    $('#show').on('click', function () {
        window.open('show_tables.php', "_blank_");
    });
    /**
     * Miscellaneous Tools
     */
    // Change Edit Mode
    $('#editmode').on('click', function () {
        var emode = $('#emode').text();
        $.ajax({
            url: 'siteEdit.php',
            data: { button: emode },
            dataType: "text",
            success: function (resp) {
                $('#emode').text(resp);
            },
            error: function (jqXHR) {
                var newDoc = document.open();
                newDoc.write(jqXHR.responseText);
                newDoc.close();
            }
        });
    });
    // Display commit
    $('#commit').on('click', function () {
        $.ajax({
            url: 'commit_number.txt',
            dataType: 'text',
            success: function (resp) {
                alert("The current commit number\nassociated" +
                    " with this site is:\n\n\t" + resp);
            },
            error: function (_jqXHR, textStatus, errorThrown) {
                var msg = "Ajax call in admintools.js line 105 has failed " +
                    "with error code: " + errorThrown + "\nSystem error message: "
                    + textStatus;
                alert(msg);
            }
        });
    });
    // Cleanup Pictures
    $('#cleanPix').on('click', function () {
        window.open('cleanPix.php', "_blank");
    });
    // PHP Info
    $('#pinfo').on('click', function () {
        window.open('phpInfo.php', "_blank");
    });
    // Add Book to BOOKS Table
    $('#addbk').on('click', function () {
        window.open("addBook.php", "_blank");
    });
    /**
     * Hike Management
     */
    // Publish a hike
    $('#pub').on('click', function () {
        window.open("reldel.php?act=rel", "_blank");
    });
    // Delete a hike
    $('#ehdel').on('click', function () {
        window.open("reldel.php?act=del", "_blank");
    });
    /**
     * GPX File Management
     */
    // Reverse all tracks
    $('#revall').on('click', function (ev) {
        ev.preventDefault();
        $('input[name=revtype]').val("gpxall");
        $('#revgpx').trigger('submit');
    });
    // Reverse single track
    $('#revsgl').on('click', function (ev) {
        ev.preventDefault();
        $('input[name=revtype]').val("gpxsgl");
        $('#revgpx').trigger('submit');
    });
}); // end of docloaded
