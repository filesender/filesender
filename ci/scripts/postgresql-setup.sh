#!/bin/bash
#
# These are only the commands that are specific to postgresql which need
# to be executed before the scripts/upgrade/database.php can be used
#
echo "----------------------------"
echo "postgresql-setup.sh DB:$DB "
echo "----------------------------"
set -ev

PSQL="psql -h localhost -U postgres"


echo "*:*:*:*:password" > ~/.pgpass
chmod 400 ~/.pgpass
echo "password is "
cat ~/.pgpass

echo "database listing is..."
$PSQL -l


# psql -c 'SELECT version();' -U postgres
$PSQL -c 'create database filesender;'
#$PSQL -c "alter user postgres with password 'password';"

if [ "$TESTSUITE" = "dataset" ]; then 
    $PSQL -c 'create database filesenderdataset;'
    bzcat ./scripts/dataset/dumps/filesender-2.2.pg.bz2 | $PSQL -d filesenderdataset
fi

if [ "$TESTSUITE" = "cron" ]; then 
    cat ./scripts/dataset/dumps/filesendercron.pg       | $PSQL
    cat ./scripts/dataset/dumps/filesendercron-touch.pg | $PSQL -d filesender
    echo 'select * from files' | $PSQL -d filesender
fi


echo "database creation should be done"
$PSQL -l

# PGVER=(psql --version|cut -d' ' -f3);
PGVER="9.2"
#sudo sed -i "s/host    all             all             127.0.0.1\/32            trust/host    all             all             127.0.0.1\/32            md5/" "/etc/postgresql/$PGVER/main/pg_hba.conf"
#sudo sed -i "s/host    all             all             ::1\/128                 trust/host    all             all             ::1\/128                 md5/" "/etc/postgresql/$PGVER/main/pg_hba.conf"


#sudo service postgresql restart
