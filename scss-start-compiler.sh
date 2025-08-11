#!/bin/bash

watch='';

which sass  > /dev/null 2>&1
if [ $? != "0" ]; then
    echo "No sass command found!"
    echo ""
    echo "    You may like to use the following to install it"
    echo ""
    echo " npm install sass "
    echo ""
    echo "    and include the node_modules/.bin/sass command in your path"
    echo ""
    echo "Exiting."
    exit 1
fi


if [ "x$1" = "xwatch" ] || [ "x$1" = "x--watch" ]; then
	watch='--watch';
	echo "ok... will watch you SCSS source file..."
else
	echo "use watch as arg1 if you want to keep updating default.css"
	echo "  from the SASS source when it is changed";
	echo "";
fi


cd ./www/css
sass $watch default.scss default.css
