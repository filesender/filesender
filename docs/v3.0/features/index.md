---
title: Detailed feature list
---

# Version 3.0 detailed feature list (draft)

This document lists most of the features currently implemented in the 3.0-beta1 code.  Please note that while most of the features are stable, some features may disappear or change.

## User visible features

### Multi-file transfer of any size

* Multi-file select using either the select button or drag & drop anywhere on the tab
* Multi-file upload in one single transfer.  Server-side it can be specified whether files are uploaded serially or in parallel (TeraSender upload module only)
* Fire and forget: once you start uploading a transfer it'll finish even if the IdP times out your session
* Upload resume (requires manual file re-selection)
* Multi-file download
	* Download all or a selection of files, streamed into to a non-compressed .zip file that will open without external utilities in the file explorer on Windows and Mac.
	* Download individual files
	* Support for download pause/resume
	* Can detect whether a file was actually downloaded (but for the last download chunk).   This also means download completed emails are now sent once the file is actually downloaded, not when the download starts.
	* Show a warning when downloading (to) a file > 2GB on Mac that you need a different unarchiver.
* Automatic deletion of transfers


### Easy to use UI

* Default and Max days valid
* Auto-complete
* User preferences
	* Can save preferred language
* Service configurable speed units (bits or bytes per second)

### Email receipts with full control

* Email receipt options:
	* No emails at all, just present a download link in the UI after uploading the transfer

* Translation URL included in emails, when user clicks on it the email is shown in another language on the FileSender service URL
* The transfer sender can specify an optional subject and/or message for inclusion with the transfer available email sent to the transfer recipient

### My Transfers

* Overview of transfers, both in flight and closed
* For each transfer:
	* Re-send download link email
	* Add recipients
	* Delete recipients
	* Extend availability period
	* Access audit trail

### Audit trail for each transfer

* Each significant action (transfer complete, download etc.) for a transfer is logged in a per-transfer audit trail
* The audit trail for a transfer can be accessed via My Transfers
* The audit trail can be received by email as HTML email or PDF.
* The audit trail email can be sent automatically on transfer expiry or accessed manually via My Files (is this true?).

### Guest access

* Guest access vouchers can now be made available for:
	* Valid only one time
	* Unlimited amount of time
	* Limited to send only to user who created the guest access voucher
* Users can be informed when their guest starts an upload or accesses the upload page
* The user creating the guest access voucher can specify which transfer options the guest has available
* The user can include an optional subject and/or message with the guest access voucher

### Language and internationalisation

* Full UTF8 support, supports all international character sets
* User language selection options:
	* Automatically based on the user's preferred language as configured in the client browser.  Communicated through the browser's Accept-Language header
	* Explicit by selecting a language from the language drop down menu in the menu bar
	* Explicit via the lang URL parameter
	* If a preferred/selected language is not available, serve what is specified in the default language configuration parameter
	* If no translations exist, use the hard coded default "en"
* Translated emails
	* Each email has a link which when clicked directs the user to a translation page on the FileSender service instance where another language can be chosen for displaying the email.

## Under the hood

### Privacy by design

* Logging to database separated in audit logging per transfer and anonymised logging for statistical purposes
* Configurable lifetime for audit logging
* Audit report can automatically be sent to transfer owner on transfer expiry after which the audit logging can be deleted.  In this scenario the sensitive data is kept exactly as long as it is needed.
* Support for logging additional user account parameters in the statlog, e.g. an organisation identifier allowing statistics per customer
* Configurable enforcing of in-flight encryption of transfers (SSL)

### Transfers

* Generalised transfer option mechanism
	* For each option you can configure whether that option is:
		* always visible or accessible under an "Advanced" click-down menu
		* switched on by default
		* user changeable or not
	* Currently implemented transfer options include full email control, audit report and direct link.
* Configurable maximums for transfers:
	* Total transfer size
	* Number of files
	* Number of recipients
* User quota
* Acceptable Use Policy tickbox with configurable options:
	* Mandatory to tick before transfer can commence
	* Ticked by default
	* Absent from UI

### TeraSender high speed upload module

* Improved robustness (needs details)
* Can be switched on and off (default on)
* Server-side configurable worker count
* Server-side configurable whether the user can specify the worker count in the upload form.  This is useful for those specific frequent very large file use cases where you really want to optimise the upload speed.  The user('s organisation) can then figure out what the best worker count for that particular setting is without burdening the FileSender service staff.

### Authentication

* Externalised authentication
* Built-in support for Shibboleth and SimpleSAMLphp authentication libraries
* In practice this means SAML2, LDAP, RADIUS, Active Directory, Facebook, etc.

### Multi-database support

* PDO-based multi-database support
* Supports MySQL and PostgreSQL
* Database upgrade script: database definitions generated from what the classes specify is needed
* Configurable database prefix, useful when running multiple FileSender instances on the same database

### RESTful Webservice API

* System administration support
* Force legacy upload mode for testing purposes
* Maintenance mode allows to do quick maintenance without killing active transfers.  Active transfers are warned and will wait until the service is available again
* Support for different types of back-end storage.  Currently implemented is filesystem support.   Extending this to for example object storage is now possible.
* Service logging to file or syslog
* Configurable storage usage warning by email
* Configurable file deletion command

### Email handling

* Configurable email_from, email_from_name, email_reply_to, email_reply_to_name, email_return_path
* Automatic email bounce handling (note: field tested by RENATER in production, not tested elsewhere)
* Configurable SPF compliant behaviour
