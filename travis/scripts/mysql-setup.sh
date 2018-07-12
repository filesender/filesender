#!/bin/bash
#
# These are only the commands that are specific to mysql/mariadb which need
# to be executed before the scripts/upgrade/database.php can be used
#
set -ev
mysql -e "create database IF NOT EXISTS filesender;" -uroot;
mysql -e "UPDATE user SET password=PASSWORD('password') WHERE user='travis';" -uroot;
