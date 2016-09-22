#!/bin/bash
set -ev
mysql -e "create database IF NOT EXISTS filesender;" -uroot;
mysql -e "UPDATE user SET password=PASSWORD('password') WHERE user='travis';" -uroot;
php ./scripts/upgrade/database.php;