#!/bin/bash

TESTUSER=tester
APACHEGROUP=apache
TARGET=/opt/filesendertest
NONTESTING=/opt/filesender

if [ -f sync-to-filesender-test.sh ]; then
    echo "you started me in the in src dir"
fi
cd "$(dirname $0)"

cd ../..
if [ ! -d classes ]; then
    echo "You should either be in the top level directory of the "
    echo " source tree or the directory that contains the script "
fi
echo "Moved back to the top level directory at $(pwd)"

mkdir -p  $TARGET/log $TARGET/files $TARGET/tmp
chmod g+w $TARGET/log $TARGET/files $TARGET/tmp

rsync -a language  "$TARGET/"
rsync -a templates "$TARGET/"
rsync -a www       "$TARGET/"
rsync -a classes   "$TARGET/"
rsync -a includes  "$TARGET/"
rsync -a lib                    "$TARGET/"
rsync -a unittests              "$TARGET/"
rsync -a scripts                "$TARGET/"
rsync -a vendor                 "$TARGET/"
rsync -a optional-dependencies  "$TARGET/"
rsync -a "$NONTESTING/config"   "$TARGET/"
sed -i -e 's/\$testing = false/\$testing = true/g' "$TARGET/config/config.php"
sed -i    "/config\['user_page'\]/d" "$TARGET/config/config.php"
sed -i    "/config\['admin'\]/d"     "$TARGET/config/config.php"
echo "\$config['user_page'] = '';" >> "$TARGET/config/config.php"
echo "\$config['admin'] = 'root';" >> "$TARGET/config/config.php"
chmod 770 "$TARGET/config/config.php"

chown -R $TESTUSER:$APACHEGROUP $TARGET
chmod -R 770 $TARGET/log $TARGET/files

echo "Test install setup at $TARGET"


