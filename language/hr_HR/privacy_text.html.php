<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Dobrodošli na {cfg:site_name}</h1>
<p>
     Da bi ova usluga funkcionirala mora zadržati neke
     informacije o datotekama, tko im može pristupiti i što se s njima
     događa. Datoteke će se automatski ukloniti iz sustava kada
     isteknu, a ostale zadržane informacije bit će uklonjene iz
     sustava i baza podataka nakon što protekne neko vrijeme. Ova
     stranica omogućuje Vam uvid u to koliko dugo traju razni podaci
     zadržani na ovoj usluzi.
</p>
<p>
     Kada se prijenos izbriše, sve povezane datoteke se
     također brišu, zajedno s kopijama svih e-mail poruka koje su poslane a
     odnose se na prijenos.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Ova usluga je podešena da sigurno obriše datoteke (wipe). ";
    echo "Sigurno brisanje datoteka uključuje upisivanje slučajnih podataka na istu lokaciju na disku";
    echo " gdje se nalazila datoteka više puta čime je datoteka potpuno uklonjena sa sustava. ";
    echo "Na ovaj način je osigurana dodatna privatnost za korisnike ove usluge.</p>";
}
?>