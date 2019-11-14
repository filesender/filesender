<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Hvordan man logger ind</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Man logger ind via en af de opregnede institutioner med den brugerkonto man har ved institutionen. Hvis din institution ikke findes på listen, eller dit loginforsøg slår fejl, skal du kontakte din it-supportafdeling.</li>
</ul>

<h3>Din browsers kapacitet</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> Du kan uploade vilkårligt store filer, dog max. {size:cfg:max_transfer_size} i hver overførsel.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> Den maksimale størrelse på hver uploadet fil er {size:cfg:max_legacy_file_size}, og der kan i hver overførsel højst uploades {size:cfg:max_transfer_size}.</li>
</ul>

<h3><i>Vilkårligt store</i> uploads med HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Du kan bruge den her metode hvis mærket <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> er synligt ovenfor.</li>
    <li><i class="fa-li fa fa-caret-right"></i>For at kunne bruge den her funktionalitet skal man blot have installeret en browser som understøtter HTML5, den seneste udgave af "webbens sprog".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Opdaterede udgaver af Firefox og Chrome på Windows, Mac OS X og Linux vides at fungere.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Man kan <strong>genoptage</strong> en afbrudt eller annulleret upload. For at gentoptage en upload skal man blot <strong>sende nøjagtig de samme filer</strong> en gang til!
        Sørg for at filerne har nøjagtig <strong>samme navne og størrelse</strong> som før.
        Når din upload begynder på ny, bør du kunne se fremgangsbjælken springe dertil hvor uploaden blev afbrudt, og fortsætte derfra.
    </li>
</ul>

<h3>Upload filer hver op til {size:cfg:max_legacy_file_size} stor uden HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender advarer dig hvis du forsøger at uploade en fil som er større end tilladt.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Uploads kan ikke genoptages med den her metode.</li>
</ul>

<h3>Hent vilkårligt store filer</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Enhver moderne browser bør fungere; hentning kræver ingen særlige faciliteter i browseren.</li>
</ul>

<h3>Begrænsningsindstillinger</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalt antal modtagere: </strong>{cfg:max_transfer_recipients} e-mailadresser adskilt ved komma eller semikolon</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalt antal filer i hver overførsel: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalt størrelse på hver overførsel: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimal filstørrelse for browsere uden HTML5: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Hver overførsel udløber efter: </strong>{cfg:default_transfer_days_valid} (højst {cfg:max_transfer_days_valid}) dage</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Gæsterettigheder udløber efter: </strong>{cfg:default_guest_days_valid} (højst {cfg:max_guest_days_valid}) dage</li>
</ul>

<h3>Tekniske detaljer</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> bruger <a href="http://www.filesender.org/" target="_blank">FileSender-software</a>.
        FileSender angiver i en given browser om HTML5-uploadmetoden er understøttet.
        Dét afhænger først og fremmest af om avanceret browserfunktionalitet er til rådighed, især HTML5-FileAPI'et.
        På websitet <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> kan man følge udbredelsen af understøttelsen af HTML5-FileAPI'et i alle større browsere.
        Især understøttelse for <a href="http://caniuse.com/filereader" target="_blank">FileReader-API'et</a> og <a href="http://caniuse.com/bloburls" target="_blank">Blob-URL'er</a> skal stå med grøn lampe (=understøttet) for at browseren kan uploade filer større end {size:cfg:max_legacy_file_size} hver.
        Vær opmærksom på at Opera 12 lige nu faktisk ikke understøtter alt hvad der kræves til FileSenders HTLM5-uploadfunktionalitet, selvom den står på listen over browsere som understøtter HTLM5-FileAPI'et.
    </li>
</ul>

<p>Besøg <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a> for nærmere oplysninger.</p>