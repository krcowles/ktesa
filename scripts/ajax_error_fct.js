/**
 * @fileOverview ajax_error_fct.js - a function to output a 'Whoops' page:
 * This script defines a function which can be used by any $.ajax call to
 * PHP scripts. In 'Development' mode, when a PHP exception occurs, the site
 * standard is to invoke the 'Whoops' class. The output from any uncaught
 * exception analyzed by Whoops is a new html page containing the exception
 * details. In 'Production' mode, no special classes are used for error
 * detection, and in that case, the user supplies a custom text message with
 * appropriate detail to be presented to the user in an 'alert'.
 * 
 * @author Tom Sandberg
 * @author Ken Cowles <krcowles29@gail.com>
 * 
 * @param {html} html potential Whoops output for a php exception in 'Development'
 * @param {string} custom the message to be displayed as an alert if not Whoops
 * @returns {void}
 */
function customAlert(html, custom) {
    // is this a Whoops error?
    if (html.indexOf('Whoops') !== -1) {
        var newDoc = document.open("text/html", "replace");
		newDoc.write(html);
		newDoc.close();
    } else {
        alert("Error encountered: " + custom);
    }
}
