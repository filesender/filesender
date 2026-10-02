---
title: Reformatting and styling the code
---

# Introduction

At some stage the formatting of code was performed. Generally if your
pull request looks to have a similar format to the existing code you
will be OK. This page describes the process for reformatting en masse
which was performed and how an attempt was made to mark these changes
are formatting only in git.

# setup

```
./composer.phar install
```

# Reformatting

If you want to show a different user for formatting only changes then
the git commit should be done with something like
scripts/formatting/commit-as-formatter.sh which will set the author to
codeformatter so the change is known to be a reformat during a git
blame and the user can flick back to the previous change on a line to
see who actually authored a semantic change.

```

BRANCH=formatted123

git branch   $BRANCH
git checkout $BRANCH

./reformat-code.sh
```

# Sending in the changes

There is a handy script to do a commit of one or more things to git
with the author information set to a codeformatter user so that it is
obvious this is a style change that was machine generated.

```
./scripts/formatting/commit-as-formatter.sh classes

git push origin $BRANCH

```

On github the pull request will have to be merged with a "merge commit"
otherwise the author information will be compacted down to the person
making the pull request.

