#!/bin/bash

name=`uuidgen`

mkdir -p /tmp/selenium-downloads

echo "$name" >| /tmp/selenium-downloads/active.txt

sleep 1
xdotool search "Warning: this site can see edits you make" windowactivate
sleep 1
xdotool type "$name"
xdotool key Return
