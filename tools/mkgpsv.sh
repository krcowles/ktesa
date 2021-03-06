#!/bin/bash
tmpIFS=$IFS
# VERSION 8.1 -- Use expanded .csv table to include custom icons & marker colors
#     add $IFS-related cmds to address spaces between words when performing a read
# --- Specify starting & default values for shell variables  ----
#
# FILESRC is flag indicating if a source file is being specified (-f flag)
# SrcFileName is file specified after the -f flag, if present, default ""
FILESRC=0
SrcFileName=""
# URLSRC is flag indicating if url is specified (-u flag)
# SrcUrl is Flickr album URL from which photo urls will be extracted, default ""
URLSRC=0
SrcUrl=""
#
# JPGFLAG is flag indicating if a path spec is supplied for photo JPG or jpg files (-j flag)
# Default location for .JPG or .jpg files is current directory if no -j flag
JPGFLAG=0
JpgLoc="."
#
# OPTFLAG indicates if -o flag is present to specify Flickr page displayed
# OnClick indicates Flickr page used to display photo when popup photo is clicked, default album-only
OPTFLAG=0
OnClick=2
#
# THUMBARG indicates if -t flag is present, specifying popup photo size
# ThumbSize indicates popup photo size, default is "m" (see size chart below)
THUMBARG=0
ThumbSize=q	# Default size for thumbnails
#
# ICONFLAG indicates if -i flag is present, allowing user to enter custom icon data;
#   otherwise, if desired, user must enter data manually after this utility completes
ICONFLAG=0
#
# LPIX stores the names of .jpg files, Lno is the number of those files
# UPIX stores the names of .JPG files, Uno is the number of those files
LPIX=""
UPIX=""
Lno=0
Uno=0
#
export JpgLoc
#
# Now process command line arguments
for i
do
	# -f Get filename for local html 
	if [ $FILESRC -gt 0 ]
	then {
		SrcFileName=$i
		FILESRC=0
	}
	fi
	# -u or else get Url for html source
	if [ $URLSRC -gt 0 ]
	then {
		SrcUrl=$i
		URLSRC=0
		echo "$SrcUrl" | cat > FlckrAlbumURL
	}
	fi
	# -j file spec for location of jpg's
	if [ $JPGFLAG -gt 0 ]
	then {
		JpgLoc=$i
		JPGFLAG=0
		echo $JPGFLAG
	}
	fi
        # "-o" opt:
        if [ $OPTFLAG -gt 0 ]
        then {
                OnClick=$i
                OPTFLAG=0
        }
        fi
        # "-t" opt:
        if [ $THUMBARG -gt 0 ]
        then {
                ThumbSize=$i
                THUMBARG=0
        }
        fi
        # find flags:
        case $i in
	-f) FILESRC=1 ;;
	-h) echo "
*** VERSION 8.0 - Expanded .csv file

This utility will create a local file called \"GPSVinput.tsv\", suitable for use
as input (along with the corresponding .GPX file) on the GPSVisualizer website.
That site will create a geomap providing popup photos from a Flickr album. The Flickr
album must first be created, and local copies of all the photos in the Flickr album
(with full metadata) must exist for this utility to complete succesfully.

This utility requires either the -f flag or -u flag (see below)

Some of the optional arguments may use a photo \"letter size\" corresponding to a
Flickr designation of the image size. The defined letter sizes (appearing in the
column indicating \"KEY\") are provided below for the Samsung phone (default) and 
for the iPhone6. The Image Size is in pixels:
-------------------------------------------------------------
 Letter Size: 
 TYPE          KEY     IMAGE SIZE      NOTES
-------------------------------------------------------------
 Square 75     sq      75 x 75         Both phones
 Square 150    q       150 x 150       Both phones
 Thumbnail     t       100 x 60        iPhone6: 100 x 75
 Small 240     s       240 x 144       iPhone6: 240 x 180
 Small 320     n       320 x 192       iPhone6: 320 x 240
 Medium 500    m       500 x 300       iPhone6: 500 x 375
 Medium 640    z       640 x 384       iPhone6: 640 x 480
 Medium 800    c       800 x 480       iPhone6: 800 x 600
 Large 1024    l       1024 x 614      iPhone6: 1024 x 768
 Large 1600    h       1600 x 960      iPhone6: 1600 x 1200
 Large 2048    k       2048 x 1229     iPhone6: 2048 x 1536
 Original      o       2560 x 1536     iPhone6: 3264 x 2448

User will be prompted to specify a marker color for the photo-marker popups; 
if no color is specified, the default is red.

Flag arguments must be preceded by a space. The options for this utility are:
    -f  =>  OPTIONAL/REQUIRED, 1 ARG; supply filename as ARG for html source file
            *** NOTE: MUST USE EITHER -f or -u FLAG on command line
    -o  =>  OPTIONAL, 1 ARG; Specify option for displaying \"on-click\" picture:
            1  -Supplies Flickr URL for picture in album, allowing photostream viewing (size \"o\")
            2  -Supplies Flickr URL for picture in album, allowing album-only viewing (size \"o\")
            3  -MAY HAVE KEYSIZE LETTER(S) APPENDED: e.g. 3m or 3sq, NO LETTER defaults to \"o\"
               -Supplies Flickr URL for web file of picture for specified letter size
            4  -Supplies Flickr URL for picture on album \"sizes\" page
            [DEFAULT, -o not specified: 1]
    -j      OPTIONAL, 1 ARG; supply file path for jpg files (e.g. ~/Desktop/Hike1), default is local
    -t  =>  OPTIONAL, 1 ARG; ARG is size letter (see listing at bottom for sizes)
            for determining pop-up (thumbnail) picture size
            [DEFAULT, -t not specified, or no ARG after -t: m]
            Caution: larger image sizes may slow popup time
    -u  =>  OPTIONAL, 1 ARG; supply URL for Flickr web source as ARG
            *** NOTE: MUST USE EITHER -f or -u FLAG on command line
    -i  =>  OPTIONAL: NO ARGMUMENT; if present, user is prompted to enter custom icon data
            Prompt will ask for:
		Description (associated with icon at specified location)
		Name (popup data to appear when icon is moused-over)
		Latitude
		Longitude
		url (must be complete http: spec, e.g. http://krcowles.github.io/ktesa/images/ike.png
		pixel size (e.g. 32x32)
            If no flag is present, the fields will be empty and can be post-edited
	
" | more
exit;;
	-i) ICONFLAG=1 ;;
	-j) JPGFLAG=1 ;;
        -o) OPTFLAG=1 ;;
        -t) THUMBARG=1 ;;
        -u) URLSRC=1 ;;
        esac
done
# DONE PROCESSING COMMAND LINE
#
# First, get either the specified .html source file, or the url of the Flickr Album:
# tmpfile "zool" is created then RMFLAG is set to tell the script to remove it later
if( test -n "$SrcUrl" )
then {
	curl -L $SrcUrl > zool
	SrcFileName=zool
	RMFLAG=1
}
else    RMFLAG=0
fi
#
# Create 2 lists of picture name files: lower- and upper-case jpg filenames:
PFILES=( `ls -1 $JpgLoc` )
for filename in ${PFILES[@]}
do
        case $filename in
                *.jpg) LPIX="${LPIX} ${filename%.*}" ;;
                *.JPG) UPIX="${UPIX} ${filename%.*}" ;;
        esac
done
Lno=`echo "$LPIX" | wc -w`
Uno=`echo "$UPIX" | wc -w`
echo ""
read -p "Enter FIRST LETTER of color to be used for photo markers
	[Pink,Red,Maroon,Orange,Green,Aqua,Teal,Blue,Navy,Violet]: " colr
good="notOK"
while [ $good != "OK" ]
do
	echo ""
	case $colr in
		[pP]* ) colr="pink"
			good="OK" ;;
		[rR]* ) colr="red"
			good="OK" ;;
		[mM]* ) colr="maroon"
			good="OK" ;;
		[oO]* ) colr="orange"
			good="OK" ;;
		[gG]* ) colr="green"
			good="OK" ;;
		[aA]* ) colr="aqua"
			good="OK" ;;
		[tT]* ) colr="teal"
			good="OK" ;;
		[bB]* ) colr="blue"
			good="OK" ;;
		[nN]* ) colr="navy"
			good="OK" ;;
		[vV]* ) colr="violet"
			good="OK" ;;
		* ) echo "Unrecognized character(s) OR no entry, please re-enter"
		    echo ""
		    read -p "Enter first letter of color to be used for photo markers [Pink,Red,Maroon,Orange,Green,Aqua,Teal,Blue,Navy,Violet]: " colr ;;
	esac
done
echo "*** You Chose: ${colr}  ***   -if this is incorrect, please edit later"
echo ""
#
# IF -i flag was specified, collect user data
iconindx=0
if [ $ICONFLAG -gt 0 ]
then {
	IFS=$'|'
	declare -a icondes
	declare -a iconnme
	declare -a iconlat
	declare -a iconlng
	declare -a iconurl
	declare -a iconsize
	echo "Icon data will now be collected"
	echo "* NOTE: Custom icon data entered will be echoed back, but will not be verified:
if editing is required, do so after the utility has completed."
	echo ""
	good="notOK"
	while
            [ $good != "OK" ]
        do
	    read -p "Is more than 1 custom icon being specified? [Y/N]: " ans
            case $ans in
                [nN]* ) mults=0
                        good="OK" ;;
                [yY]* ) mults=1
	                good="OK";;
                * ) echo "Y or N only please!"
	            echo "" ;;
            esac
        done
	cont="Y"
	while
	    [ $cont == "Y" ]
	do
	    read -p "Description field for icon (arbitrary): " icona
	    icondes[iconindx]="$icona"
	    read -p "Name to appear when mouseover of custom icon: " iconb
	    iconnme[iconindx]="$iconb"
	    read -p "Latitude of icon: " iconc
	    iconlat[iconindx]="$iconc"
	    read -p "Longitude of icon: " icond
	    iconlng[iconindx]="$icond"
	    read -p "Enter the full url of custom icon (e.g. http://zorro.github.io/images/icon.png): " icone
	    iconurl[iconindx]="$icone"
	    read -p "Icon size in pixels (e.g. 32x32): " iconf
	    iconsize[iconindx]="$iconf"
	    if [ $mults -eq 0 ]
	    then 
		cont="N"
	    else {
		good="notOK"
		while
            		[ $good != "OK" ]
        	do
            		read -p "Do you wish to enter another custom icon? [Y/N]: " ans
            		case $ans in
                		[nN]* ) cont="N"
                        	        good="OK" ;;
                		[yY]* ) echo ""
				        good="OK" ;;
                		* ) echo "Y or N only please!"
                    		    echo "" ;;
                	esac
        	done
	    }
	    fi
	    iconindx=$(( $iconindx + 1 ))
	done
	k=0
	echo "$k" ", ${iconindx}"
	while
		[ $k -lt $iconindx ]
	do
		echo ""
		echo "*** Custom icon data set ***"
		echo "Description: ${icondes[k]}"
        	echo "Name: ${iconnme[k]}"
        	echo "Latitude: ${iconlat[k]}"
        	echo "Longitude: ${iconlng[k]}"
        	echo "URL for this icon: ${iconurl[k]}"
        	echo "Pixel size: ${iconsize[k]}"
		k=$(( $k + 1 ))
	done
	IFS=tmpIFS
}
fi
#
# Dump the source html into the following 'awk' script for processing/extracting links
# awk specifies a function "ExtractSring" to handle repeated requests to pull data from a string
#
echo "Working..."
cat $SrcFileName |
/usr/bin/awk -v CheckOpt=$OnClick -v Tsize=$ThumbSize -v FSpec=$JpgLoc -v lnum="$Lno" -v unum="$Uno" -v ljpgs="$LPIX" -v ujpgs="$UPIX" -v noOfIcons=$iconindx -v icodes="${icondes[*]}" -v iconms="${iconnme[*]}" -v icolats="${iconlat[*]}" -v icolngs="${iconlng[*]}" -v icourls="${iconurl[*]}" -v icopix="${iconsize[*]}" -v icolor="$colr" ' function ExtractString(Str,EndQuote)
{
        AttrStart=RSTART+RLENGTH+1
        RemainingString=substr(Str,AttrStart,length(Str)-AttrStart)
        if( EndQuote == 1 ) {
                AttrEnd=index(RemainingString,"\"")-1
                Attr=substr(RemainingString,1,AttrEnd)
                return Attr
        }
        else return RemainingString
}
# Set input & output field separators, process options and print header of the output file:
BEGIN   {
        FS=","
        OFS="\t"
        OptLength=length(CheckOpt)
        if( OptLength > 3 ) {
                print "** VIEWING OPTION UNSUPPORTED **"
                exit(4)
        }
        if( OptLength == 3 ) {
                ViewOpt=substr(CheckOpt,1,1)
                # Unless incorrectly specified, this is the "sq" letter option
                LargeSize="sq"
        }
        if( OptLength == 2 ) {
                ViewOpt=substr(CheckOpt,1,1)
                LargeSize=substr(CheckOpt,2,1)
        }
        if( OptLength == 1 ) {
                ViewOpt=sprintf(CheckOpt)
                LargeSize="o"
        }
        LargeStrEnd=sprintf("\"key\":" LargeSize)
	if( lnum > 0 ) {
		split(ljpgs,LPIX," ")
	}
	if( unum > 0 ) {
		split(ujpgs,UPIX," ")
	}
#
# Create Header Row: (and custom icons, if any)
#
        print "folder" OFS "desc" OFS "name" OFS "Latitude" OFS "Longitude" OFS "thumbnail" OFS "url" OFS "date" OFS "n-size" OFS "symbol" OFS "icon_size" OFS "color"
#
# Enter custom icon data, if any...
	if ( noOfIcons > 0 ) {
                split(icodes,iDesc,"|")
                split(iconms,iName,"|")
                split(icolats,iLat," ")
                split(icolngs,iLng," ")
                split(icourls,iURL," ")
                x = split(icopix,iSize," ")
		for( n=1;n<noOfIcons+1;n++ ) {
			print "Folder1" OFS iDesc[n] OFS iName[n] OFS iLat[n] OFS iLng[n] OFS OFS OFS OFS OFS iURL[n] OFS iSize[n]
		}
        }
} #end BEGIN
{
        # Extract the "albumId" from the html
        if (match($0,/\"albumId\":/)) {
                albumId=ExtractString($0,1)
        }
	# Find the photo-models segment and form an array of sizes with album links (PicsArray)
        if (match($0,/\{\"_flickrModelRegistry\":\"photo-models\"/)) {
                NumPix = split($0,PicsArray,/\{\"_flickrModelRegistry\":\"photo-models\"/)
                # Get Owner ID
                if( match(PicsArray[1], /\"id\":/)) {
                        ownerId=ExtractString($0,1)
                }
                else exit(4)
                for (j=2; j<=NumPix; j++) {

                        # Get title = photo name = file name:
                        if( match(PicsArray[j], /\"title\":/)) {
                                title=ExtractString(PicsArray[j],1)
                        }
                        else exit(4)

                        # There may or may not be a "description", check the string:
                        if( match(PicsArray[j], /\"description\":/)) {
                                description=ExtractString(PicsArray[j],1)
                        }
                        else description = "Enter description here"

                        # Get the thumbnail pointer:
                        if( match(PicsArray[j], "\"" Tsize "\"")) {
                                KS_String=ExtractString(PicsArray[j],0)
                                if( match(KS_String, /\"url\":/)) {
                                        thumbUrl=ExtractString(KS_String,1)
                                        gsub(/\\/,"",thumbUrl)
                                }
                                else exit(4)
                        }
                        else exit(4)
                        
                        # Get the n-size pointer, regardless of thumbnail specified above
                        if( match(PicsArray[j], "\"n\"")) {
				nSize=ExtractString(PicsArray[j],0)
				if( match(nSize,/\"url\":/)) {
					n_url=ExtractString(nSize,1)
					gsub(/\\/,"",n_url)
				}
				else exit(4)
			}
                        
                        # Get Photo ID to use for building Photo URL - tjs note could also extract this from thumbUrl
                        if( match(PicsArray[j], /\"ownerNsid\":/)) {
                                Nsid=ExtractString(PicsArray[j],0)
                                if( match(Nsid, /\"id\":/)) {
                                        photoId=ExtractString(Nsid,1)
                                }
                                else exit(4)
                        }
                        else exit(4)
			
                        # Get GPS data using exiftool: NOTE: case treatment of jpg (upper & lower-case)
			if( lnum > 0 ) {
				for(m=1; m<=lnum; m++) {
					if( LPIX[m] ~ title ) {
						exifCmdLine=sprintf("exiftool -csv -n -GPSL*e -Date* " FSpec "/" title ".jpg | tail -n+2" )
					}
				}
			}
			if( unum > 0 ) {
				for(k=1; k<=unum; k++) {
					if( UPIX[k] ~ title ) {
						exifCmdLine=sprintf("exiftool -csv -n -GPSL*e -Date* " FSpec "/" title ".JPG | tail -n+2" )
					}
				}
			}
                        #exifCmdLine=sprintf("exiftool -csv -n -GPSL*e -Date* " FSpec "/" title "*.JPG | tail -n+2" )
                        exifCmdLine | getline exifOutput
			gpsInfoCount=split(exifOutput,gpsInfo,/,/)

                        # All elements are in place to create viewing option specified:
                        flickrleft="https://www.flickr.com/photos/"
                        # Opt 1: flickrleft ownerId / "albums/" albumId "/" photoId
                        if( ViewOpt == 1 ) {
                                pageurl=sprintf(flickrleft ownerId "/" photoId "/in/photostream/")
                        }
                        # Opt 2: flickrleft ownerId "/" photoId "/" in/album- albumId
                        if( ViewOpt == 2) {
                                pageurl=sprintf(flickrleft ownerId "/" photoId "/in/album-" albumId)
                        }
                        # Opt 4: flickrleft ownerId/photoId/sizes/l
                        if( ViewOpt == 4 ) {
                                pageurl=sprintf(flickrleft ownerId "/" photoId "/sizes/l")
                        }
                        if( ViewOpt == 1 || ViewOpt == 2 || ViewOpt == 4 ) {
                                print "Folder1" OFS title OFS description OFS gpsInfo[2] OFS gpsInfo[3] OFS "https:" thumbUrl OFS pageurl OFS gpsInfo[4] OFS "https:" n_url OFS OFS OFS icolor
                        }
                        # Opt 3: already stashed in tmp.csv
                        if( ViewOpt == 3 ) {
                                if( match(PicsArray[j], "\"" LargeSize "\"")) {
                                LS_String=ExtractString(PicsArray[j],0)
                                if( match(LS_String, /\"url\":/)) {
                                        largeUrl=ExtractString(LS_String,1)
                                        gsub(/\\/,"",largeUrl)
                                }
                                else exit(4)
                                print "Folder1" OFS title OFS description OFS gpsInfo[2] OFS gpsInfo[3] OFS "https:" thumbUrl OFS "https:" largeUrl OFS gpsInfo[4] OFS "https:" n_url
                        }
                        else exit(4)

                        }
                }  #END for(j=2;   loop
        }  #END match for photo-models
}  #END awk script
' > GPSVinput.tsv
if [ $RMFLAG == 1 ]
then {
	$(rm zool)
}
fi
echo "DONE"
#
#-------------------------------------------------------------
# Size Letter Options: NOTE: Samsung phone listed first
# TYPE          KEY     IMAGE SIZE      NOTES
#-------------------------------------------------------------
# Square 75     sq      75 x 75         Both phones
# Square 150    q       150 x 150       Both phones
# Thumbnail     t       100 x 60        iPhone6: 100 x 75
# Small 240     s       240 x 144       iPhone6: 240 x 180
# Small 320     n       320 x 192       iPhone6: 320 x 240
# Medium 500    m       500 x 300       iPhone6: 500 x 375
# Medium 640    z       640 x 384       iPhone6: 640 x 480
# Medium 800    c       800 x 480       iPhone6: 800 x 600
# Large 1024    l       1024 x 614      iPhone6: 1024 x 768
# Large 1600    h       1600 x 960      iPhone6: 1600 x 1200
# Large 2048    k       2048 x 1229     iPhone6: 2048 x 1536
# Original      o       2560 x 1536     iPhone6: 3264 x 2448

