"use strict";
/**
 * @fileoverview This script presents the user with available
 * fixes for detected faults in the corresponding gpx file(s)
 *
 * @author Ken Cowles
 *
 * @version 1.0 First release
 */
$(function () {
    var hikeNo = $('#hikeNo').text();
    var fault_data = $('#fdata').text();
    var line_items = fault_data.split("|");
    for (var j = 0; j < line_items.length; j++) {
        var btn_id = "fix" + j;
        var fault_info = line_items[j].split("^");
        var $div_item = $("<div class='fixwpt'><p class='sfault'>In file <span class='symfile'>" +
            fault_info[1] + "</span>, for waypoint: <span class='wname'>" +
            fault_info[3] + "</span><br />Replace symbol: '<span class='symbad'>" + fault_info[2] +
            "</span>' with: " + html_syms + "<br /></p>" +
            "<p class='iname' style='display:none;'>" + fault_info[0] + "</p>");
        var $btn = $apply.clone();
        $btn.attr('id', btn_id);
        $div_item.append($btn);
        $div_item.append("</div><br />");
        $('#corrections').append($div_item);
    }
    var $inpnames = $('.iname');
    var $wptnames = $('.wname');
    var $faults = $('.symbad');
    var $repls = $('.syms');
    $('.fix').each(function (indx) {
        var btn_id = "#fix" + indx;
        $(btn_id).on('click', function () {
            var $div = $(this).parent();
            $div.css('background-color', 'darkgray');
            $(this).attr('disabled', 'disabled');
            var replacer = $repls[indx].value;
            var ajax_input = $inpnames[indx].textContent;
            var ajax_wpts = $wptnames[indx].textContent;
            var ajax_bsym = $faults[indx].textContent;
            var ajaxdata = { syminput: ajax_input, wptname: ajax_wpts,
                symfault: ajax_bsym, replacer: replacer };
            $.ajax({
                url: 'replaceBadSyms.php',
                method: 'post',
                data: ajaxdata,
                success: function (result) {
                    if (result === "OK") {
                        alert("Symbol replaced");
                    }
                    else {
                        alert("Symbol not replaced: contact admin");
                    }
                },
                error: function (_jqXHR) {
                    alert("Script not executed: " + _jqXHR.responseText);
                }
            });
        });
    });
    $('#finish').on('click', function () {
        var $replacements;
        $replacements = $('.fix');
        for (var k = 0; k < $replacements.length; k++) {
            if ($replacements[k].disabled === false) {
                alert("Some replacements have not yet been made");
                return false;
            }
        }
        // can't use ajax as a response is expected therefrom...
        $('<form method="post" action="saveTab1.php">' +
            '<input type="hidden" name="fsaved" value="Y" />' +
            '<input type="hidden" name="hikeNo" value="' + hikeNo + '" />' +
            '</form>').appendTo('body').trigger('submit').remove();
        return;
    });
});
