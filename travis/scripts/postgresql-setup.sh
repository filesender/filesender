#!/bin/bash
set -ev
psql -c 'create database filesender;' -U postgres
php ./scripts/upgrade/database.php;