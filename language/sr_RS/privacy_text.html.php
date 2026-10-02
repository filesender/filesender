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
     Da bi ova usluga funkcionisala mora zadržati neke
     informacije o fajlovima, ko im može pristupiti i šta se s njima
     događa. Fajlovi će se automatski ukloniti iz sistema kada
     isteknu, a ostale zadržane informacije biće uklonjene iz
     sistema i baza podataka nakon što istekne neko vreme. Ova
     stranica omogućava Vam uvid u to koliko dugo traju razni podaci
     zadržani na ovoj usluzi.
</p>
<p>
     Kada se transfer izbriše, svi povezani fajlovi se
     takođe brišu, zajedno sa kopijama svih e-mail poruka koje su poslate a
     odnose se na transfer.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Ova usluga je podešena da sigurno obriše fajlove (wipe). ";
    echo "Sigurno brisanje fajlova uključuje upisivanje slučajnih podataka na istu lokaciju na disku";
    echo " gde se nalazio fajl više puta čime je fajl potpuno uklonjen sa sistema. ";
    echo "Na ovaj način je osigurana dodatna privatnost za korisnike ove usluge.</p>";
}
?>