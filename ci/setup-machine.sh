#!/bin/bash

PWD=$(pwd)
echo "----------------------------------------------------------------"
echo "This is the ci/setup-machine.sh script running on database $DB "
echo "----------------------------------------------------------------"

echo "current directory"
pwd
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

sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place ./config/config.php
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
sudo apt-get install curl
sudo apt-get install apache2 #libapache2-mod-fastcgi

####
# 
# Apache
#
sudo a2enmod rewrite actions fastcgi alias ssl headers
sudo sed -i "s/NameVirtualHost \*:80/# NameVirtualHost \*:80/" /etc/apache2/ports.conf
sudo sed -i "s/Listen 80/# Listen 80/" /etc/apache2/ports.conf
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/certs/filesender_test.key -out /etc/ssl/certs/filesender_test.crt -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=file_sender.app"
sudo cp -f ci/apache2.conf /etc/apache2/sites-enabled/000-default.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-enabled/000-default.conf

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


# - echo "... making sure sauce connect doesn't redefine ..."
# - sudo  sed -i -e "s,define('SAUCE_HOST',if(\!defined('SAUCE_HOST')) define('SAUCE_HOST',g" /home/travis/build/filesender/filesender/vendor/sauce/sausage/src/Sauce/Sausage/SauceAPI.php
# - sudo  sed -i -e "s,define('SAUCE_HOST',if(\!defined('SAUCE_HOST')) define('SAUCE_HOST',g" /home/travis/build/filesender/filesender/vendor/sauce/sausage/src/Sauce/Sausage/SauceConfig.php

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
sudo  sed -i -e "s,define('SAUCE_HOST',if(\!defined('SAUCE_HOST')) define('SAUCE_HOST',g" vendor/sauce/sausage/src/Sauce/Sausage/SauceAPI.php
sudo  sed -i -e "s,define('SAUCE_HOST',if(\!defined('SAUCE_HOST')) define('SAUCE_HOST',g" vendor/sauce/sausage/src/Sauce/Sausage/SauceConfig.php


# after_failure:
# - sudo cat /var/log/apache2/error.log
# - sudo cat /var/log/apache2/access.log
# - find ./log -type f -exec cat {} +
