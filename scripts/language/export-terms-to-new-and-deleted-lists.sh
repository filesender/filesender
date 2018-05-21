#!/bin/bash

php ./export-all-terms.php                       /tmp/allterms
    ./filter-terms-list-to-deleted-terms-only.sh oldterms.txt   /tmp/allterms >| /tmp/deletedterms.txt
    ./filter-terms-list-to-new-terms-only.sh     oldterms.txt   /tmp/allterms >| /tmp/newterms.txt
    /bin/cp -f /tmp/allterms oldterms.txt
php ./convert-one-per-line-terms-to-json.php  /tmp/newterms.txt /tmp/newterms.json
