"use strict";
/// <reference types="jquery" />
/**
 * @fileoverview This script performs basic menu operations and page setup
 * for the landing site. Due to the fact that there is no mobileNavbar.php
 * and accompanying navMenu.js, this script supplies membership selections
 * independently. This script is invoked by both memeber and nonmember
 * landing sites.
 *
 * @author Ken Cowles
 *
 * @version 1.0 First responsive design implementation
 * @version 1.1 Typescripted
 * @version 2.0 Rescripted due to changes in bootstrap causing menu issues
 * @version 3.0 Rescripted for offline maps presentation
 */
$(function () {
    const CACHE_NAMES = {
        tiles: 'map_tiles',
        code: 'map_source'
    };
    // Show benefits, depending on available space...
    const logo = document.getElementById('logo');
    const logo_ht = logo.clientHeight;
    const welcome = document.getElementById('welcome');
    const welcome_ht = welcome.clientHeight;
    const opts = document.getElementById('opts');
    const opts_ht = opts.clientHeight;
    const user_ht = $('.usr_choices').height();
    const consumed_space = logo_ht + welcome_ht + opts_ht + user_ht;
    var view_space = window.innerHeight;
    var bene_space = $('#bennies').height();
    var bene_alloc = view_space - consumed_space;
    if (bene_alloc <= bene_space) {
        $('#bennies').hide();
    }
    const member = $('#cookie_state').text() === 'OK' ? true : false;
    if (member) {
        const name_store = localStorage.getItem('mapnames');
        const sw_version = parseInt($('#version').text());
        const ud_state = 'A' + sw_version;
        if (name_store === null) {
            // this is the user's first entry to the mobile site...
            localStorage.setItem('mapnames', 'none');
            localStorage.setItem('ud_resp', ud_state);
        }
        // address users already using maps but have no 'ud_resp' yet
        const ls_exists = localStorage.getItem('ud_resp') === null ? false : true;
        if (!ls_exists) {
            localStorage.setItem('ud_resp', ud_state);
        }
        const update_dialog = document.getElementById('update');
        const reload = document.getElementById('proceed');
        const stay = document.getElementById('keep');
        // Accept update
        reload.addEventListener("click", () => {
            update_dialog.close();
            localStorage.setItem('ud_resp', 'A' + sw_version);
            window.open("../tools/offlineReset.html", "_self");
        });
        // Reject update
        stay.addEventListener("click", () => {
            localStorage.setItem('ud_resp', 'R' + sw_version);
            update_dialog.close();
        });
        // Test for software upgrades
        const update_response = localStorage.getItem('ud_resp');
        const my_version = parseInt(update_response.substring(1));
        if (sw_version > my_version) {
            update_dialog.showModal();
        }
    }
    $('#membership').on('change', function () {
        var id = $(this).find("option:selected").attr("id");
        var newloc;
        switch (id) {
            case 'bam':
                newloc = "../accounts/unifiedLogin.php?form=reg";
                window.open(newloc, "_self");
                break;
            case 'login':
                newloc = "../accounts/unifiedLogin.php?form=log";
                window.open(newloc, "_self");
                break;
            case 'logout':
                if (member) {
                    var ans = confirm("Logging out will delete any saved " +
                        "offline maps. Proceed?");
                    if (ans) {
                        deleteNamedCache(CACHE_NAMES.code);
                        deleteNamedCache(CACHE_NAMES.tiles);
                        clearObjectStore();
                        localStorage.removeItem('mapnames');
                        localStorage.removeItem('ud_resp');
                    }
                }
                $.ajax({
                    url: '../accounts/logout.php?expire=N',
                    method: "get",
                    success: function () {
                        window.open("../index.html", "_self");
                        // Service worker will be uninstalled...
                    },
                    error: function () {
                        alert("Something went wrong!");
                    }
                });
                break;
            default:
                alert("This should never happen!");
        }
    });
    $(window).on('resize', function () {
        window.open("../index.html", "_self");
    });
    /**
     * Page links
     */
    $('#choice1').on('click', function () {
        window.open("../pages/responsiveTable.php", "_self");
    });
    $('#choice2').on('click', function () {
        window.open("../pages/mapOnly.php", "_self");
    });
    $('#choice3').on('click', function () {
        window.open("../pages/saveOffline.php?logo=no", "_self");
    });
    $('#choice4').on('click', function () {
        window.open("../pages/useOffline.html", "_self");
    });
    $('.blocks').on('click', function () {
        alert("Members only: sign up for a free membership!");
    });
});
