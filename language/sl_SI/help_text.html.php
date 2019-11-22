<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Prijava</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Prijavite se preko enega od navedenih ponudnikov identitet (IdP) s prijavnimi podatki za Vašo organizacijo. Vkolikor svoje organizacije ne vidite na seznamu oziroma se ne morete prijaviti, se obrnite na Vašo skupino za podporo.</li>
</ul>

<h3>Zmožnosti Vašega brskalnika</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> Nalagate lahko datoteke kakršne koli velikosti do {size:cfg:max_transfer_size} na prenos.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> Nalagate lahko datoteke do največ {size:cfg:max_legacy_file_size} posamezno in do največ {size:cfg:max_transfer_size} na cel prenos.</li>
</ul>

<h3>Nalaganja datotek <i>vseh velikosti</i> s HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>To metodo lahko uporabljate, če se Vam zgoraj prikaže znak <img src="images/html5_installed.png" alt="HTML5 upload enabled" />.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Da omogočite to funkcionalnost, preprosto uporabljajte kateri koli sodobni brskalnik, ki podpira HTML5; zadnjo verzijo "jezika svetovnega spleta".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Posodobljene različice brskalnikov Firefox in Chrome na sistemih Windows, Mac OS X in Linux preverjeno delujejo.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Z začasno ustavljenimi ali prekinjenimi prenosi lahko <strong>nadaljujete</strong>. Za nadaljevanje nalaganja, preprosto <strong>pošljite iste datoteke</strong> še enkrat.
        Poskrbite, da imajo datoteke <strong>enaka imena in velikosti</strong> kot prej.
        Ko se nadaljevanje nalaganja prične, boste videli vrstico z napredkom skočiti na isto mesto, kot je bila prej, od koder bo nadaljevala.
    </li>
</ul>

<h3>Nalaganja do {size:cfg:max_legacy_file_size} na datoteko brez HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender Vas bo obvestil, če boste poskušali naložiti datoteko, ki je prevelika za to metodo.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Nadaljevanje nalaganja s to metodo ni podprto.</li>
</ul>

<h3>Prenosi vseh velikosti</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Vsak sodoben brskalnik se bo izkazal povsem v redu, prenosi nimajo posebnih zahtev.</li>
</ul>

<h3>Nastavljene omejitve storitve</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Največje število prejemnikov : </strong>{cfg:max_transfer_recipients} e-poštni naslovi, ločeni z vejico ali podpičjem</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Največje število datotek na prenos : </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Največja velikost na prenos : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Največja velikost datoteke za brskalnike, ki ne podpirajo HTML5 : </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Rok veljavnosti prenosov : </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Rok veljavnosti za goste : </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Tehnične podrobnosti</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> uporablja <a href="http://www.filesender.org/" target="_blank">FileSender programsko opremo</a>.
        FileSender javi, ali je metoda nalaganja s HTML5 podprta za dotični brskalnik.
        To se zanaša predvsem na dostopnost napredne funkcionalnosti brskalnika, posebaj HTML5 FileAPI.
        Prosimo, uporabljajte <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> spletno stran za pregled podprtosti HTML5 FileAPI za vse večje brskalnike.
        Podpora za <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> in <a href="http://caniuse.com/bloburls" target="_blank">Blob URLje</a> mora biti svetlo zelene barve (=podprto), da lahko brskalnik podpira nalaganje datotek, večjih od  {size:cfg:max_legacy_file_size}.
        Upoštevajte, da četudi je brskalnik Opera 12 naveden kot podprt za uporabo, trenutno ne podpira vsega potrebnega za HTML5 nalaganje v aplikaciji FileSender
    </li>
</ul>

<p>Za več informacij obiščite <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>