#!/bin/bash
#
# These are only the commands that are specific to postgresql which need
# to be executed before the scripts/upgrade/database.php can be used
#
echo "postgresql-setup.sh DB:$DB "
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
    cat ./scripts/dataset/dumps/filesendercron-touch.pg | psql -U postgres -d filesender
    echo 'select * from files' | psql -U postgres -d filesender
fi


# PGVER=(psql --version|cut -d' ' -f3);
PGVER="9.2"
#sudo sed -i "s/host    all             all             127.0.0.1\/32            trust/host    all             all             127.0.0.1\/32            md5/" "/etc/postgresql/$PGVER/main/pg_hba.conf"
#sudo sed -i "s/host    all             all             ::1\/128                 trust/host    all             all             ::1\/128                 md5/" "/etc/postgresql/$PGVER/main/pg_hba.conf"


sudo service postgresql restart
