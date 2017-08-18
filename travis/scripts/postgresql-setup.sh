#!/bin/bash
set -ev
# psql -c 'SELECT version();' -U postgres
psql -c 'create database filesender;'        -U postgres
psql -U postgres -c "alter user postgres with password 'password';"
php ./scripts/upgrade/database.php;

psql -c 'create database filesenderdataset;' -U postgres
bzcat ./scripts/dataset/dumps/filesender-2.0beta1.pg.bz2 | psql -d filesenderdataset 
