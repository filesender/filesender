#!/bin/bash
#
docker compose down
docker stop filesender
docker rm filesender
docker build -t filesender . --progress plain
