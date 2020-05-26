#!/bin/bash
#
# These are only the commands that are specific to postgresql which need
# to be executed before the scripts/upgrade/database.php can be used
#
set -ev
# psql -c 'SELECT version();' -U postgres
psql -c 'create database filesender;'        -U postgres
psql -U postgres -c "alter user postgres with password 'password';"

if [ "$TESTSUITE" = 'dataset' ]; then 
    psql -c 'create database filesenderdataset;' -U postgres
    bzcat ./scripts/dataset/dumps/filesender-2.2.pg.bz2 | psql -d filesenderdataset -U postgres
fi

if [ "$TESTSUITE" = 'cron' ]; then 
    cat ./scripts/dataset/dumps/filesendercron.pg | psql -U postgres
    cat ./scripts/dataset/dumps/filesendercron-touch.pg | psql -U postgres
fi

