---
title: FileSender Documentation
---

## FileSender project

FileSender is a web based application that allows authenticated users
to securely and easily send arbitrarily large files to other users.
Authentication of users is provided through [SimpleSAMLphp, supporting
SAML2, LDAP and RADIUS and
more](http://simplesamlphp.org/docs/stable/simplesamlphp-idp#section_2).
Users without an account can be sent an upload voucher by an
authenticated user. FileSender is developed to the requirements of the
higher education and research community.

The purpose of the software is to send a large file to someone, have
that file available for download for a certain number of downloads
and/or a certain amount of time, and after that automatically delete
the file. The software is not intended as a permanent file publishing
platform.

This is the home for Filesender documentation. For more information
about the project [please visit our homepage](http://filesender.org).

### Which version should you choose

Version 2.2 has been released in August 2018 and is the recommended
choice.

Following the 2.0 release there will be subsequent releases in the
pattern 2.1, 2.2, 2.30 etc. Each of these releases will build on
version 2.0 adding bugfixes and features. It is planend that you can
migrate from 2.0 upwards in the 2.x series.

The previos production release is [1.6.1, released on December 30th
2015](https://downloads.filesender.org/filesender-1.6.1.tar.gz). 

### Documentation

Please see the [documentation for versions 2.x](http://docs.filesender.org/v2.0/).

### License

FileSender is released under the [BSD
license](http://opensource.org/licenses/BSD-3-Clause). It is open
source software and available for free.

### Availability and download

Visit the [Releases
page](https://github.com/filesender/filesender/releases) for details
about the general availability of the FileSender software.

### Feature Requests

Go to the [Issues](https://github.com/filesender/filesender/issues)
page if you have a feature you would like to see added to FileSender.

### Features

Version 2.0 features are [described here](v2.0/features/).

A snapshot of features for the latest 1.6(.x) release is located at [Features](v1.6/features). 

* light-weight server footprint, optimized for least possible dependencies
* share arbitrarily large files from standard desktop environments, no client-side deployment required
* Native HTML5 and JavaScript UI with supported browsers. No plugins required.
* High-speed upload module with HTML5 uploads
* Graceful fallback to invisible Flash component for non-HTML5 browsers, allowing uploads up to 2GB
* integrates with various authentication mechanisms using SimpleSAMLphp (SAML2, RADIUS, LDAP)
* upload guest vouchers to allow users without an account to upload a file
* cancel / resume file uploads using the HTML5 File API in supported browsers
* download files multiple times, from link with built-in password in auto-generated email, or directly from interface by authenticated user
* automatic deletion of shared files and issued vouchers after X amount of time, or manual deletion by authenticated user any time prior to expiry
* email notification each time a file is uploaded, downloaded or manually deleted, or a voucher is issued or manually deleted
* MyFiles provides overview lists of currently shared files * Overview list of unused issued guest vouchers
* resend download link emails to file recipients without re-uploading the file * add additional recipients to already uploaded files
* UTF8 support, supports all international character sets
* Multi-language support. Out-of-the-box FileSender 1.6 supports Czech, Croatian, Dutch, English (Australian), Finnish, French, German, Hungarian, Italian, Norwegian (Bokmål), Serbian, Slovenian and Spanish. You can easily adapt relevant language labels to your local needs in an upgrade-friendly way, for example to localise the splash screen text. You can also easily modify which languages you make available to your users
* PDO-based multi-database support for PostgreSQL, MySQL and sqlite



### Requirements

Some storage, either MariaDB or PostgreSQL for database, either Apache
or nginx for web server and SimpleSamlPhp.


