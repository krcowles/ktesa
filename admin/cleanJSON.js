"use strict";
/**
 * @fileoverview This script deletes marked gpx and/or json files from
 * their respective directories
 *
 * @author Ken Cowles
 * @version 1.0 First release
 */
$(function () {
    $('#del_all').on('click', function () {
        if ($(this).prop('checked')) {
            $('.ext').each(function () {
                $(this).prop('checked', true);
                $(this).next().css('color', 'blue');
            });
        }
        else {
            $('.ext').each(function () {
                $(this).prop('checked', false);
                $(this).next().css('color', 'black');
            });
        }
    });
    // Highlight individually checked items
    $('.ext').on('change', function () {
        if ($(this).is(":checked")) {
            $(this).next().css('color', 'blue');
        }
        else {
            $(this).next().css('color', 'black');
        }
    });
});
