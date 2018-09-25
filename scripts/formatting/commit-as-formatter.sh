#!/bin/bash

export GIT_COMMITTER_NAME="codeformatter"
export GIT_COMMITTER_EMAIL="<codeformatter@filesender.org>"
export GIT_AUTHOR_NAME="codeformatter"
export GIT_AUTHOR_EMAIL="<codeformatter@filesender.org>"


git commit   --author 'codeformatter <codeformatter@filesender.org>' "$@"
