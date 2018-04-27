#!/bin/bash
set -x
# psql -c 'SELECT version();' -U postgres
export PGPASSWORD=password

psql -c 'create database filesender;'     -h db-host   -U postgres
psql -U postgres -h db-host -c "alter user postgres with password 'password';"
php ./scripts/upgrade/database.php;

psql -c 'create database filesenderdataset;' -h db-host -U postgres
bzcat ./scripts/dataset/dumps/filesender-2.0beta1.pg.bz2 | psql -h db-host -U postgres -d filesenderdataset 
