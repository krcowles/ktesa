/**
 * @fileoverview This script performs basic menu operations and page setup
 * for the landing site
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 First responsive design implementation
 * @version 1.1 Typescripted
 * @version 2.0 Rescripted due to changes in bootstrap causing menu issues
 */
var icon_space = $('#flexitem').height() as number;
var view_space = window.innerHeight;
var bene_space = $('#bennies').height() as number;
if (view_space - icon_space <= bene_space) {
    $('#bennies').hide();
}
$(function () {
    $('#membership').on('change', function() {
        var id = $(this).find("option:selected").attr("id");
        var newloc: string;
        switch(id) {
            case 'bam':
                newloc = "../accounts/unifiedLogin.php?form=reg";
                window.open(newloc, "_self");
                break;
            case 'login':
                newloc = "../accounts/unifiedLogin.php?form=log";
                window.open(newloc, "_self");
                break;
            case 'logout':
                var data = { expire: 'N' };
                $.ajax({
                    url: '../accounts/logout.php',
                    data: data,
                    success: function () {
                        location.reload();
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
    /**
     * Page links
     */
    $('#choice1').on('click', function () {
        window.open("../pages/responsiveTable.php", "_self");
    });
    $('#choice2').on('click', function () {
        window.open("../pages/mapOnly.php", "_self");
    });
    $('#choice3').on('click', function() {
        window.open("../pages/offlineMapDisplay.php?logo=no");
    });
});

