
The scripts/dataset directory contains a script to use the regular
FileSender code to create a reasonable sized database of 'test data'.
The database is designed to be of a size similar to one of the running
deployments of FileSender to allow performance issues to be examined
and improved.

The dumps directory contains both MariaDB and PostgreSQL database
dumps made with specific versions of FileSender. This allows database
schemas to evolve and also investigation into possible migration
issues that folks might have between FileSender versions.

The dataset ensures that 10,000 users and 3,000 guests exist in the
database. 

Most transfers have only a single file associated but a distribution
is created which is designed to allow expoloration of multi file transfer
performance.

30,000  transfers with 1 file
 3,000  transfers with 2 files
 1,000  transfers with 3 files
   500  transfers with 4 files
   300  transfers with 5 files
   200  transfers with 6 files
    ...
    30  transfers with 9 files

To create AuditLog table entries some additional activity is performed
on each transfer. For each transfer, a file will be downloaded up to
around 7 times and for half the transfers the archive of the transfer
is downloaded. About half the transfers are expired to represent a
running installation.

This gives around 350k AuditLogs for File type, and 230k logs for
Transfer type. The File auditlogs are split into 45k file uploaded
logs (total of file distribution above), and 150k download started and
ended logs. The transfer auditlogs include around 35k each for
transfer_started, upload_started, upload_ended, transfer_available,
and transfer_sent. Since half the transfers are downloaded as archive
and closed there are around 17.5k entries for
archive_download_started, archive_download_ended, and
transfer_expired.

Most transfers have two recipients with around half that amount having
a single recipient. There is a minor taper off for 3 and 4 recipients
on a transfer to add a little diversity to the database. 

MariaDB [filesender]> select min(c) as recip_per_transfer,count(*) as count
  from (select count(*) as c from Recipients group by transfer_id) sub group by c;
+--------------------+-------+
| recip_per_transfer | count |
+--------------------+-------+
|                  1 | 11086 |
|                  2 | 19892 |
|                  3 |  3501 |
|                  4 |   702 |
+--------------------+-------+

The actual sending of email is forced off by the database creator
script. This does not disable FileSender from keeping entries in the
TranslatableEmails table for emails that would have been sent. Each
transfer that is performed generates a transfer_available email, with
many transfers being marked as expired generating transfer_expired
message(s).

select count(*) as c,translation_id from
 TranslatableEmails group by translation_id order by c desc;
+-------+--------------------------+
| c     | translation_id           |
+-------+--------------------------+
| 64180 | transfer_available       |
| 35181 | upload_complete          |
| 32089 | transfer_expired         |
| 17590 | transfer_expired_receipt |
| 17590 | report_inline            |
|  3000 | guest_created            |
|  1500 | guest_created_receipt    |
+-------+--------------------------+

Creation of the dataset can be time consuming, on the order of many hours.
For this reason exports of the created database have been created to
allow people to import the dataset for testing. These are in the dumps
directory and have been created with the commands shown below.

DB setup
--------

If you are using PostgreSQL you might consider running an instance that could
sustain some loss of data in power out in order to gain a huge performance boost
for running the dataset creator by setting the following. Note that you shouldn't
do this on a production machine, we can only really do it here because we are creating
synthetic data so worst case could erase the database after a power failure and
recreate it.

# postgresql.conf
fsync = off
synchronous_commit = off



To run the data creator
php /opt/filesender/scripts/dataset/create.php



You might also like to use the --scale parameter to generate between
1.0 (100%) and 0.01 (1%) of the dataset as the full generation can take
hours to complete.

To export a created MySQL/MariaDB database
mysqldump -p --user filesender --databases filesender > dumps/filesender-version-me.mysqldump

To export a created PostgreSQL database
pg_dump --no-owner -U filesender filesender | bzip2 -9 > filesender-version-me.pg.bz2
