var mapdiv = document.getElementById('gmap_div');
var bodwidth = window.innerWidth;
var bodheight = window.innerHeight;
var newwidth = parseInt(bodwidth * .74);
var newheight = parseInt(bodheight * .25);
newwidth += 'px';
mapdiv.style.width = newwidth;
window.alert("IN");
$(window).resize( function() {
    var getmap = document.getElementById('gmap_div');
    bodwidth = window.innerWidth;
    newwidth = parseInt(bodwidth * .74);
    getmap.style.width = newwidth;
});

