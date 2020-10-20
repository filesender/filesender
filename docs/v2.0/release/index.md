# Release Process

Here you can find how to make a new release for filesender. Details of
past and planned filesender releases are listed in the release
schedule.

## Source packages

* First make sure that you really want to make a release. Make sure
  that you have merged your last pull requests and do not have
  anything remaining in your local master that you would like in the
  release.

* A tag will be made during the release process. There is no need to
  make one locally.

* Visit the [GitHub release page](https://github.com/filesender/filesender/releases)
  and click on [Draft a new release](https://github.com/filesender/filesender/releases/new).

* In the tag version selection enter something following the format filesender-MAJORVER-betaVER.
  For example filesender-2.0-beta3.

* The release title and description should follow the style of the last release on
  the [release page](https://github.com/filesender/filesender/releases). It is best
  to "edit" the page for the previous release and copy that markdown to the new release
  page as a template.

* If there are changes to the database give some indication of what has changed and that
  the database update script must be run

* If there are changes in the templates directory give an indication and some information about that.

* The configuration directives differences can be found by doing a git diff to the previous
  release.
  
* Do not worry about attaching files, github will take tar.gz
  snapshots from the repository for you.

* Record the link to this new release.
  For example: https://github.com/filesender/filesender/releases/tag/filesender-2.0-beta3

* Edit the files containing version numbers and changelogs.
  Particularly the docs directory shows how to get the latest release
  from git and might need to have small changes. Commit this to master
  on github (through a PR).

* You might need to edit the Development Status section on the home
  page to reflect the current release number and release date.

* Send the release announcement email as plain text (NO HTML!) using
  the following email template:

```
To: filesender-announce@filesender.org
Cc: filesender-dev@filesender.org
Subject: New FileSender Released: version N.nn 

Hi,

A new FileSender release is now available for download.
Please see the releases page for full details and information
on how to obtain, install, and migrate to this release

https://github.com/filesender/filesender/releases/

Cheers.
```
    
