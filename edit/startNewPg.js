"use strict";
/**
 * @fileoverview Verify data for new pages: make sure hike title is unused
 * and if cluster page, selected group doesn't already have a page. Ensure
 * that Latin1 characters are properly deployed and eliminate any HTML
 * entity representations. Also manage which items are displayed for chosen
 * hike 'type'.
 *
 * @author Ken Cowles
 *
 * @version 3.0 Check for valid UTF-8 chars (Latin1) and replace HTML entities
 *  in titles if they are used;
 */
$(function () {
    var error = $('#pgerror').text();
    var grpnamedup = error.indexOf('Group') !== -1 ? true : false;
    if (error !== 'clear' && error !== '') {
        alert("Error encountered: " + error);
    }
    // Map the valid Latin1 charrs
    var charmap = [192, 193, 194, 195, 196, 197, 199, 200,
        201, 202, 203, 204, 205, 206, 207, 209, 210, 211, 212,
        213, 214, 217, 218, 219, 220, 224, 225, 226, 227, 228,
        229, 231, 232, 233, 234, 235, 236, 237, 238, 239, 241,
        242, 243, 244, 245, 246, 249, 250, 251, 252];
    var entitymap = ['Agrave', 'Aacute', 'Acirc', 'Atilde', 'Auml', 'Aring', 'Ccedil',
        'Egrave', 'Eacute', 'Ecirc', 'Euml', 'Igrave', 'Iacute', 'Icirc', 'Iuml', 'Ntilde',
        'Ograve', 'Oacute', 'Ocirc', 'Otilde', 'Ouml', 'Ugrave', 'Uacute', 'Ucirc', 'Uuml',
        'agrave', 'aacute', 'acirc', 'atilde', 'auml', 'aring', 'ccedil', 'egrave', 'eacute',
        'ecirc', 'euml', 'igrave', 'iacute', 'icirc', 'iuml', 'ntilde', 'ograve', 'oacute',
        'ocirc', 'otilde', 'ouml', 'ugrave', 'uacute', 'ucirc', 'uuml'];
    var not_allowed = "Unacceptable character in the name supplied\n" +
        "The string will be truncated at that point";
    var appMode = $('#appMode').text();
    // load lists of 'pgTitle's & 'clusters' for data validation 
    var titleList;
    /**
     * Oddly, a character position within a string will detect a 32-bit
     * unicode value, but if taken 'character by character' (which is
     * actually 16-bit chunks), the 32-bit code will not be seen.
     *
     * @param {string} entry
     * @returns {string}
     */
    var latin_check = function (entry) {
        var ret_string = entry;
        for (var i = 0; i < entry.length; i++) {
            var cp = entry.codePointAt(i);
            if (cp > 0xFFFF) { // 3 or 4-byte character encoding
                alert(not_allowed);
                ret_string = entry.substring(0, i);
            }
            else if (cp > 127 && charmap.indexOf(cp) === -1) {
                alert(not_allowed);
                ret_string = entry.substring(0, i);
            }
        }
        return ret_string;
    };
    /**
     * If the user has entered a special character as an HTML entity,
     * convert it to UTF-8 encoding
     */
    var entity_check = function (entry) {
        var ret_string = entry;
        if (entry.includes("&")) {
            // may be entity or entity number:
            if (entry.includes("#")) {
                var pos = entry.indexOf("#") + 1;
                var substr = entry.substring(pos);
                var end = substr.indexOf(";");
                var code = parseInt(substr.substring(0, end));
                var iso = "&#" + code + ";";
                var char_code = String.fromCharCode(code);
                ret_string = entry.replace(iso, char_code);
            }
            else {
                var pos = entry.indexOf("&") + 1;
                var substr = entry.substring(pos);
                var end = substr.indexOf(";");
                var code = substr.substring(0, end);
                var iso = "&" + code + ";";
                var map_pos = entitymap.indexOf(code);
                if (map_pos !== -1) {
                    var char_code = String.fromCharCode(charmap[map_pos]);
                    ret_string = entry.replace(iso, char_code);
                }
            }
        }
        return ret_string;
    };
    $.ajax({
        type: "POST",
        url: "getTitles.php",
        dataType: 'JSON',
        success: function (titles) {
            titleList = titles;
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
            var msg = "startNewPg.js: trying to load getTitles.php";
            ajaxError(appMode, _jqXHR, _textStatus, msg);
        }
    });
    /**
     * The following function collects the names of cluster groups
     */
    var groups;
    var getClusters = function (def) {
        $.ajax({
            url: 'getGroups.php',
            method: 'get',
            dataType: 'json',
            success: function (data) {
                groups = data;
                def.resolve();
            },
            error: function (_jqXHR, _textStatus, _errorThrown) {
                var msg = "Tring to access getGroups.php from getClusters()";
                ajaxError(appMode, _jqXHR, _textStatus, msg);
            }
        });
        return;
    };
    var initdef = $.Deferred();
    getClusters(initdef);
    // establish page load/refresh radio button states
    $('#cluster').prop('checked', false);
    $('#normal').prop('checked', true);
    // Prevent submitting form when user hits 'Enter' key in input field
    $('form').find('#hikename').on('keydown', function (ev) {
        var retval = true;
        if (ev.key == 'Enter') {
            retval = false;
        }
        return retval;
    });
    // radio button actions
    $('#cluster').on('change', function () {
        if ($(this).prop('checked')) {
            $('#cls').css('display', 'block');
        }
    });
    if (grpnamedup) {
        $('#cluster').trigger('click');
    }
    $('#normal').on('change', function () {
        if ($(this).prop('checked')) {
            $('#cls').css('display', 'none');
        }
    });
    // styling when focus is on input
    $('#hikename').on('focus', function () {
        $(this).css('background-color', 'blanchedalmond');
    });
    $('#hikename').trigger("focus");
    $('.new').each(function () {
        $(this).on('focus', function () {
            $(this).css('background-color', 'blanchedalmond');
        });
        $(this).on('blur', function () {
            $(this).css('background-color', 'white');
        });
    });
    // validate user's choice for hikename
    $('#hikename').on('change', function () {
        var userchoice = $(this).val();
        for (var i = 0; i < titleList.length; i++) {
            if (userchoice == titleList[i]) {
                alert("This name already exists; Please try another");
                $(this).val('');
            }
        }
        var str = latin_check(userchoice);
        $(this).val(str);
        var estr = entity_check(str);
        $(this).val(estr);
    });
    // validate user's choice for new group name
    $('#newgroup').on('change', function () {
        var new_group = $(this).val();
        for (var j = 0; j < groups.length; j++) {
            if (new_group == groups[j]) {
                alert("This name already exists; Please try another");
                $(this).val('');
            }
        }
        var str = latin_check(new_group);
        $(this).val(str);
        var estr = entity_check(str);
        $(this).val(estr);
    });
    $('#newclusgrp').on('change', function () {
        var new_group = $(this).val();
        for (var j = 0; j < groups.length; j++) {
            if (new_group == groups[j]) {
                alert("This name already exists; Please try another");
                $(this).val('');
            }
        }
        var str = latin_check(new_group);
        $(this).val(str);
        var estr = entity_check(str);
        $(this).val(estr);
    });
    /**
     * Instantiate the Cluster Page Editor instead of the Hike Page Editor:
     */
    var submittable = function (group) {
        if (groups.indexOf(group) !== -1) {
            alert("This cluster group already has a page assigned\n" +
                "Please select a new group");
            return false;
        }
        else {
            return true;
        }
    };
    // get select value in drop-down
    var selectVal = $('#cpages').val();
    $('#cpages').on('change', function () {
        selectVal = $(this).val();
    });
    // validate user's choice for new group name
    var dup = false;
    $('#newclusgrp').on('change', function () {
        var newgrp = $(this).val();
        if (groups.indexOf(newgrp) !== -1) {
            dup = true;
        }
        else {
            dup = false;
        }
    });
    $('#createcpg').on('click', function () {
        var returnbool = true;
        var grpinput = $('#newclusgrp').val();
        var newgrp = grpinput.trim();
        if (newgrp == '') {
            var clusDef = $.Deferred();
            getClusters(clusDef); // prevent user from creating dup if returning to this page
            $.when(clusDef).then(function () {
                var retval = true;
                if (submittable(selectVal)) {
                    var item = selectVal;
                    var choice = item.replace(/ /g, '+');
                    var newpg = "submitClusterPg.php?choice=" + choice;
                    window.open(newpg, "_blank");
                }
                else {
                    retval = false;
                }
                return retval;
            });
        }
        else {
            if (dup) {
                alert("This group already exists; Please try another");
                returnbool = false;
            }
            else {
                var choice = newgrp.replace(/ /g, '+');
                var newpg = "submitClusterPg.php?new=y&choice=" + choice;
                window.open(newpg, "_blank");
            }
        }
        return returnbool;
    });
});
