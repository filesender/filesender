svn status dev/doc/phpdoc | grep "?" | awk '{print $2}' | xargs svn add
svn status dev/doc/phpdoc | grep "!" | awk '{print $2}' | xargs svn del
svn ci -m "Update php documentation" dev/doc/phpdoc
