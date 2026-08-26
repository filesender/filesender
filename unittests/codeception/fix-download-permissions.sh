#!/bin/bash

# the below sudo just does something like this
# so we can get at the downloads as another user.
# chmod g+r /home/testdriver/Downloads/*

sudo /home/ben/bin/filesender-fix-testdriver-permissions.sh
cd /tmp/selenium-download
activefile=$(ls -1rtc|tail -1);
echo "$activefile" >| /tmp/selenium-downloads/active.txt
