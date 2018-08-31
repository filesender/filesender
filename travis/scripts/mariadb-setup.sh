#!/bin/bash
#
# These are only the commands that are specific to mariadb which need
# to be executed before the scripts/upgrade/database.php can be used
#
set -ev

mysql -u root <<EOF
CREATE DATABASE `filesender` DEFAULT CHARACTER SET utf8mb4;
GRANT USAGE ON *.* TO 'filesender'@'localhost' IDENTIFIED BY 'password';
GRANT DROP, CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE ON `filesender`.* TO 'filesender'@'localhost';


CREATE DATABASE `filesenderdataset` DEFAULT CHARACTER SET utf8mb4;
GRANT DROP, CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE ON `filesenderdataset`.* TO 'filesender'@'localhost';
FLUSH PRIVILEGES;

exit

EOF

