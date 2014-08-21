#!/bin/bash

###
# User: Damien Bruchet
# Date: 04/09/2014
# Time: 08:56
# Version: 1.0
#
# This script allows to automate execution of unittest for PHP projets.
# See associated documentation.
##/

printf "\033c"

DATE=$(date +%Y-%m-%d_%H\h%M) 

echo
printf '%*s\n' "${COLUMNS:-$(tput cols)}" '' | tr ' ' -
echo "$DATE"
echo Launching unit tests using PHPUnit : 
printf '%*s\n' "${COLUMNS:-$(tput cols)}" '' | tr ' ' -
echo

logfilename=log_"$DATE".log
resultfilename=result_"$DATE".txt

error=false
if [ "$1" = "" ]; then
    echo Syntax error: type launcher.sh --help for more informations.
elif [[ "$1" = "--help" || "$1" = "-h" ]]; then
    echo
    echo usage: launcher.sh testFolder [testfile.php] [[-c [testconfig.xml]] [-ts [testSuiteName]]]
else
    if [ "$1" = "-c" ]; then
        if [ "$2" = "" ]; then
            echo Syntax error: using -c, attended xml configuration file
            error=true
        else
            tmpsrc=tests/testsuitelogs
            if [ ! -d "$tmpsrc" ]; then
                mkdir -m 0777 $tmpsrc
            fi
            tmpsrc=tests/testsuitelogs/logs
            if [ ! -d "$tmpsrc" ]; then
                mkdir -m 0777 $tmpsrc
            fi
            tmpsrc=tests/testsuitelogs/html
            if [ ! -d "$tmpsrc" ]; then
                mkdir -m 0777 $tmpsrc
            fi
            if [ -f "$2" ]; then
                srclog="tests/testsuitelogs/logs/$logfilename"
                srcres="tests/testsuitelogs/logs/$resultfilename"
                srchtml="tests/testsuitelogs/html"
                if [ ! -f "$srclog" ]; then
                    touch $srclog
                fi
                if [ ! -f "$srcres" ]; then
                    touch $srcres
                fi
                echo command:
                if [[ "$3" = "-ts" || "$3" = "-testsuite" ]] && [ "$4" != "" ]; then
                    echo "phpunit --debug -c $2 --testsuite $4 --log-junit $srclog --coverage-html $srchtml > $srcres"
                    echo
                    echo
                    echo -n "Test(s) running... "
                    phpunit --debug -c "$2" --testsuite "$4" --log-junit "$srclog" --coverage-html $srchtml > "$srcres"
                else
                    echo "phpunit --debug -c $2 --log-junit $srclog --coverage-html $srchtml > $srcres"
                    echo
                    echo
                    echo -n "Test(s) running... "
                    phpunit --debug  -c "$2" --log-junit "$srclog" --coverage-html "$srchtml" > "$srcres"    
                fi
            else
                echo Error: file not found: "$2"
                error=true
            fi
        fi
    else
        srclog=''
        srcres=''
        srchtml=''
        srcfile=''
        if [ -d "$1" ]; then
            srclog="$1"/logs/"$logfilename"
            srcres="$1"/logs/"$resultfilename"
            srchtml="$1"/html
            srcfile="$1"
        fi
        if [ -d "tests/$1" ]
        then
            srclog=tests/"$1"/logs/"$logfilename"
            srcres=tests/"$1"/logs/"$resultfilename"
            srchtml=tests/"$1"/html
            srcfile=tests/"$1"
        fi
        if [ -f "$1" ]; then
            tmpsrc=$(dirname "$1")
            srclog="$tmpsrc"/"$logfilename"
            srcres="$tmpsrc"/"$resultfilename"
            srchtml="$tmpsrc"/html
            srcfile="$tmpsrc"
        fi
        tmpsrc=$(dirname "$srclog")
        if [ ! -d "$tmpsrc" ]; then
            mkdir -m 0777 $tmpsrc
        fi
        tmpsrc=$(dirname "$srchtml")
        if [ ! -d "$tmpsrc" ]; then
            mkdir -m 0777 $tmpsrc
        fi
        tmpsrc=$(dirname "$srchtml")
        if [ ! -d "$tmpsrc" ]; then
            mkdir -m 0777 $tmpsrc
        fi
        if [ "$srclog" = '' ]; then
            echo Error: no folder/file found: "$1"  
            error=true
        else
            echo "phpunit --debug  --coverage-html $srchtml --log-junit $srclog $srcfile > $srcres"
            echo
            echo
            touch "$srclog"
            touch "$srcres"
            echo -n "Test(s) running... "
            phpunit --debug --coverage-html "$srchtml" --log-junit $srclog "$srcfile"  > "$srcres"  
        fi
    fi
    if [ "$error" = "false" ]; then
        echo "Done."
        echo
        printf '%*s\n' "${COLUMNS:-$(tput cols)}" '' | tr ' ' -
        echo Results and logs are now availiable on the 
        echo "$srcres" and "$srclog" folders
        printf '%*s\n' "${COLUMNS:-$(tput cols)}" '' | tr ' ' -
    fi
fi
echo
