
/* Zoom detection is not provided as standard, and is browser-dependent. 
 * The methodology employed here works well only where the devicePixelRatio 
 * property actually reveals browser pixel-scaling. As an example, the method 
 * works for Firefox and Chrome, but not Safari: Safari always reports "2" 
 * for retina displays, and "1" otherwise. This ratio is otherwise used to 
 * decide whether or not to allow image sizing to take place. 
 * Where devicePixelRatio is not supported (e.g. Safari), another method is 
 * used: ratio of current window to available screen width). The latter 
 * works only for zooming out, not on zooming in.
 */
var usePixelRatio;
(function browserType() { 
    if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) !== -1 ) {
        usePixelRatio = false; }
    else if(navigator.userAgent.indexOf("Chrome") !== -1 ) {
    	usePixelRatio = true; }
    else if(navigator.userAgent.indexOf("Safari") !== -1) {
        usePixelRatio = false; }
    else if(navigator.userAgent.indexOf("Firefox") !== -1 ) {
        usePixelRatio = true; }
    else if((navigator.userAgent.indexOf("MSIE") !== -1 ) || (!!document.documentMode === true )) { //IF IE > 10 
        usePixelRatio = false; }  
    else {
        usePixelRatio = false; }
}());
var zoomMax = screen.width;
var winWidth = $(window).width();
var winrat; // ratio of window to available screen width: use when can't use devicePixelRatio
