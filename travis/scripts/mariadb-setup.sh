#!/bin/bash
#
# These are only the commands that are specific to mariadb which need
# to be executed before the scripts/upgrade/database.php can be used
#
set -ev

mysql -u root -e "CREATE DATABASE filesender DEFAULT CHARACTER SET utf8mb4;"
mysql -u root -e "GRANT USAGE ON *.* TO 'filesender'@'localhost' IDENTIFIED BY 'password';"
mysql -u root -e "GRANT DROP, CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE ON filesender.* TO 'filesender'@'localhost';"


mysql -u root -e "CREATE DATABASE filesenderdataset DEFAULT CHARACTER SET utf8mb4;"
mysql -u root -e "GRANT DROP, CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE ON filesenderdataset.* TO 'filesender'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"





