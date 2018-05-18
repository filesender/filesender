#!/bin/bash
#
# These are only the commands that are specific to postgresql which need
# to be executed before the scripts/upgrade/database.php can be used
#
set -ev
# psql -c 'SELECT version();' -U postgres
psql -c 'create database filesender;'        -U postgres
psql -U postgres -c "alter user postgres with password 'password';"

psql -c 'create database filesenderdataset;' -U postgres
bzcat ./scripts/dataset/dumps/filesender-2.0beta1.pg.bz2 | psql -d filesenderdataset 

