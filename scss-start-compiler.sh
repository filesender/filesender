#!/bin/bash

watch='';


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
