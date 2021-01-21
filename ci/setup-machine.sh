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



export POSTGRES_DB
export POSTGRES_HOST
export POSTGRES_USER
export POSTGRES_PASSWORD

mkdir -p ./log ./tmp ./files

#phpenv config-add ./ci/php-config.ini

cp ./ci/filesender-config.php ./config/config.php
if [ "$DB" = "mysql" ]; then
    sed -e "s?usemysql=0?usemysql=1?g" --in-place ./config/config.php  ;
fi

sed -e "s?%TRAVIS_BUILD_DIR%?${FILESENDERROOT}?g" --in-place ./config/config.php
chmod -R a+x ./ci/scripts

####miq#./ci/scripts/simplesamlphp-setup.sh

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
sudo apt-get install curl wget apache2 

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

# enable php-fpm
#sudo sed -i "s/error_reporting = E_ALL/error_reporting = E_ALL \& ~E_DEPRECATED \& ~E_STRICT/" ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
#sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
#sudo a2enmod rewrite actions fastcgi alias
#echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
#sudo sed -i -e "s,www-data,travis,g" /etc/apache2/envvars
#sudo chown -R travis:travis /var/lib/apache2/fastcgi
#sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf
# #- sudo sed -i -e "s,nobody,travis,g " ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf
# - ~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm
# - sudo cat /etc/apache2/sites-enabled/000-default.conf

# echo "setting up php-fpm..."
# version=7.2
# sudo a2enmod rewrite actions fastcgi alias
# ls -l /etc/php.ini
# echo "cgi.fix_pathinfo = 1" >> /etc/php.ini
# ls -l /etc/apache2/envvars
# echo "___looking at /var/lib/apache2 1"
# sudo ls -l /var/lib/apache2
# echo "___looking at /var/lib/apache2 2"
# sudo ls -l /var/lib/apache2/fastcgi
# echo "___sites enabled"
# sudo ls -l /etc/apache2/sites-enabled/


version=7.2
sudo apt-get install php$version-fpm
sudo cp /usr/sbin/php-fpm$version /usr/bin/php-fpm # copy to /usr/bin
sudo service php$version-fpm start
sudo service php$version-fpm status
php-fpm -v
echo "______ dpkg listing "
dpkg -l

echo "______ ... "
# recommended by install script for php$version-fpm
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php7.2-fpm


echo "___ /etc/httpd"
#ls -l /etc/httpd

echo "___ /etc/httpd/conf.d"
#ls -l /etc/httpd/conf.d

#sudo a2dismod mpm_event
#sudo  a2enmod mpm_prefork
#sudo  a2enmod php7.0




echo "restarting apache2..."
sudo service apache2 restart

# stop the database we are not planning to use
# to catch bad configurations that might use the wrong database by mistake
if [ "$DB" = "pgsql" ]; then
    sudo service mysql stop
fi 
if [ "$DB" = "mysql" ]; then
    sudo service postgresql stop
fi 

echo "about to do the update to sauce...1"
ls -l vendor
echo "about to do the update to sauce...2"
ls -l vendor/sauce
echo "about to do the update to sauce...3"
ls -l vendor/sauce/sausage
echo "about to do the update to sauce...4"
ls -l vendor/sauce/sausage/src

echo "... making sure sauce connect doesn't redefine SAUCE_HOST ..."

grep -l -R SAUCE_HOST ${FILESENDERROOT}/vendor | \
    xargs -I {} -n 1 sed -i -e "s,define('SAUCE_HOST',if(\!defined('SAUCE_HOST')) define('SAUCE_HOST',g" "{}"


echo "... trying to get index page to verify ..."
curl -k https://localhost/filesender/


# after_failure:
# - sudo cat /var/log/apache2/error.log
# - sudo cat /var/log/apache2/access.log
# - find ./log -type f -exec cat {} +
