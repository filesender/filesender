<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Dobrodošli na strani {cfg:site_name}</h1>
<p>
  Za delovanje storitve se morajo hraniti določeni podatki o datotekah,
  kdo lahko do njih dostopa in kaj se je z njimi zgodilo. Datoteke se ob
  preteku roka veljavnosti avtomatsko odstranijo s sistema. Ravno tako tudi
  podatki, ki jih zadevajo. Na tej strani lahko vidite koliko časa se hranijo
  različni drobci informacij.
</p>
<p>
  Upoštevajte, da se ob odstranitvi prenosa izbrišejo vse z njim povezane
  datoteke in kopije z njim povezanih poslanih sporočil.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Ta stran je nastavljena za popolno uničenje izbrisanih datotek. ";
    echo "Popolno uničenje datoteke vključuje pisanje podatkov na isto mesto na disku";
    echo " večkrat, da uporabniški podatki popolnoma izginejo iz sistema. ";
    echo "To omogoča dodatno stopnjo zasebnosti za uporabnike te storitve.</p>";
}
?>