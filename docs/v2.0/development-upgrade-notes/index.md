---
title: Development upgrade notes
---

# Development upgrade notes

Development notes on all changes that happened in version 2.0. These
will be taken as input for the upgrade notes which will be the
condensed version.

## Upgrading from v2.0 alpha to v2.0 beta2

You should already have Apache and your choice of database (PostgreSQL
or MariaDB etc). If you are going to bring the same database forward
it is recommended that you take a snapshot of the database (pg_dump,
mysqldump etc) before proceeding. The installation notes would have
installed FileSender into the directory tree at /opt/filesender.

What you'll need to do to upgrade is:

* Backup your database
* Backup your filesender installation, especially if you've made local modifications to pages or code
* Install the v2.0 beta code from packages or by unpacking the tarball
* Run the database migration script to update anything and create new database indexes
  for a performance boost
* Install your config.php into your new FileSender, for example to, /opt/filesender/config/config.php

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

### Install the v2.0 beta code

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


## Upgrading from 1.6 or older

WARNING: These are very old instructions you might like to consider a
fresh install if moving from a version prior to the v2.0 alpha/beta
releases.

### Database handling

* The 2.0 database initialisation script populates the filesender database with tables based on the class definitions.  This means the database initialisation script and FileSender can now detect whether the database contains the appropriate tables and fields.  This also means you do the database initialisation after the FileSender configuration

### Config directives that no longer exist

* customCSS: overrides in skin directory.  Override by add default.css or style.css in skin directory.  In order to lib_  jquery_ui.css (font awesome), css_default.css, skin_styles.css.  Have to create a styles.css under skin directory. (can be named otherwise).  In future can also add scripts.js to skin directory to allow for local extra javascripts.  If there is a logo.png file in skin directory it will be used as backdrop.  Otherwise use logo file in image directory.  Allows customise filesender and not lose things when update.  Document purpose of skin directory.  And need to document templates.
* datedisplayformat: in language files
* upload_box_default_size (was available in 2.0 prototype summer 2013): done in CSS, override CSS in skin directory.
* displayerrors (available since 1.0?): error mechanism was overhauled.
* dnslookup (available since 1.0): in audit logs etc. are logging IP-address.  We'll see if someone asks for this.
* client_specific_logging (available since 1.0): error mechanism was overhauled.
* client_specific_logging_uids (available since 1.0): error mechanism was overhauled
* db_dateformat: in language file
* crlf
* voucherRegEx
* voucherUIDLength
* openSSLKeyLength (introduced in 2.0 summer 2013 prototype)
* emailRegEx
* webWorkersLimit (since 1.6)
* auth_sp_saml_simplesamlphp_url
* site_filestore:
* site_tempfilestore: use different way of storing files
* log_location
* cron_exclude_prefix
* cron_cleanuptempdays
* filestorage_filesystem_file_location (2.0 prototype?)
* filestorage_filesystem_temp_location (2.0 prototype?)
* statlog_enable (prototype?): built-in in lifetime parameter
* auditlog_enable (prototype?): built-in in lifetime parameter
* max_flash_upload_size: the Flash upload component is now removed.  To present a reliable progress bar to a user, functionality that was first available in PHP 5.4 is required (which functionality?)
