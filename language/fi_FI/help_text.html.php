<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Sisäänkirjautuminen</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Kirjaudu sisään käyttämällä listatun kotiorganisaatiosi tarjoamia käyttäjätunnuksia. Mikäli organisaatiotasi ei ole listattu, tai sisäänkirjautumisessa esiintyy ongelmia, ota yhteyttä oman kotiorganisaatiosi IT-tukeen</li>
</ul>

<h3>Selaimesi ominaisuudet</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5-tiedonsiirto tuettu" /> HTML5-yhteensopivalla selaimella voit siirtää palveluun suuriakin tiedostoja. Tällä hetkellä kokorajoitus on {size:cfg:max_transfer_size}.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5-tiedonsiirron tuki puuttuu" /> Selaimesi tukee korkeintaan {size:cfg:max_legacy_file_size} kokoisten tiedostojen siirtoa.</li>
</ul>

<h3>HTML5 suurten tiedostojen siirtoon</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Voit käyttää tätä tiedonsiirtomenetelmää mikäli näet yläpuolella <img src="images/html5_installed.png" alt="HTML5-tiedonsiirto tuettu" />-ikonin selaintuen merkiksi</li>
    <li><i class="fa-li fa fa-caret-right"></i>Pystyt hyödyntämään palvelun HTML5-tukea käyttämällä riittävän nykyaikaista selainta.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Esimerkiksi Firefoxin ja Chromen nykyversiot Windowsilla, Mac OS:lla ja Linuxilla toimivat.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Voit <strong>jatkaa</strong> keskeytynyttä tiedonsiirtoa. Jos tiedoston lähetys palveluun jostain syystä katkeaa, voit jatkaa siirtoa uudelleenkäynnistää siirron jakamalla <strong>saman tiedoston samalla nimellä uudelleen</strong>. an interrupted or cancelled upload.
        Kun siirto jälleen alkaa, etenemispalkin pitäisi jatkua siitä mihin viimeksi jäit.
    </li>
</ul>

<h3>Yli {size:cfg:max_legacy_file_size} kokoisten tiedostojen siirtäminen palveluun ilman HTML5-tukea</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} varoittaa mikäli yrität siirtää liian suurta tiedostoa ilman selaimen HTML5-tukea.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Keskeytyneiden siirtojen jatkaminen ei ole mahdollista ilman HTML5-tukea. Suosittelemme nykyaikaisempaa selainta.</li>
</ul>

<h3>Tiedostojen lataaminen palvelusta</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Download-suuntaiseen siirtoon eli jaetun tiedoston lataamiseen palvelusta ei ole erityisiä selainvaatimuksia. Tiedoston koosta riippumatta HTML5-tukea ei vaadita.</li>
</ul>

<h3>Palveluun asetettut käyttörajat</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Vastaanottajien enimmäismäärä: </strong>{cfg:max_transfer_recipients} sähköpostiosoitetta pilkulla tai puolipisteellä erotettuna</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Tiedostojen suurin sallittu määrä yksittäisessä jaossa: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Yksittäisen jaon suurin sallittu yhteiskoko: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Suurin mahdollinen tiedostokoko ilman selaimen HTML5-tukea: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Tiedostojako oletusarvoisesti noudettavissa (vuorokautta): </strong>{cfg:default_transfer_days_valid} (maks. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Kutsu oletusarvoisesti voimassa (vuorokautta): </strong>{cfg:default_guest_days_valid} (maks. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Teknisiä yksityiskohtia</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> perustuu avoimen lähdekoodin <a href="http://www.filesender.org/" target="_blank">FileSender-sovellukseen</a>.
        Palvelu tunnistaa ja kertoo mikäli käyttäjän selain tukee nopeampaa ja luotettavampaa HTML5-tiedonsiirtoa.
        Mikäli olet kiinnostunut selaimesi HTML5-tuesta, voit käyttää esimerkiksi palvelua <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> vertaillaksesi eri selainten HTML5 FileAPI -ominaisuuksia.
        FileSender hyödyntää erityisesti <a href="http://caniuse.com/filereader" target="_blank">FileReader API-</a> ja <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> -toiminnallisuuksia.
    </li>
</ul>

<p>Lisätietoja FileSender-projektista löydät osoitteesta <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>