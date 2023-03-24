"use strict";
/// <reference path="./heic2any.d.ts" />
var preview_ht = 80;
var $anchor = $('#anchor');
var droppedFiles = false;
// test browser's feature support
var isAdvancedUpload = 'FormData' in window && 'FileReader' in window;
var $div = $('#heic_upld');
var image;
var reader;
var base64data;
var previews = document.getElementById('previews');
$('#preload').css({
    top: '360px',
    left: '360px',
    position: 'fixed'
});
var heic_cnt = 0;
var img_id = 0;
/**
 * For drag and drop files
 */
if (isAdvancedUpload) {
    $div.on('drag dragstart dragend dragover dragenter dragleave drop', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
    })
        .on('dragover dragenter', function () {
        $div.addClass('is-dragover');
    })
        .on('dragleave dragend drop', function () {
        $div.removeClass('is-dragover');
    })
        .on('drop', function (e) {
        $('#ldg').css('display', 'inline');
        var dfs = e.originalEvent;
        var dxfr = dfs.dataTransfer;
        droppedFiles = dxfr.files;
        heic_cnt += droppedFiles.length;
        convertHeicToJpg(droppedFiles);
    });
}
else {
    alert("Dropping of images not supported for this browser.");
}
/**
 * For input files using input browse function
 */
$('#file').on('change', function () {
    var file_input = this;
    var heics = file_input.files;
    heic_cnt += heics.length;
    if (heics.length === 0) {
        alert("No files selected");
        return false;
    }
    else {
        convertHeicToJpg(heics);
        return;
    }
});
// The conversion, previews, and uploads
function convertHeicToJpg(input) {
    $('#preload').css('display', 'block');
    for (var j = 0; j < input.length; j++) {
        var file = input[j];
        var fname = file.name;
        var mime = file.type;
        console.log("Mime for " + fname + ": " + mime);
        if (!mime.startsWith("image/")) {
            alert("Non-image file encountered: " + fname);
            heic_cnt--;
            continue;
        }
        if (fname.length > 1024) {
            alert("Please rename this file such that the name is\n" +
                "less than 1024 characters (including file extension\n" +
                "This file will not be displayed...");
            heic_cnt--;
            continue;
        }
        // test the file extension - only heic files allowed at this time
        var lastdot = fname.lastIndexOf('.');
        if (lastdot !== -1) {
            var ext = fname.slice(lastdot + 1);
            var newname = fname.slice(0, lastdot) + ".jpg";
            if (ext.toLowerCase() === 'heic') {
                var blob = file;
                heic2any({
                    blob: blob,
                    toType: "image/jpeg"
                })
                    .then(function (resultBlob) {
                    if (!resultBlob.type.startsWith("image/")) {
                        alert("Not an image file");
                        return false;
                    }
                    var url = URL.createObjectURL(resultBlob);
                    var img = document.createElement("img");
                    img.classList.add("converted");
                    img.height = preview_ht;
                    img.onload = function () {
                        //URL.revokeObjectURL(img.src);      
                    };
                    img.src = url;
                    var picdiv = document.createElement('div');
                    var info = document.createElement("span");
                    info.classList.add('newnames');
                    var dwnldr = $anchor.clone();
                    var a = dwnldr[0];
                    a.id = 'img' + img_id++;
                    //a.classList.add('dld');
                    a.href = url;
                    a.text = "Download: " + newname;
                    a.style.display = 'inline-block';
                    a.download = newname;
                    info.appendChild(a);
                    picdiv.appendChild(img);
                    picdiv.appendChild(info);
                    previews.appendChild(picdiv);
                    return;
                })["catch"](function (x) {
                    console.log(x.code);
                    console.log(x.message);
                });
            }
            else {
                alert("This does not appear to be an heic file");
                heic_cnt--;
                continue;
            }
        }
        else {
            alert("No file extension found");
            heic_cnt--;
            continue;
        }
    }
    var ld_check = setInterval(function () {
        var $items = $('.converted').length;
        if ($items === heic_cnt) {
            $('#preload').css('display', 'none');
            for (var k = 0; k < $items; k++) {
                var aid = '#img' + k;
                if (!$(aid).hasClass('created')) {
                    $(aid).addClass('created');
                    $(aid).on('click', function () {
                        alert("Downloaded...");
                        $(this).parent().parent().remove();
                        heic_cnt--;
                    });
                }
            }
            clearInterval(ld_check);
        }
    }, 400);
    return;
}
