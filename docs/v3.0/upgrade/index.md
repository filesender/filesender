---
title: Upgrade to a newer 2.x release
---

## Upgrade to a newer 2.x release

This page provides details of how to update from a 2.x series release
that you may have installed using the 
[installation](https://docs.filesender.org/filesender/v2.0/install/) process.

You will notice that the release notes starts out mentioning in the 
"Major changes" section if the database update script needs to be run
and if the templates directory has changed. If the database update
script is not needed you can elect to skip running it for the update.

If the templates directory has changed in a release and you have modified 
any files in the templates directory of your installation you will have
to bring those modifications forward to the new templates files when 
upgrading.

The release notes also mentions new configuration changes and the keys
that are new. The intention is that new keys will have sane default
values so you only need to investigate for new features that you might
like to turn on or configure in your config.php.

### Process overview

* Update the source code
* If needed and there is a conflict, merge your changes to files in the templates directory
* If needed, run the database script to update the database schema
* Peruse the new configuration options to enable new functionality
* Test your setup 
* Enjoy

### Update the source code

The following two subsections provide information about updating the
source code using either the source tar balls or directly with git.
Note that whichever method you use you should ensure that the file
[permissions](https://docs.filesender.org/filesender/v2.0/install/#step-3---setup-the-filesender-configuration)
are set properly after the update.

#### Installing from source archives

Download the source archive into a directory, for example ~/src. Lets
assume you have downloaded a release and have a file at
~/src/filesender-2.41.tar.gz. This can be directly extracted into your
/opt/filesender directory by being in the right directory and telling
tar to strip the leading filesender-filesender-2.41.


```
su -l
cd  /opt/filesender
tar --extract \
    --file ~/src/filesender-filesender-2.41.tar.gz \
    --strip-components=1  \
    --verbose

```


#### Using FileSender using git

For the 2.x series the master branch tracks the releases. This means
that if 2.41 is the latest release the master should also be version
2.41. Each release has a tag and git commit associated with it and you
can use that to checkout the source code for an exact version if you
like. The two last git commands below checkout version 2.41 and the
current 2.x series release respectively. You may receive an error message
trying to switch to another version if you have modified the templates directory. 
This is considered in the next section.

```
cd  /opt/filesender
git fetch origin

git checkout filesender-2.41
git checkout master
```

### The templates directory

You might be able to skip this section if you did not change your
local templates or source files. On the other hand, if you are seeing
something like the below message when you try to upgrade this section
might help.

```
((filesender-2.40))]$ vi templates/upload_page.php

... make changes ...

((filesender-2.40))]$ git checkout filesender-2.41
error: Your local changes to the following files would be overwritten by checkout:
        templates/upload_page.php
Please commit your changes or stash them before you switch branches.
Aborting
```

If there are local changes you might like to commit them to a branch,
checkout the release you would like to use, and try applying your
local changes again as shown below.

```
((filesender-2.40))]$ git checkout -b filesender-2.40-local
((filesender-2.40))]$ git commit -a -m 'my changes'
((filesender-2.40))]$ git diff filesender-2.40 > ~/tmp/filesender-local-changes.patch
((filesender-2.40))]$ git checkout filesender-2.41
((filesender-2.41))]$ patch -p1 < ~/tmp/filesender-local-changes.patch
```

### The database script

The same script that created the database can also inspect the
existing database and make the required changes for a release. For
example, adding a new table or column. If you are running MySQL or
MariaDB you will need to ensure that the drop permission is available
to the script as shown in the
[installation](https://docs.filesender.org/filesender/v2.0/install/#option-b---mysql).
This is unfortunate as only views should be dropped and it is hoped
that a less aggressive permission can be used in the future.


```
php /opt/filesender/filesender/scripts/upgrade/database.php
```

### New configuration options

The release notes will mention the new conifg.php key items and they
should have associated documentation in the
[configuration](https://docs.filesender.org/filesender/v2.0/admin/configuration/)
page.

### Test and enjoy!

Thank you for using FileSender. If there are issues with this document
or things that could be more clear please let us know. These
documentation files are in the github repository if you would like to
contribute documentation updates.


## Issues and Bugs

Please inspect and report bugs on the [GitHub Issue
Tracker](https://github.com/filesender/filesender/issues)

