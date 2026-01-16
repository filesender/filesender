#!/bin/bash
#
docker stop filesender
docker rm filesender
docker build -t filesender . --progress plain
