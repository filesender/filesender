#!/bin/sh

# Do a quick check for missing language tags in a set of
# language files in the current directory.

# Assumptions:
# - All tags are defined as $lang["<name>"] = "<definition>";
# - $lang tags start at the beginning of a line (no whitespace before $lang)
# - All other lines (including continuation lines) are ignored.
# - language files are named according to the specification in
#   language/README.language which more or less is matched by the shell
#   pattern [a-z][a-z]*_[A-Z][A-Z]*.php
# - The 'master' definitions are found in the ../language/en_AU.php file.
#   Adapt the $master variable as needed.

# Basic usage:
#
# cd <directory with language files>
# ../scripts/check-language-tags.sh
#
# Output is ordered by tag
# To get a list ordered by file just pipe the output through sort:
#
# ../scripts/check-language-tags.sh | sort

# Variables
master="../language/en_AU.php"
files=$(ls [a-z][a-z]*_[A-Z][A-Z]*.php)

# Check for tags defined in the master file and missing in
# language files being checked.

grep '^\$lang' ${master} | cut -d= -f1 | sort | while read tag
do
  for f in $files
  do
    if grep -qF "$tag" $f 
    then
       true
    else
      echo "$f: missing $tag"
    fi
  done
done

# Check for tags unknown in the master file but defined in
# language files being checked.

for f in $files
do
  grep '^\$lang' $f | cut -d= -f1 | sort | while read tag
  do
    if grep -qF "$tag" ${master}
    then
       true
    else
      echo "$f: unknown $tag"
    fi
  done
done
