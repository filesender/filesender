#!/bin/bash

#
# Run this is postgres user and cp /tmp/filesendercron.pg back to the repo
#

cd /tmp
pg_dump --no-owner -C -c  filesendercron >| filesendercron.pg
sed -i s/filesendercron/filesender/g filesendercron.pg
chmod o+r filesendercron.pg

