
* import-idp-metadata.php

Import the metadata about your known IdPs from SimpleSAMLphp into the
idps table in FileSender. This script will process all known remote
IdPs from your SimpleSAMLphp metadata.

* cron-update-idp-metadata.php

This will read the entityID for each idp in the idps table and search
for metadata from SimpleSAMLphp to populate the other columns in the
table. The default is to use this script to update metadata as needed
rather than doing it inline as requests are processed. 

This script will only process the entityIDs from the filesender IdP
table.



