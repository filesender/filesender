The main script you are interested in is the database.php file.

To upgrade your FileSender database use the database.php script. Note
that the script may need the DROP permission for MariaDB databases as
database views are dropped at the start of the script and recreated at
the end. See the MariaDB section of the [install
guide](https://docs.filesender.org/filesender/v2.0/install/#option-b---mysql)
for more information.

```
$ cd /opt/filesender/filesender/scripts/upgrade
$ php database.php
```

The database.php script will create new columns in tables as needed
and drop and remake the database views to the current specification.
It should not be a problem to run the database.php script many times
on an installation. If no columns have changed and no new tables are
created then only the views will be remade which is a fairly quick
operation.

As is generally good administration practice, it is recommended to
have a backup of the database.
