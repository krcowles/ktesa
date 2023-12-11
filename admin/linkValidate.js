"use strict";
/**
 * @fileoverview This script allows processing of link validations
 *               'offline' via ajax, as it requires significant
 *               time to process the links, and the network would
 *               otherwise timeout waiting to display the finished
 *               document.
 * @author Ken Cowles
 * @version 1.0 First release
 */
var test_size = 50;
var lot_size = 1; // minimum number of scans to perform
var hikes;
var links;
var deletes = [];
var row;
/**
 * This is a recursive ajax call which will process each subset
 * of links, of size 'test_size', pausing to provide the admin
 * an opportunity to break the loop or continue checking links.
 *
 * @param {number} remaining How many subsets remain to be processed
 * @param {number} first     The next starting point
 */
var subsetScan = function (remaining, first, size) {
    var ajaxdata = { first: first, size: size };
    $.ajax({
        url: 'getBadLinks.php',
        method: 'post',
        data: ajaxdata,
        dataType: 'json',
        success: function (data) {
            remaining--;
            $('#loading').hide();
            hikes = data[0];
            links = data[1];
            if (links.length > 0) {
                for (var i = 0; i < links.length; i++) {
                    deletes.push(links[i]); // accumulate these...
                    row = "<tr><td>" + hikes[i] + "</td><td>" +
                        links[i] + "</td></tr>";
                    $('#lnk_results ').append(row);
                }
            }
            else {
                alert("No bad links discovered from " +
                    first + " to " + (first + (test_size - 1)));
            }
            first += test_size;
            if (remaining === 0) {
                alert("Link testing completed");
                $('#del_lnks').prop('disabled', false);
                return false;
            }
            var ans = confirm("Now remaining: " + remaining + " test lots\n" +
                "Continue from " + first + " to " + (first + (test_size - 1)) +
                "?\nNOTE: You will be unable to delete links until the next " +
                "test completes");
            if (ans) {
                if (remaining > 0) {
                    $('#loading').show();
                    subsetScan(remaining, first, size);
                }
            }
            else {
                $('#del_lnks').prop('disabled', false);
                return false;
            }
            return;
        },
        error: function () {
            alert("Failed to access getBadLinks.php");
        }
    });
};
// MAIN
$(function () {
    var total = "<p>Total links to test: ";
    var noOfLinks;
    $.ajax({
        url: 'getRefsLinks.php?caller=ajax',
        dataType: 'json',
        method: 'get',
        success: function (count) {
            noOfLinks = count;
            total += count + "</p>";
            $('#prelim').after(total);
        },
        error: function () {
            alert("Failed to retrieve count of links in getRefsLinks.php");
        }
    });
    $('#test').on('click', function () {
        var new_size = $('#new_size').val();
        test_size = parseInt(new_size);
        $('#loading').show();
        var first_link = $('#start').val();
        var first = parseInt(first_link);
        var lots = noOfLinks / test_size;
        lot_size = Math.floor(lots);
        if (lots - lot_size > 0) {
            lot_size++;
        }
        $('#del_lnks').prop('disabled', true);
        // prime the pump:
        subsetScan(lot_size, first, test_size);
    });
    $('#del_lnks').on('click', function () {
        var deletions = JSON.stringify(deletes);
        var ajaxdata = { links: deletions };
        $.ajax({
            url: "deleteBadLinks.php",
            method: "post",
            data: ajaxdata,
            dataType: "text",
            success: function (result) {
                if (result === 'ok') {
                    deletes = [];
                    alert("Bad links deleted");
                    $('#lnk_results tbody').empty();
                }
                else {
                    alert("Unknown error - ajax result is not ok");
                }
            },
            error: function () {
                alert("Failed to delete links: deleteBadLinks.php");
            }
        });
    });
});
