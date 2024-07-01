---
title: FileSender Shredding deleted files
---

# FileSender Shredding deleted files

FileSender 3.0 can execute shred or other long running tasks to
securely delete user files when they are discarded by the user.

The problem with shredding files right at the time the user deletes a
transfer is that a secure shredding on a large file may take a very
long time. A user may not be interested in waiting for this shredding
to complete before continuing to use the FileSender Web interface.

If you wish to run shred at the time a user deletes a file simply
continue to use shred in the storage_filesystem_file_deletion_command
configuration directive. Otherwise, the new
storage_filesystem_file_shred_command can be set which will also
enable the new shredding functionality.

If you are using this shredding support, when a user deletes a
transfer files are renamed into the directory specified by the
storage_filesystem_shred_path configuration directive. For best, and
most secure, results you should have storage_filesystem_shred_path on
the same filesystem as storage_filesystem_path. On security grounds,
the code may reject configurations where these two paths are not on
the same filesystem in the future.

Files that are moved to storage_filesystem_shred_path have a new file
name and are inserted into the shredfiles table in the database. That
table has a synthetic tuple id, the file name, and an optional
errormessage field. To actually shred the files you should arrange to
execute the scripts/task/cron-handle-shred-files.php script on a
regular basis. This script will perform the actual shredding of the
files and remove them from the database after a successful shred.

If shredding failed then the output of
storage_filesystem_file_shred_command is inserted into the
errormessage field to help the system administrator to track down the
problem. Each time your run the cron-handle-shred-files.php script
every file in the shredfiles table will be attempted to be shredded.
So if you have had a problem shredding a file and have changed things
such as file permissions to fix the problem simply rerun the
cron-handle-shred-files.php script and an attempt will be made to
shred all files in the shredfiles table.

## Configuration directives of interest

* storage_filesystem_file_shred_command
* storage_filesystem_shred_path
* storage_filesystem_path (Note: should be on same filesystem as storage_filesystem_shred_path).


