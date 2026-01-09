#!/bin/bash
# Utility for making archive that can be uploaded to nmhikes.com 
# VERSION: 1.0 Create archive, add vendor directory, add file containing commit number
#              Archive is placed in directory ../CuArchives
# VERSION: 1.1 Use full path for git so that cmd will run from php; 
#              Ensure CuArchives is available (see 'cd' command)
# VERSION  1.2 Remove vendor zip to reduce ftp overhead; this file
#              will already be populated in public_html/zipUploads
# Usage: docroot/tools/makeArchive.sh branch_name commit_number
# Upload this archive, then upload vendor.zip
cd /Users/kencowles/src/ktesa
/usr/local/git/bin/git archive -o ../CuArchives/$1_$2.zip $1   # Create archive
#zip -rq ../CuArchives/$1_$2.zip vendor      # Add vendor directory [NOT]
echo $1_$2 > admin/commit_number.txt        # Commit number to text file
zip -rq ../CuArchives/$1_$2.zip admin/commit_number.txt # Add file to archive
echo "DONE"
