---
title: FileSender Documentation
---

## FileSender Roadmap 

  

Filesender is currently available as a v2 Beta release and the team is working towards a final stable release of the v2 software.  A full set of the features being developed or implemented in the v2 software is available [here](http://docs.filesender.org/v2.0/). 

The Filesender team is starting to collate items for roadmap development beyond Filesenderv2.0. Further features are entirely dependent on the funding that the project can attract.  To find out more about how you can donate to the project visit the Filesender Programme pages at the [Commons Conservancy](https://commonsconservancy.org/programmes/). 

Current priorities for the post 2.0 Filesender Roadmap are:

### Priority 1

* Bugfix release
* Object-store storage
* Complete automated CI tests
* Translation portal
* Packaging and documentation


### Priority 2

* Smooth UI
* Statistics
* Download link protection
* Improve TeraSender speed and robustness
* Address-book: link with group info sources


## Community Proposals

These are specific things that might help the project in the future.
They are not "officially" on the roadmap but the ideas seem
interesting and in the interest of being an open project the ideas are
freely contributed here for anybody to see, take an interest in,
implement, suggest better ideas etc.

### app

It might be useful to start with a "share" type app allowing images,
video, etc to be uploaded to filesender instead of other third party
platforms. This would help allow researchers to keep all files on
filesender instead of using a mixture of platforms in order to use
phones and laptops for information gathering.

### more admin functionality

Such as delete transactions, or "become a user" for a while.

### GDPR compliance and privacy footprint

GDPR compliance test first, see what needs to be adapted, make
FileSender GDPR compliant by default using default config.

### OAuth (REST client and integration with other applications!)

todo: expand this paragraph into real text.

### db compaction

MySQL style databases in some configurations can only index 190 byte
varchar columns. Secondary indexes provide large performance gains but
can also only be assumed to work on 190 varchar columns in some MySQL
installations. There are many columns that are 255 "bytes" long which
might be useful to index. The task is to examine those to try to
work out which ones can safely be truncated to 190 bytes. Also,
instead of directly stipulating the metadata (length) in the class the
code will be extended to allow classes to be used to stipulate that.

So an email column definition might go from something like the
following where the database code will lookup the size of
FileSenderEmail in a constant class instead of finding it directly.
This will also help possible future referential integrity updates and
constraints.

```
        'id' => array( 'type' => 'string', 'size' => 190, ),
to
        'id' => array( 'type' => 'FileSenderEmail', ),
```

Indexing and possible schema changes (numbers for fixed values or
string etc.). Currently VARCHAR 256, easily blows up to 1KB to store
10 bytes of string, with 100.000 tuples that slows things down.

### db views

There are many cases where database views would help. Having good
views moves common SQL query code into the view definition, making
queries simpler to write and allowing the view definition to be
updated in one place if issues are found with a specific relational
database or if the performance of a query can be tuned. A recent
example of this is trying to work out of a transfer was encrypted,
because that information is stored in a text field an SQL query must
try to string match that field in a way that will work across all
database backends. A view would offer a column "is-encrypted" which
allows that to be checked more easily from any query.

While the database views may be backed by complicated SQL in this
release, having the views allows the schema to be changed such as with
the "db compaction" task without breaking the queries that use
the view. The view definition is updated in one place and all queries
using that view will then work with the new database schema.

This is closely related to the "db compaction" task. The views
become more useful when they can join lookup tables from integer
values such as they can when "db compaction" is implemented.

User database and auth against local database The ability to create
and admin user login and password in the local database instead of
through SAML.

### db query refresh

Rewrite the core query behind the AuditLog fromTransfer() to use more
server side SQL instead of fetching ID values to the php code and
using SQL that is not so efficient for evaluation.

https://github.com/filesender/filesender/blob/master/classes/data/AuditLog.class.php#L295

Using the synthetic dataset check performance for common operations
and add secondary indexes where they can help performance.


### SAML config examples

SAML examples for small deployments. This includes adding an option in
FileSender to be able to update the password when using saml auth that
stores the password in the database. This password update code would
be part of FileSender, to be optionially enabled via a config option
and allow the file Sender web interface to update the user password
stored in the same filesender database.

https://github.com/filesender/filesender/issues/100


### Travis CI

For FileSender 2.0 we should consider PHP 5.6 to be the minimum
version. This will mean the CI can be moved to that version and use
the more recent Ubuntu operating system to run the CI. This is
starting to become a problem as travis CI has moved to more recent
operaitng systems that no longer offer php 5.3. Some of the current
selenium tests fail when executed in php 5.6 and must be updated.

Currently the CI executes exclusively on a postgresql database. It
would be useful to perform on both mysql and postgresql to catch SQL
query issues specific to either.

Widen the scope of the php that is tested by CI

Add more selenium tests.

Which PHP versions are in stock Debian + Red Hat?
```
  Fedora 24 is php 5.6.31
  Fedora 26 is php 7.1 
  Debian Stretch is php 7.0 https://packages.debian.org/stretch/php
```

Add Travis work, Update Travis to newer Ubuntu to newer php, Fix
Selenium tests, More selenium tests!

### Better documentation and email handling

Clearly documentation is an issue for this. It seems that the bounce
handling is a great example of an area that documentation can still
improve. The current http://docs.filesender.org/v2.0/
admin/configuration/ page still has much orange "to be checked" areas
relating to email.

It seems that the cited bounce script is this one scripts/task/
emailfeedback.php. The emailfeedback.php script is looking at incoming
emails and acting on content type message/delivery-status containing
'failed' as bounces.

At any rate the documentation shouldn't be leaving folks in a place that they 
have to search around to work out where the bounce related script is.

So to the the ball rolling there needs to be a new "email handling" page to 
document things we want bounce handling on there and SPF so that folks do not 
have to get to this thread for that.

What are the other email related things that are the most important for 
FileSender to start doing? From previous email we have: DMARC, SPF, SRS.

### Command line tools

Basic command line tool(s) that make using FileSender scriptable.
Check what Owncloud / Nextcloud etc have for this and consider maybe
FUSE or coreutils clones. It could be quite useful in many a
scenario.

### Local Area Network FileSender

It might be interesting to have an 'Local Area Network FileSender'
capability, such as offered by https://github.com/warner/magic-wormhole

magic-wormhole uses really cool and unusual end-to-end crypto to send unlimited size
files to people after a real-time magic keyword is shared, does local
discovery etc.

### organisation slide show

Ability to show a slide show with cool stuff an organisation hosting filesender has in
the pipeline w.r.t. new services while downloading (e.g. similar to
WeTransfer). Could also be used to showcase cool open source stuff from
the outside, charities, the coolest scientific imagery of the day, etc.
People are waiting, they can get an 'experience' for free. FileSender
should be fun too ;)

### Theme emails

better themability of the emails sent out. Perhaps have a designer look
at freshening up the templates.

### accessibility optimisations

### chat

integrate a chat option when sender is still online while receiver
goes to pick up their file.

### quick preview

prioritize download of specific chunks of zip files etc.
Perhaps include previews of files?

### best practices badge

Have a look at the following to see if there are best practices not
yet followed.

https://github.com/coreinfrastructure/best-practices-badge/blob/master/doc/criteria.md

