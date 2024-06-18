#Upgrade notes from version 1.6 to version 2.0

These upgrade notes are a work in progress and may not be complete and/or contain errors.

Version 2.0 is a new base line release that is __NOT backwards compatible__ with versions 1.x.

#What changed?
* Both the database and the class model have been redesigned from scratch to support
new functionality in a future-proof and modular way.  Many features were added.  This
means much of the code was rewritten.
* The database design was moved from two flat tables to a relational design.
* There are many changes to the configuration directives.  New features required new
directives, several directives were consolidated and many were renamed to make
naming more consistent.
* The way you make your own local adaptions to language files has changed
* Multi-language email templates are now supported.  If you adapt language files
don't forget to have a look at the email templates for those languages you support on your
site.


#The upgrade process summarised
There is no defined upgrade path from version 1.x to version 2.0 that allows you to 
keep your version 1.x database.  Should anyone create this upgrade path please
share it with the community.

The currently envisioned upgrade path for those with an existing 1.x based service is:
* install an instance of version 2.0 with its own database.  This is where new transfers
go after you run this instance on your production FileSender URL.
* keep your 1.6 installation available on a different URL, for example
yourfilesender.yoursite.dom/old for at least "max expiry time".

You can remove the /old instance after all files that were available at the time of
the URL switch have timed out.


   * install a new instance of version 2.0 with its own database on a URL different 
   from your production site.  Test it, for example by making it available as a beta 
   service to your community. 
   * when the time to switch your 2.0 instance to production on your well-known
   FileSender service URL, there is no permanently stored information in FileSender. While files are in transit
(available for download) there are two things to consider: the download URLs of available
files need to work, and there is a possibility users want to use MyFiles functionality
on those files (delete file, resend email etc.)

Two ways to resolve:
* patch code that generates download file to include the "future" URL
* create a rewrite rule that rewrites 1.6 URLs to the "future" URL

The latter has not been tested but given the difference in download URLs this should be possible:
https://filesender.uninett.no/?vid=4ccc90a0-4d6b-d3e8-9bdf-000068cb9103
https://terasender.uninett.no/branches/filesender-2.0/?s=download&token=c08aca74-2b41-05ed-9724-ff6cdf570874   
===

#Changes in configuration directives

##Changed defaults in 2.0
The following defaults changed from version 1.x to 2.0
* __email_newline__ now defaults to "\r\n", before this was "\n"
* __terasender_enabled__ now defaults to "true", before "false"

##Changed configuration directives in 2.0
Many new configuration directives were added and quite a number were changed.

* __webWorkersLimit__: renamed to terasender_worker_count.  
before you could launch several workers and each worker would request jobs.  
There were # jobs per worker.  
Testing showed having more than 1 job per worker gained nothing.  
When you have browser process (tab in chrome) and doing async stuff
(launch ajax request) get time to do other things.  This was not way 
workers were thought to behave.  Worker is not efficient when doing async stuff.  
Several jobs per worker = async.  Theory: several jobs per worker can mean that 
when one job sends blob, other job can fetch data.  No significant gain observed.  
Code was more complex so simplified.


##Obsoleted configuration directives in 2.0
* __cron_shred__: use the storage_filesystem_file_deletion_command to specify which 
command to use for deleting files.
* __debug__: you can now use log_facilities to set a log level.
* __max_email_recipients__: in 1.x this was the same value for both file transfers and
guest invitation vouchers.  This is now split in to max_transfer_recipients and 
max_guest_recipients
* __terasender_chunksize__: this is consolidated in the upload chunk size which is used
for both terasender and non-terasender upload.
* __terasender_jobsPerWorker__: this directive was removed as it didn't have practical
relevance to performance.
* __crlf__: there is now a constant specifying this. This parameter needed to be
configurable in the past when especially OUtlook used a different newline format.  This
is no longer an issue.
* __voucherRegEx__: was used for generating unique IDs.  The algorithm for doing this
is now hardcoded in the utilities class.  The reasoning is that changing the algorithm
for generating unique IDs is a Bad Idea: the uniqueness of IDs is key to the security
of file transfers, you don't want to enable shoot-yourself-in-the-foot by making the
algorithm configurable.  
* __emailRegEx__: was used for validating syntactical correctness of email addresses.  
As of 2.0 the code uses the PHP built-in facility for checking email address validity 
which these days works well.  The PHPfunction used is filter_var with the 
filter FILTER_VALIDATE_EMAIL.  
