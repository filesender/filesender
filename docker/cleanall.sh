#!/bin/bash
#
echo Prune system and voumes and remove containers, images and volumes in 5s. Press Ctrl-C to cancel.
for i in {5..1}; do echo $i & sleep 1; done

docker rm -vf $(docker ps -aq)
docker rmi -f $(docker images -aq)
docker system prune -f
docker volume prune -f

rm -rf mysql_data
mkdir -p mysql_data
