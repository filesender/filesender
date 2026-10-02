<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<p>
    Failijagamise aegumisel eemaldatakse süsteemist kõik vastava failijagamisega seotud failid ning e-posti aadressid.
    Samuti kustutatakse logid andmebaasist mõne aja möödudes. Allpool on kirjas kui kaua erinevaid andmeid säilitatakse.
</p>
<p>
    Failijagamise kustutamisel eemaldatakse süsteemist kõik vastava failijagamisega seotud failid ning e-posti aadressid.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>This site is configured to shred uploaded files when they are deleted. ";
    echo "Shredding a file involves writing data into the same location on the disk";
    echo " many times in order to truely remove the user data from the system. ";
    echo "This provides additional privacy for users of this service.</p>";
}
?>
