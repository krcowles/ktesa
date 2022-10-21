"use strict";
/**
 * @fileoverview This script deletes marked gpx and/or json files from
 * their respective directories
 *
 * @author Ken Cowles
 * @version 1.0 First release
 */
$(function () {
    $('#del_egpx').on('change', function () {
        // why does is("checked") work below but not here???
        if ($(this).prop('checked')) {
            $('.egpx').each(function () {
                $(this).prop('checked', true);
                $(this).next().css('color', 'blue');
            });
        }
        else {
            $('.egpx').each(function () {
                $(this).prop('checked', false);
                $(this).next().css('color', 'black');
            });
        }
    });
    $('#del_ejson').on('click', function () {
        if ($(this).prop('checked')) {
            $('.ejson').each(function () {
                $(this).prop('checked', true);
                $(this).next().css('color', 'blue');
            });
        }
        else {
            $('.ejson').each(function () {
                $(this).prop('checked', false);
                $(this).next().css('color', 'black');
            });
        }
    });
    $('#del_all').on('click', function () {
        if ($(this).prop('checked')) {
            $('.egpx').each(function () {
                $(this).prop('checked', true);
                $(this).next().css('color', 'blue');
            });
            $('.ejson').each(function () {
                $(this).prop('checked', true);
                $(this).next().css('color', 'blue');
            });
        }
        else {
            $('.egpx').each(function () {
                $(this).prop('checked', false);
                $(this).next().css('color', 'black');
            });
            $('.ejson').each(function () {
                $(this).prop('checked', false);
                $(this).next().css('color', 'black');
            });
            $('#del_egpx').prop('checked', false);
            $('#del_ejson').prop('checked', false);
        }
    });
    // Highlight individually checked items
    $('.egpx').on('change', function () {
        if ($(this).is(":checked")) {
            $(this).next().css('color', 'blue');
        }
        else {
            $(this).next().css('color', 'black');
        }
    });
    $('.ejson').on('click', function () {
        if ($(this).is(":checked")) {
            $(this).next().css('color', 'blue');
        }
        else {
            $(this).next().css('color', 'black');
        }
    });
});
