#!/bin/bash
set -ev
mysql -e "create database IF NOT EXISTS filesender;" -uroot;
php ./scripts/upgrade/database.php;