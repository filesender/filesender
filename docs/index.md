---
title: FileSender Documentation
---

# The problem

You know the problem.  You need to send a file larger than a couple of megabytes to someone and the email comes back: attachment too big.  Then what?

# FileSender project

FileSender is a web based application that allows authenticated users to securely and easily send arbitrarily large files to other users. Authentication of users is provided through [SimpleSAMLphp, supporting SAML2, LDAP and RADIUS and more](http://simplesamlphp.org/docs/stable/simplesamlphp-idp#section_2). Users without an account can be sent an upload voucher by an authenticated user. FileSender is developed to the requirements of the higher education and research community.

The purpose of the software is to send a large file to someone, have that file available for download for a certain number of downloads and/or a certain amount of time, and after that automatically delete the file. The software is not intended as a permanent file publishing platform.

## License

FileSender is released under the [BSD license](http://opensource.org/licenses/BSD-3-Clause). It is open source software and available for free.

## Availability and download

The latest production release is [1.6.1, released on December 30th 2015](https://app.assembla.com/wiki/show/file_sender/Release_1-6-1). The previous major release [1.5, released on March 3rd 2013](https://app.assembla.com/wiki/show/file_sender/Release_1-5) is still supported. Releases [1.1.1, released on May 31st 2012](https://app.assembla.com/wiki/show/file_sender/Release_1-1-1) using a Flash based UI for large uploads and [1.0.1, released on 18 May 2011](https://app.assembla.com/wiki/show/file_sender/Release_1-0-1) using the deprecated Gears plugin are not supported anymore. Visit the [Download page](https://app.assembla.com/wiki/show/file_sender/Download) for details about the general availability of the FileSender software.

## Development status

The version currently under development is version 2.0. The alpha version can be installed from Git, see [the documentation for 2.0](v2.0/install/) for details. Check the [Release Schedule](https://app.assembla.com/wiki/show/file_sender/Release_Schedule) for the planning.

## Blog

The FileSender project has a [Blog](https://app.assembla.com/spaces/file_sender/wiki/Blog_and_News), where news and status updates are posted.

## Mailing Lists

The FileSender project uses a number of mailinglists to support people deploying FileSender software and to coordinate development. Please go to the [Support and Mailinglists](https://app.assembla.com/wiki/show/file_sender/Support_and_Mailinglists) page and subscribe yourself to those lists relevant for you.

## Feature Requests

Go to [Feature requests](https://app.assembla.com/wiki/show/file_sender/Feature_requests) if you have a feature you would like to see added to FileSender.

## Features

A snapshot of features for the latest 1.6(.x) release is located at [Features](https://app.assembla.com/wiki/show/file_sender/Features). A snapshot of features for the previous 1.1(.x) release is located at [Features_v1-1](https://app.assembla.com/wiki/show/file_sender/Features_v1-1).

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

FileSender's functionality is also described in [End_User_Documentation_for_v1-6](https://app.assembla.com/wiki/show/file_sender/End_User_Documentation_for_v1-6) snapshot of features for the previous 1.1(.x) release at [End_User_Documentation_for_v1-1](https://app.assembla.com/wiki/show/file_sender/End_User_Documentation_for_v1-1) and [Specifications](https://app.assembla.com/wiki/show/file_sender/Specifications).

## Requirements

A snapshot of requirements for the latest 1.6(.x) release is located at [Requirements](https://app.assembla.com/wiki/show/file_sender/Requirements). A snapshot of requirements for the previous 1.1(.x) release is located at [Requirements_for_v1-1](https://app.assembla.com/wiki/show/file_sender/Requirements_for_v1-1).

## Documentation

Installation and administrator documentation for the latest 1.6(.x) release is provided at [Documentation_v1-6](https://app.assembla.com/wiki/show/file_sender/Documentation_v1-6) (documentation for the previous 1.1(.x) release at [Documentation_v1-1](https://app.assembla.com/wiki/show/file_sender/Documentation_v1-1)).

## Developer

Development documentation is provided in the [Developer section](https://app.assembla.com/wiki/show/file_sender/Developer).
