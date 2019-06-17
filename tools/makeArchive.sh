#!/bin/bash
# Utility ffor making archive that can be uploaded to 000webhost 
# VERSION: 1.0 Create archive, add vendor directory, add file containing commit number
# Usage: ../tools/makeArchive branch_name commit_number
# Must be run from git home directory. Places archive in ../CUArchives directory

git archive -o ../CuArchives/$1_$2.zip $1   # Create archive
zip -rq ../CuArchives/$1_$2.zip vendor      # Add vendor directory
echo $1_$2 > admin/commit_number.txt        # Commit number to text file
zip -rq ../CuArchives/$1_$2.zip admin/commit_number.txt # Add file to archive
echo "DONE"
