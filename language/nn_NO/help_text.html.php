<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Innlogging</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Du loggar inn via ein av identitetstilbydarane i lista med den vanlege institusjonskontoen din. Kontakt den lokale IT-brukarstøtta dersom du ikkje finn institusjonen din i lista eller ikkje får logga inn.</li>
</ul>

<h3>Funksjonar i nettlesaren din</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5-opplasting er slått på" /> Du kan laste opp filer av alle storleikar, opptil {size:cfg:max_transfer_size} per overføring.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5-opplasting er slått av" /> Du kan laste opp filer på opptil {size:cfg:max_legacy_file_size} kvar og opptil {size:cfg:max_transfer_size} per overføring.</li>
</ul>

<h3>Opplasting av filer <i>uansett storleik</i> med HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Du kan bruke denne metoden dersom teiknet <img src="images/html5_installed.png" alt="HTML5-opplasting er slått på" /> ovanfor blir vist.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Bruk ein oppdatert nettlesar som støttar HTML5, den nyaste versjonen av «språket på nettet», for å slå på denne funksjonen.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Oppdaterte versjonar av Firefox og Chrome på Windows, Mac OS X og Linux fungerer.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Du kan <strong>halde fram</strong> med ei avbroten eller avlyst opplasting. For å halde fram med ei opplasting sender du berre <strong>nøyaktig dei same filene</strong> på nytt.
        Kontroller at filene har <strong>same namn og storleik</strong> som før.
        Når opplastinga startar, skal framdriftslinja hoppe til staden der opplastinga vart avbroten og halde fram derifrå.
    </li>
</ul>

<h3>Opplasting av filer på opptil {size:cfg:max_legacy_file_size} utan HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} varslar deg dersom du prøver å laste opp ei fil som er for stor for denne metoden.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Det er ikkje mogleg å halde fram med opplastingar med denne metoden.</li>
</ul>

<h3>Nedlasting av filer uansett storleik</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Alle moderne nettlesarar fungerer fint; nedlasting krev ingen særskilde funksjonar.</li>
</ul>

<h3>Grenser for tenesta</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalt tal på mottakarar: </strong>{cfg:max_transfer_recipients} e-postadresser skilde med komma eller semikolon</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalt tal på filer per overføring: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimal storleik per overføring: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimal filstorleik i nettlesarar utan HTML5: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Overføringar er gyldige i: </strong>{cfg:default_transfer_days_valid} dagar (maks. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Gjesteinvitasjonar er gyldige i: </strong>{cfg:default_guest_days_valid} dagar (maks. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Tekniske detaljar</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> brukar <a href="http://www.filesender.org/" target="_blank">FileSender-programvara</a>.
        FileSender viser om HTML5-opplasting er støtta i den aktuelle nettlesaren.
        Dette er i hovudsak avhengig av om avanserte nettlesarfunksjonar er tilgjengelege, særleg HTML5 FileAPI.
        Bruk nettstaden <a href="http://caniuse.com/fileapi" target="_blank">«When can I use...»</a> for å følgje med på kor langt implementeringa av HTML5 FileAPI har kome i dei største nettlesarane.
        Støtte for <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> og <a href="http://caniuse.com/bloburls" target="_blank">Blob-URL-ar</a> må særleg vere vist som lysegrøn (= støtta) for at nettlesaren skal kunne laste opp filer som er større enn {size:cfg:max_legacy_file_size}.
        Merk at sjølv om Opera 12 er oppført med støtte for HTML5 FileAPI, støttar han ikkje alt som trengst for å bruke HTML5-opplasting i FileSender.
    </li>
</ul>

<p>Du finn meir informasjon på <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
