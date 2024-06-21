# Release Process

Here you can find how to make a new release for filesender. Details of
past and planned filesender releases are listed in the release
schedule.

Releases are numbered incrementally with a structure of MAJOR.PATCH
naming, where MAJOR and PATCH are both numbers. Releases are made when
there is functionality added, removed, changed, or fixed. Releases are
cut from the GitHub `master` branch. Updates from the `development`
branch are squashed into a single commit and pushed up to github as a
pull request to `master`.

## Source packages

* First make sure that you really want to make a release. Make sure
  that you have merged your last pull requests and do not have
  anything remaining in your local development branch that you would
  like in the release.

* Releases are cut from the GitHub master branch. The master will have
  squashed commits from the development branch. The updates from
  development need to be merged into master using a pull request.

* A tag will be made during the release process. There is no need to
  make one locally.

* Visit the [GitHub release page](https://github.com/filesender/filesender/releases)
  and click on [Draft a new release](https://github.com/filesender/filesender/releases/new).
  Make sure you select master as the branch to make the release from.

* In the tag version selection enter something following the format
  filesender-MAJORVER-betaVER. For example filesender-3.0.
  Releases are tagged using git tags in the form of
  <branch>-filesender-MAJOR.PATCH

* It can be useful to find a previous release and click "edit" and
  copy the markdown to the new release you have started and remove the
  content from each section. Otherwise, GitHub releases have a set
  structure, providing sections about: 1) The release version, 2)
  Distribution 3) Installation 4) Upgrade notes 5) Major changes 6)
  Configuration changes 7) Support and Feedback.

* The release title and description should follow the style of the
  last release on the [release
  page](https://github.com/filesender/filesender/releases). It is best
  to "edit" the page for the previous release and copy that markdown
  to the new release page as a template.

* If there are changes to the database give some indication of what
  has changed and that the database update script must be run

* If there are changes in the templates directory give an indication
  and some information about that.

* The configuration directives differences can be found by doing a git
  diff to the previous release.
  
* Do not worry about attaching files, github will take tar.gz
  snapshots from the repository for you. GitHub releases have
  artifacts added to them in two formats: ZIP (.zip) and GZipped
  Tarball (.tar.gz). The artifacts are named according to the tagname,
  suffixed by the format of the artifact.


* Record the link to this new release.
  For example: https://github.com/filesender/filesender/releases/tag/filesender-3.0

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
    
