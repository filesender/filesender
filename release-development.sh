#!/bin/bash

releasebranch=${1:?supply release branch as arg1};


git checkout master
git fetch upstream
git merge upstream/master
git checkout upstream/development
git diff master >| ~/bak/fs.patch
git checkout master
git checkout -b $releasebranch
patch -p1 < ~/bak/fs.patch
git status

echo "do git add and commit then push to complete."

