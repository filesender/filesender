<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Tervetuloa palveluun {cfg:site_name}</h1>
<p>
    Toimiakseen suunnitellulla tavalla palvelu säilyttää tietoja jaetuista tiedostoista, palvelun käyttäjistä sekä suoritetuista toimenpiteistä. Tiedostot ja niihin liittyvät tiedot poistetaan palvelusta automaattisesti tiedostojaon eräännyttyä. Tiedostoista ei oteta varmuuskopioita, eikä palvelua ole tarkoitettu tiedostojen pysyväissäilytykseen. Tällä sivulla on lisätietoja tallennetuista tiedoista.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Kun tiedostojako erääntyy, tiedostot poistetaan ja niiden sijainti levyllä ylikirjoitetaan useita kertoja. Toimenpiteellä pyritään osaltaan turvaamaan palvelun käyttäjien tietosuojaa.</p>";
}
?>