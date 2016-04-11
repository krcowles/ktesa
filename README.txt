The tools in this directory are intended for the following use:
1. mkgpsv_r5: a unix script, requiring input options, used to create,
   in conjunction with pictures in a Flickr album and a GPX track file,
   an input file for the GPSVisualizer web tool. The GPSVisualizer
   creates the geomaps used by the site pages.
   
2. mkpage.html: the html source, with corresponding CSS and javascript
   files, intended for creating the html for the image portion of each
   site page. The html file created is "newPage.txt". For each site
   page to be created, modify the html for mkpage according to its
   instructions. The new html can then be copied and pasted into the
   body of the new site page. 
   
3. newPage.html: an example of html source created by incorporating newPage.txt,
   with corresponding CSS and javascript to effect the popup descriptions on
   included photos.
