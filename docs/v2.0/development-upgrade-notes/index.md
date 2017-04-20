---
title: Development upgrade notes
---

Development notes on all changes that happened in version 2.0.  These will be taken as input for the upgrade notes which will be the condensed version.

# Database handling

* The 2.0 database initialisation script populates the filesender database with tables based on the class definitions.  This means the database initialisation script and FileSender can now detect whether the database contains the appropriate tables and fields.  This also means you do the database initialisation after the FileSender configuration

# Config directives that no longer exist

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
