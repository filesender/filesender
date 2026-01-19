#!/bin/bash
#
docker build -t filesender . --progress plain
docker compose up -d
sleep 15
docker exec -ti filesender php /opt/filesender/filesender/scripts/upgrade/database.php
