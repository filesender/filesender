---
title: Development upgrade notes
---

## Upgrading from v2.x to v3.x

You should already have Apache and your choice of database (PostgreSQL
or MariaDB etc). If you are going to bring the same database forward
it is recommended that you take a snapshot of the database (pg_dump,
mysqldump etc) before proceeding. The installation notes would have
installed FileSender into the directory tree at /opt/filesender.

What you'll need to do to upgrade is:

* Backup your database
* Backup your filesender installation, especially if you've made local modifications to pages or code
* Move away the classes, templates, and www directories for 2.x series to avoid orphaned code 
* Install the v3.0 code from packages or by unpacking the tarball.
* Run the database migration script to update anything and create new database indexes
  for a performance boost
* Install your config.php into your new FileSender, for example to, /opt/filesender/config/config.php
* Check for updated configuration variables in config.php

### Backup your database

You will likely want to use pg_dump or mysqldump for this step.

### Backup your filesender installation

For example using one of the following. Note that filesender/logs and
filesender/files might be very large directories so you might like to
omit them from the backup as shown below. This should result in an
archive that is in the order of 50Mb in size.

```
cd /opt/
tar -czvf ~/filesender-old.tar.gz \
   --exclude files \
   --exclude .git  \
   --exclude logs  \
   --dereference   \
   filesender
```

The below is a more complete backup but might also consume a lot of
disk space to backup each file that has been uploaded to FileSender.
So depending on what the du command gives you then you might be
consuming a bunch of disk space for the backup.

```
cd /opt/
du -sh ./filesender/files
cp -avL filesender filesender-2.0alpha
```

### Move away the classes, templates, and www directories for 2.x series to avoid orphaned code 

```
cd /opt/filesender
mkdir ~/filesender-2.x
mv classes templates www ~/filesender-2.x
```

### Install the v3.0 code

This should follow the directions for installing from the [install page](../install/).

### Run the database migration script

The following command should update the database as needed.

```
php /opt/filesender/scripts/upgrade/database.php
```

### Install your config.php into your new FileSender

The old config shouldn't have been replaced by the above, but making
it explicit can't be a bad thing.
For example, using

```
cd /opt/
cp -avL filesender-2.0alpha/config/config.php filesender/config/config.php 
```



### Database handling

* The 3.0 database initialisation script populates the filesender
  database with tables based on the class definitions. This means the
  database initialisation script and FileSender can now detect whether
  the database contains the appropriate tables and fields. This also
  means you do the database initialisation after the FileSender
  configuration

### Config directives that no longer exist in 3.0

FIXME
