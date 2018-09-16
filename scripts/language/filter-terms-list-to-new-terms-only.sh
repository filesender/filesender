#!/bin/bash

oldfile=${1:?supply the old term export as arg1};
newfile=${2:?supply the new term export as arg2};

diff --old-line-format="" --unchanged-line-format=""  $oldfile $newfile 
