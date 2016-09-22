#!/bin/bash
set -ev
psql -c 'create database filesender;' -U postgres
psql -U postgres -c "alter user postgres with password 'password';"
php ./scripts/upgrade/database.php;