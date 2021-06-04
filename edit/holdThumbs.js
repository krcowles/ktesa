/**
 * @fileoverview This script is used in the editor to allow users to crop and
 * size photos for the preview & thumbs directories, or alternately, to upload
 * a pre-sized preview image of 300x225px. The images are used on the home
 * page in the side table.
 * 
 * @author Ken Cowles
 * @version 1.0 First pass
 */
var $sizeblock = $('#sizeblock').detach();

const cboxht = 500; // upload area for cropbox 'repl' (height & width)
const cropWd = 300; // crop box frame width
const cropHt = 225; // crop box frame height
const sboxht = 225; // presized image height
const xOrg   = 20;  // default starting coords for crop box frame
const yOrg   = 20;
const thWd   = 83;  // thumbnail dimensions
const thHt   = 62;
const blockSize = 10; // size of crop frame 'grabber' in uppper-left corner
const epsilon = 5;  // nominal tolerance for presized image upload
// globals
var width;  // image width
var height; // image height
var xStart = xOrg;  // upper left corner location of crop frame 
var yStart = yOrg;
var xCropOrg; // current location for cropping when 'Apply' is clicked
var yCropOrg;
var ctx; // canvas context
var indxNo = $('#hikeNo').text();
var posted = false;

/**
 * The following code needs to be re-invoked after clearing the 'boxes' div
 * in order for dragNdrop or file select to continue to work. Hence it was
 * encased in a function;
 */
function photoEvents() {
    var $copybtn = $('#selthmb');
    var $sizebtn = $('#selpre');
    var $copybox = $('#repl');
    var $sizebox = $('#presize');
    var orgBoxesState = $('#boxes').clone(false);
    // Note: 'validated' array in ktesaUplader.js get updated and needs to be reset below
    // Drag N Drop
    $copybox.on('drag dragstart dragend dragover dragenter dragleave drop', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
    })
        .on('dragover dragenter', function() {
            $(this).addClass('entered');
    })
        .on('dragleave dragend drop', function () {
            $(this).removeClass('entered');
    })
        .on('drop', function(e) {
            var dfs = e.originalEvent;
            var dxfr = dfs.dataTransfer;
            droppedFile = dxfr.files;
            loadImage(droppedFile);
    });
    $sizebox.on('drag dragstart dragend dragover dragenter dragleave drop', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
    })
        .on('dragover dragenter', function() {
            $(this).addClass('entered');
    })
        .on('dragleave dragend drop', function () {
            $(this).removeClass('entered');
    })
        .on('drop', function(e) {
            var dfs = e.originalEvent;
            var dxfr = dfs.dataTransfer;
            droppedFile = dxfr.files;
            $.when(filechecks(droppedFile)).then(function() {
                loadPreview(droppedFile[0]);
                validated = [];
            });
    });
    // File select buttons
    $copybtn.on('change', function () {
        var file_input = this;
        loadImage(file_input.files);
    });
    $sizebtn.on('change', function() {
        var file_input = this;
        loadPreview(file_input.files[0]);
    });
    // Reset button to restore 'thumbpic' div to original state
    $(document).off('click', '#reset').on('click', '#reset', function(ev) {
        ev.preventDefault();
        if (!posted) {
            $('#boxes').replaceWith(orgBoxesState);
            photoEvents();
        } else {
            alert("Image has already been posted; you cannot reset here\n" +
                "To start over, hit the 'Apply' button and delete the preview");
        }
    });
}
photoEvents();
/**
 * This function will replace the 'Pre-size' box with a loaded presized image 
 */
 function loadPreview(img) {
    var reader = new FileReader();
    reader.onload = function(e) {
        $sizeblock.css('display', 'block');
        $('#presize').replaceWith($sizeblock);
        let image = document.getElementById('ps');
        image.onload = function() {
            let xdim = this.width;
            let ydim = this.height;
            if (xdim > (cropWd + epsilon) || xdim < (cropWd - epsilon) ||
                ydim > (cropHt + epsilon) || ydim < (cropHt - epsilon)) {
                    alert("This image is not properly sized to 300 x 225 pixels\n" +
                        "Please click on 'Reset' and reload a sized image, or\n" +
                        "if the image fits the bounds, use 'as is' shown");
            }
        }
        image.src = e.target.result;
        image.height = sboxht;
        // remove Crop Box and replace with Restart button and text
        var html = '<div id="cprestart"><div><button id="reset">Reset Image</button>';
        html += '</div><div><span>Clear this image to reset the options;<br />';
        html += 'Or when ready, click "Apply"</span></div></div>';
        $('#repl').replaceWith(html);
        $('#uccrop').text("0");
        $('#ucprev').text("1");
    }
    reader.readAsDataURL(img);
 }
/**
 * This function will handle verifying and loading the selected image;
 * Final disposition depends on the caller
 */
function loadImage(img) {
    var inputImage = new Image();
    $.when(filechecks(img)).then(function() {
        $.when(ldImgs(validated)).then(function() {
            inputImage.src = FR_Images[0]['data'];  // FileReader data
            inputImage.onload = () => {
                // create a canvas that will present the output image
                var outputImage = document.createElement("canvas");
                var MAX_HEIGHT = cboxht;
                var MAX_WIDTH = MAX_HEIGHT;
                width  = inputImage.naturalWidth;
                height = inputImage.naturalHeight;
                var parent = document.getElementById('boxes');
               
                // reduce size for editing:
                if (width > height) {
                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }
                } else {
                    if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }
                }
                outputImage.width  = width;
                outputImage.height = height;

                // draw our image at position 0, 0 on the canvas
                ctx = outputImage.getContext("2d");
                ctx.drawImage(inputImage, 0, 0, width, height);
                // create a 'global' for use in the 'Apply' routine
                window.IMAGE_DATA = ctx.getImageData(0, 0, width, height);
                var child = document.getElementById('repl');
                parent.replaceChild(outputImage, child);
                // remove Pre-size box and replace with Restart button and text
                var html = '<div id="cprestart"><div><button id="reset">Reset Image</button>';
                html += '</div><div><span>Clear this image and reset for loading;<br />';
                html += 'Or when ready, click "Apply"</span></div></div>';
                $('#presize').replaceWith(html);
                $('#uccrop').text("1");
                $('#ucprev').text("0");

                /**
                 * The following code sets up the crop box and provides functionality
                 * to move it around the canvas
                 */
                var canvasPos = $(outputImage).offset();
                var yCanvas = canvasPos.top;
                var xCanvas = canvasPos.left;
                var mouseIsDown = false;
                ctx.strokeStyle = "yellow";
                ctx.fillStyle   = "yellow";
                ctx.lineWidth   = 3;
                //const canvas_image = ctx.getImageData(0, 0, width, height);
                moveCropper(ctx, xOrg, yOrg); 
                outputImage.onmousedown = function(e) {
                    let xpos = e.pageX - xCanvas;
                    let ypos = e.pageY - yCanvas;
                    if (xpos >= xStart && xpos <= (xStart + blockSize) 
                        && ypos >= yStart && ypos <= (yStart + blockSize)) {
                            mouseIsDown = true;
                        }
                }
                outputImage.onmousemove = function(e) {
                    if (mouseIsDown) {
                        ctx.drawImage(inputImage, 0, 0, width, height);
                        let xpos = e.pageX - xCanvas;
                        let ypos = e.pageY - yCanvas;

                        moveCropper(ctx, xpos, ypos);
                    } else return;
                }
                outputImage.onmouseup = function(e) {
                    mouseIsDown = false;
                }
            };
            // Reset these arrays so as not to conflict w/ktesaUploader.js
            FR_Images = [];
        });
        validated = [];
    });
}
/**
 * A utility function to draw/redraw the crop box
 */
function moveCropper(ctx, x, y) {
    xStart = x;
    yStart = y;
    ctx.beginPath();
    ctx.rect(x, y, cropWd, cropHt);
    ctx.stroke();
    ctx.beginPath();
    ctx.rect(x, y, blockSize, blockSize);
    ctx.fillRect(x, y, blockSize, blockSize);
    ctx.stroke();
    // assign crop values for 'Apply'
    xCropOrg = parseInt(x);
    yCropOrg = parseInt(y);
}
/**
 * If the user wishes to delete the save preview and start over, he/she may
 * click the 'Delete Preview' button.
 */
$('#redo_thumb').on('click', function(ev) {
    ev.preventDefault();
    let img2delete = $('#current_preview').attr('src');
    let post_data = {indxNo: indxNo, img: img2delete};
    $.post('deletePreview.php', post_data, function(result) {
        if (result === 'OK') {
            // note: using reload() can return the user to tab1
            window.open('editDB.php?tab=2&hikeNo=23', '_self');
        }
     });
});
/**
 * Save the cropped image just prior to submitting form
 */
 $(document).off('click', '#ap2').on('click', '#ap2', function(ev) {
    // submit after asynch functions complete
    ev.preventDefault();
    var urlCreator = window.URL || window.webkitURL;
    // first part of file name for preview/thumb
    let prefix = $('#htitle').text();
    prefix = prefix.substr(0, 4);
    // which image has user selected?
    let uccrop = $('#uccrop').text();
    let ucprev = $('#ucprev').text();
    if (uccrop === '1') { // cropped version
        // redraw the canvas without the crop box
        ctx.putImageData(window.IMAGE_DATA, 0, 0);
        // get the cropped image and write it to a canvas element
        let cropData = ctx.getImageData(xCropOrg, yCropOrg, cropWd, cropHt);
        let saveCanvas = document.createElement("canvas");
        saveCanvas.width = cropWd;
        saveCanvas.height = cropHt;
        let ctx1 = saveCanvas.getContext("2d");
        ctx1.putImageData(cropData, 0, 0);
        // prepare this for uploading and for forming thumb.jpg
        var dataurl = saveCanvas.toDataURL('image/jpeg', 0.7);
        var blob1 = dataURItoBlob(dataurl); // this will be uploaded
        var imageSrc = urlCreator.createObjectURL(blob1);
        // create the smaller thumb image
        let thumbimg = document.createElement("img");
        thumbimg.onload = function() {
            let tcanvas = document.createElement("canvas");
            tcanvas.width  = thWd;
            tcanvas.height = thHt;
            let tctx = tcanvas.getContext('2d');
            tctx.drawImage(this, 0, 0, thWd, thHt);
            var tdataurl = tcanvas.toDataURL('image/jpg', .7);
            var blob2 = dataURItoBlob(tdataurl);
            var cpDat = new FormData();
            cpDat.append("prev", blob1);
            cpDat.append("thmb", blob2);
            cpDat.append("prefix", prefix);
            cpDat.append("indxNo", indxNo);
            saveImages(cpDat);
        }
        thumbimg.src = imageSrc; 
    } else if (ucprev === '1') {  // presized image
        var base64img = $('#ps').attr('src');
        var blob1 = b64toBlob(base64img);
        // create the thumb
        let thumbimg = document.createElement("img");
        thumbimg.onload = function() {
            let tcanvas = document.createElement("canvas");
            tcanvas.width  = thWd;
            tcanvas.height = thHt;
            let tctx = tcanvas.getContext('2d');
            tctx.drawImage(this, 0, 0, thWd, thHt);
            var tdataurl = tcanvas.toDataURL('image/jpg', .7);
            var blob2 = dataURItoBlob(tdataurl);
            var psDat = new FormData();
            psDat.append("prev", blob1);
            psDat.append("thmb", blob2);
            psDat.append("prefix", prefix);
            psDat.append("indxNo", indxNo);
            saveImages(psDat);
        }
        thumbimg.src = base64img;
    } 

});
/**
 * Image source data (base64Encoded string) needs to be converted to a blob
 * for uploading - see StackOverflow post:
 * https://stackoverflow.com/questions/27980612/converting-base64-to-blob-in-javascript
 */
 function b64toBlob(dataURI) {
    var byteString = atob(dataURI.split(',')[1]);
    var ab = new ArrayBuffer(byteString.length);
    var ia = new Uint8Array(ab);

    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ab], { type: 'image/jpeg' });
}
/**
 * Upload the preview and thumb images
 */
function saveImages(ajaxdata) {
    $.ajax({
        url: 'savePreview.php',
        method: 'post',
        data: ajaxdata,
        processData: false,
        contentType: false,
        success: function(saved) {
            if (saved === 'OK') {
                posted = true;
                $('#f2').trigger('submit');
            } else {
                alert("Could not save image; contact admin");
            }
        },
        error: function(jqXHR) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });

}