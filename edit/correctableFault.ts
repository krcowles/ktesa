declare var html_syms: string;
declare var $apply: JQuery.PlainObject;
/**
 * @fileoverview This script presents the user with available
 * fixes for detected faults in the corresponding gpx file(s)
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 First release
 */
$(function() {

var appMode = $('#appMode').text() as string;
var hikeNo = $('#hikeNo').text() as string;
var fault_data = $('#fdata').text();
var line_items = fault_data.split("|");
for (var j=0; j<line_items.length; j++) {
    var btn_id = "fix" + j;
    var fault_info = line_items[j].split("^");
    /**
     * [0] => saved tmpfile name
     * [1] => gpx filename
     * [2] => unsupported sym name
     * [3] => waypoint name
     * html_syms is <select> box for supported syms
     */
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
var $inpnames = $('.iname');  // saved tmpfile name
var $wptnames = $('.wname');  // waypoint name
var $faults   = $('.symbad'); // unsupported symbol
var $repls    = $('.syms') as JQuery<HTMLSelectElement>;
$('.fix').each(function(indx) {
    var btn_id = "#fix" + indx;
    $(btn_id).on('click', function() {
        var $div = $(this).parent();
        $div.css('background-color', 'darkgray');
        $(this).attr('disabled', 'disabled');
        var replacer  = $repls[indx].value;
        var ajax_input = $inpnames[indx].textContent as string;
        var ajax_wpts  = $wptnames[indx].textContent as string;
        var ajax_bsym  = $faults[indx].textContent as string;
        var ajaxdata   = {syminput: ajax_input, wptname: ajax_wpts,
            symfault: ajax_bsym, replacer: replacer};
        $.ajax({
            url: 'replaceBadSyms.php',
            method: 'post',
            data: ajaxdata,
            success: function(result: string) {
                if (result === "OK") {
                    alert("Symbol replaced");
                } else {
                    alert("Symbol not replaced: contact admin");
                }
            },
            error: function(_jqXHR, _textStatus, _errorThrown) {
                let msg = "correctableFaults.js: attempting to replace " +
                    "bad symbol via replaceBadSyms.php";
                ajaxError(appMode, _jqXHR, _textStatus, msg);
            }
        });
    });       
});
$('#finish').on('click', function() {
    var $replacements: JQuery<HTMLButtonElement>;
    $replacements = $('.fix');
    for (var k=0; k<$replacements.length; k++) {
        if ($replacements[k].disabled === false) {
            alert("Some replacements have not yet been made");
            return false;
        }
    }
    // can't use ajax, as a response is expected therefrom...
    $('<form method="post" action="saveTab1.php">' +
        '<input type="hidden" name="fsaved" value="Y" />' +
        '<input type="hidden" name="hikeNo" value="' + hikeNo + '" />' +
        '</form>').appendTo('body').trigger('submit').remove();
    return;
})

});