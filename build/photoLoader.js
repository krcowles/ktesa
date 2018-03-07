$(function () { // when page is loaded...
// imported vars from php: cnt (int) pgLinks (json array) albTypes (json array)
var alinks = JSON.stringify(pgLinks);
var atypes = JSON.stringify(albTypes);
var ajaxdata = { 'cnt': cnt, 'albs': alinks, 'types': atypes };
$.ajax({
    type: 'POST',
    url: 'getPicDat.php',
    dataType: 'text',
    data: ajaxdata,
    success: function(result) {
        // result is an array of objects in string form
        picdata = JSON.parse(result);
        var pichtml = '';
        for (var i=0; i<picdata.length; i++) {
            var photo = picdata[i];
            phTitles[i] = photo.pic;
            // scale width to row height:
            var aspect = photo.pWd/photo.pHt;
            var dispWidth = Math.floor(220 * aspect);
            pichtml += '<div style="width:' + dispWidth + 'px;margin-left:2px;' +
                'margin-right:2px;display:inline-block">' + 
                '<input class="ckbox" type="checkbox" name="incl[]"' +
                'value="' + phTitles[i] + '" />&nbsp;&nbsp;Add it' +
                '<img class="allPhotos" height="220px" ' +
                'width="' + dispWidth + 'px" src="' + photo.nsize +
                '" alt="' + photo.pic + '" /><br /></div>';
        }
        $('#loader').css('display','none');
        $('#main').css('display','block');
        // Create the document section containing the photos and scripts
        var picDiv = document.createDocumentFragment();
        var photos = document.createElement("DIV");
        photos.innerHTML = pichtml;
        picDiv.appendChild(photos);
        var photoSelect = document.createElement("SCRIPT");
        photoSelect.src = "newPhotos.js";
        var photoCaptions = document.createElement("SCRIPT");
        photoCaptions.src = "../scripts/picPops.js";
        picDiv.appendChild(photoSelect);
        picDiv.appendChild(photoCaptions);
        document.getElementById("main").appendChild(picDiv); 
    },
    error: function(jq, errmsg, stat) {
        var emsg = "Failed to execute getPicDat.php: " + errmsg + "; " + stat;
        alert(emsg);
    }
});

});