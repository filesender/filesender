#!/bin/bash

set -v

PWD=$(pwd)
echo "----------------------------------------------------------------"
echo "This is the ci/setup-machine.sh script running on database $DB "
echo "----------------------------------------------------------------"

echo "current directory"
pwd
FILESENDERROOT=$(pwd);
echo "your php modules installed..."
php -m
echo "----------------------------------------------------------------"


echo "127.0.0.1  file_sender.app" | sudo tee -a /etc/hosts


export POSTGRES_DB
export POSTGRES_HOST
export POSTGRES_USER
export POSTGRES_PASSWORD

mkdir -p ./log ./tmp ./files

cp ./ci/filesender-config.php ./config/config.php
if [ "$DB" = "mysql" ]; then
    sed -e "s?usemysql=0?usemysql=1?g" --in-place ./config/config.php  ;
fi

sed -e "s?%TRAVIS_BUILD_DIR%?${FILESENDERROOT}?g" --in-place ./config/config.php
chmod -R a+x ./ci/scripts

####
echo "looking at permissions on config.php"
ls -l ./config/config.php
sudo chown www-data ./config/config.php
ls -l ./config/config.php
echo "looking at www/skin permissions"
ls -ld www/skin
ls -ld www
sudo mkdir www/skin
sudo chown www-data www/skin
sudo chmod u+rwx www/skin
echo "final permissions..."
ls -ld www/skin


####
#
# handle the database
#
echo "Calling database specific setup script DB:$DB "
if [ "$DB" = "pgsql" ]; then
    echo "PostgreSQL database type..."
    ./ci/scripts/postgresql-setup.sh
fi 
if [ "$DB" = "mysql" ]; then
    ./ci/scripts/mariadb-setup.sh
fi 

echo "----------"
echo "back in setup-machine.sh script..."
echo "Calling upgrade/database.php on the database now"
php scripts/upgrade/database.php
if [ "$TESTSUITE" = "dataset" ]; then
    php scripts/upgrade/database.php --db_database filesenderdataset ;
fi

####
#
# Packages
#
sudo apt-get update
sudo apt-get install curl wget apache2  libapache2-mod-fastcgi apache2-mpm-worker 

####
# 
# Apache
#
sudo a2enmod rewrite actions fastcgi alias ssl headers
sudo sed -i "s/NameVirtualHost \*:80/# NameVirtualHost \*:80/" /etc/apache2/ports.conf
sudo sed -i "s/Listen 80/# Listen 80/" /etc/apache2/ports.conf
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/certs/filesender_test.key -out /etc/ssl/certs/filesender_test.crt -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=file_sender.app"
sudo cp -f ci/apache2.conf /etc/apache2/sites-enabled/000-default.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?${FILESENDERROOT}?g" --in-place /etc/apache2/sites-enabled/000-default.conf

sudo apt-get install php${php_version}-fpm
sudo cp /usr/sbin/php-fpm${php_version} /usr/bin/php-fpm # copy to /usr/bin
sudo ls -l /var/lib/apache2/fastcgi
sudo chown -R runner:docker /var/lib/apache2/fastcgi /usr/sbin/php-fpm${php_version} /usr/bin/php-fpm

phpini=$(php -r "echo get_cfg_var('cfg_file_path');")
echo "cgi.fix_pathinfo = 1" >> $phpini

echo "___ changing group to www-data for $FILESENDERROOT "
sudo chgrp -R www-data ${FILESENDERROOT}
sudo chown -R www-data:docker   ${FILESENDERROOT}/log
sudo chown -R www-data:docker   ${FILESENDERROOT}/files
sudo chown -R www-data:www-data ${FILESENDERROOT}/tmp
# recommended by install script for php${php_version}-fpm
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php7.4-fpm

echo "restarting apache2 and php-fpm..."
sudo service php${php_version}-fpm start
sudo service php${php_version}-fpm status
sudo service apache2 restart

# stop the database we are not planning to use
# to catch bad configurations that might use the wrong database by mistake
if [ "$DB" = "pgsql" ]; then
    sudo service mysql stop
fi 
if [ "$DB" = "mysql" ]; then
    sudo service postgresql stop
fi 

echo "... making sure sauce connect doesn't redefine SAUCE_HOST ..."
grep -l -R SAUCE_HOST ${FILESENDERROOT}/vendor | \
    xargs -I {} -n 1 sed -i -e "s,define('SAUCE_HOST',if(\!defined('SAUCE_HOST')) define('SAUCE_HOST',g" "{}"

echo "... trying to get index page to verify ..."
curl -k https://localhost/filesender/
curl -k https://file_sender.app/filesender/

echo "--------------------"
echo "Apache logs....     "
echo "--------------------"
cat /var/log/apache2/access.log
cat /var/log/apache2/error.log
