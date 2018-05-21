---
title: FileSender Documentation
---

## FileSender project

FileSender is a web based application that allows authenticated users to securely and easily send arbitrarily large files to other users. Authentication of users is provided through [SimpleSAMLphp, supporting SAML2, LDAP and RADIUS and more](http://simplesamlphp.org/docs/stable/simplesamlphp-idp#section_2). Users without an account can be sent an upload voucher by an authenticated user. FileSender is developed to the requirements of the higher education and research community.

The purpose of the software is to send a large file to someone, have that file available for download for a certain number of downloads and/or a certain amount of time, and after that automatically delete the file. The software is not intended as a permanent file publishing platform.

This is the home for Filesender documentation.  For more information about the project [please visit our homepage](http://filesender.org).

### Which version should you choose

The latest production release is [1.6.1, released on December 30th
2015](https://downloads.filesender.org/filesender-1.6.1.tar.gz). There
have been a series of beta releases for version 2.0 the most recent
being [rc1, released on 21th May
2018](https://github.com/filesender/filesender/releases). You are
encouraged to use the latest 2.0 beta release if possible as migration to later
beta releases and the final 2.0 release will be much simpler.

### Documentation

If you are looking to install and use the v2.0 beta start looking at the [documentation for 2.0](http://docs.filesender.org/v2.0/).

FileSender version 1.6 documentation is being migrated to github [here](http://docs.filesender.org/v1.6/).

### License

FileSender is released under the [BSD license](http://opensource.org/licenses/BSD-3-Clause). It is open source software and available for free.

### Availability and download

The latest production release is [1.6.1, released on December 30th 2015](https://downloads.filesender.org/filesender-1.6.1.tar.gz). The previous major release [1.5, released on March 3rd 2013](https://downloads.filesender.org/filesender-1.5.tar.gz) is still supported.

Visit the [Releases page](https://github.com/filesender/filesender/releases) for details about the general availability of the FileSender software.

### Development status

The version currently under development is version 2.0. A series of
beta releases have been made and you are encouraged to use the latest
beta if possible. See [the documentation for 2.0](v2.0/install/) for
details. 


### Feature Requests

Go to the [Issues](https://github.com/filesender/filesender/issues) page if you have a feature you would like to see added to FileSender.

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

A snapshot of requirements for the latest 1.6(.x) release is located at [Requirements](v1.6/requirements). 


