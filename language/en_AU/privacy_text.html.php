<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Welcome to {cfg:site_name}</h1>
<p>
    In order for this service to operate it must retain some
    information about files, who can access them, and what has
    happened. Files will be automatically removed from the system when
    they expire and other retained information will be removed from
    the system and database after some amount of time has passed. This
    page allows you to see how long various pieces of information are
    retained by this installation.
</p>
<p>
    Note that when a transfer is deleted, all the related files are
    also deleted along with the copies of any emails that have been sent out
    which relate to the transfer.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>This site is configured to shred uploaded files when they are deleted. ";
    echo "Shredding a file involves writing data into the same location on the disk";
    echo " many times in order to truely remove the user data from the system. ";
    echo "This provides additional privacy for users of this service.</p>";
}
?>
