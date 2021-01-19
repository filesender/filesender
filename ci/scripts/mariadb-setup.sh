#!/bin/bash
#
# These are only the commands that are specific to mariadb which need
# to be executed before the scripts/upgrade/database.php can be used
#

echo "----------------------------"
echo "mariadb-setup.sh DB:$DB "
echo "----------------------------"
set -ev

MYSQL="mysql --host=127.0.0.1 -u root --protocol=TCP "

echo "[client]"             > ~/.my.cnf
echo "host=127.0.0.1"      >> ~/.my.cnf
echo "password=password"   >> ~/.my.cnf
echo "#something=else"     >> ~/.my.cnf
chmod 600 ~/.my.cnf
echo "my.cnf is "
cat ~/.my.cnf

echo "database listing is..."
$MYSQL --execute="show databases;"


$MYSQL  -e "CREATE DATABASE filesender DEFAULT CHARACTER SET utf8mb4;"
$MYSQL  -e "GRANT USAGE ON *.* TO 'filesender'@'localhost' IDENTIFIED BY 'password';"
$MYSQL  -e "GRANT DROP, CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE, DROP, REFERENCES ON filesender.* TO 'filesender'@'localhost';"


$MYSQL  -e "CREATE DATABASE filesenderdataset DEFAULT CHARACTER SET utf8mb4;"
$MYSQL  -e "GRANT DROP, CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE ON filesenderdataset.* TO 'filesender'@'localhost';"
$MYSQL  -e "FLUSH PRIVILEGES;"





