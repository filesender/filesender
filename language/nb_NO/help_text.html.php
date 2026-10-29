<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Logg inn</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Du logger inn via en av identitetstilbyderne i listen med den vanlige institusjonskontoen din. Kontakt lokal IT-støtte hvis du ikke finner institusjonen din i listen, eller hvis du ikke får logget inn.</li>
</ul>

<h3>Nettleserfunksjoner</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5-opplasting aktivert" /> Du kan laste opp filer av alle størrelser, opptil {size:cfg:max_transfer_size} per overføring.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5-opplasting deaktivert" /> Du kan laste opp filer på opptil {size:cfg:max_legacy_file_size} hver og opptil {size:cfg:max_transfer_size} per overføring.</li>
</ul>

<h3>Opplasting av filer <i>uansett størrelse</i> med HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Du kan bruke denne metoden hvis symbolet <img src="images/html5_installed.png" alt="HTML5-opplasting aktivert" /> vises ovenfor.</li>
    <li><i class="fa-li fa fa-caret-right"></i>For å aktivere denne funksjonen trenger du bare å bruke en oppdatert nettleser som støtter HTML5, den nyeste versjonen av «nettets språk».</li>
    <li><i class="fa-li fa fa-caret-right"></i>Oppdaterte versjoner av Firefox og Chrome på Windows, Mac OS X og Linux fungerer.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Du kan <strong>fortsette</strong> en avbrutt eller avlyst opplasting. For å fortsette en opplasting sender du ganske enkelt <strong>nøyaktig de samme filene</strong> på nytt.
        Kontroller at filene har <strong>samme navn og størrelse</strong> som før.
        Når opplastingen starter, skal du se at framdriftslinjen hopper til stedet der opplastingen ble avbrutt, og fortsetter derfra.
    </li>
</ul>

<h3>Opplasting av filer på opptil {size:cfg:max_legacy_file_size} per fil uten HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} varsler deg hvis du prøver å laste opp en fil som er for stor for denne metoden.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Det er ikke mulig å fortsette opplastinger med denne metoden.</li>
</ul>

<h3>Nedlasting av filer uansett størrelse</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Alle moderne nettlesere fungerer fint; nedlasting krever ingen spesielle funksjoner.</li>
</ul>

<h3>Konfigurerte begrensninger for tjenesten</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalt antall mottakere: </strong>{cfg:max_transfer_recipients} e-postadresser adskilt med komma eller semikolon</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalt antall filer per overføring: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimal størrelse per overføring: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimal filstørrelse per fil for nettlesere uten HTML5: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Antall dager før overføringen utløper: </strong>{cfg:default_transfer_days_valid} (maks. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Antall dager før gjestetilgangen utløper: </strong>{cfg:default_guest_days_valid} (maks. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Tekniske detaljer</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> bruker <a href="http://www.filesender.org/" target="_blank">FileSender-programvaren</a>.
        FileSender viser om HTML5-opplastingsmetoden støttes i en bestemt nettleser.
        Dette avhenger hovedsakelig av om avanserte nettleserfunksjoner er tilgjengelige, særlig HTML5 FileAPI.
        Bruk nettstedet <a href="http://caniuse.com/fileapi" target="_blank">«When can I use...»</a> for å følge med på hvor langt implementeringen av HTML5 FileAPI har kommet i de største nettleserne.
        Støtte for <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> og <a href="http://caniuse.com/bloburls" target="_blank">Blob-URL-er</a> må være markert lysegrønt (= støttet) for at nettleseren skal kunne laste opp filer som er større enn {size:cfg:max_legacy_file_size}.
        Merk at selv om Opera 12 står oppført med støtte for HTML5 FileAPI, støtter den ikke alt som trengs for å bruke HTML5-opplasting i FileSender.
    </li>
</ul>

<p>Du finner mer informasjon på <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
