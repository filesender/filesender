<h3>Login</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
Sie melden sich an, indem Sie Ihr Standard-Anmelde-Konto beim entsprechenden Anmelde-System verwenden. Sollte Ihr Anmelde-System nicht aufgelistet sein oder Ihre Login fehlschlägt, wenden Sie sich bitte an Ihre IT.
</li>
</ul>

<h3>Was unterstützt Ihr Browser?</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="hochladen mittels HTML5 ist aktiviert" />Sie können Dateien jeder Größe bis zu einer Gesamtgröße von {size:cfg:max_transfer_size} pro Übertragung hochladen.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="hochladen mittels HTML5 ist inaktiv" />Sie können Dateien bis zu einer Größe von {size:cfg:max_legacy_file_size} bis zu einer Gesamtgröße von  {size:cfg:max_transfer_size} pro Übertragung hochladen.</li>
</ul>

<h3>Hochladen <i>jeder Größe</i> mittels HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
Sie werden in der Lage sein, diese Methode zu verwenden, wenn das <img src="images/html5_installed.png" alt="hochladen mittels HTML5 ist aktiviert" /> Symbol oben dargestellt ist.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
Um diese Methode zu aktivieren vewwenden Sie einfach einen aktuellen Browser, der HTML5, die neueste "Sprache des Internets", unterstützt.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
Aktuelle Versionen von Firefox und Chrome unter Windows, Mac OS X und Linux sollten funktionieren.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Sie können eine unterbrochene oder abgebrochene Übertragung <strong>fortsetzen</strong>. Um dies zu tun, schicken Sie einfach <strong>exakt die selben Dateien</strong> noch einmal!
        Stellen Sie sicher, dass die Dateien exakt <strong>die gleichen Namen und Dateigrößen</strong> wie vorher haben.
        Wenn der Upload beginnt, sollten Sie bemerken, dass der Fortschirittsbalken einen Sprung zur letzten Position macht und dann weitergeführt wird.
    </li>
</ul>

<h3>Hochladen von Dateien mit einer Größe bis zu  {size:cfg:max_legacy_file_size} ohne HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
FileSender wird Sie warnen, falls Sie versuchen, Dateien die größer sind zu übertragen.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Ein fortsetzen von abgebrochenen Übertragungen ist mit dieser Methode nicht möglich.</li>
</ul>

<h3>Herunterladen aller Dateien</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
Jeder moderne Browser wird funktionieren. Es gibt zum Herunterladen der Dateien keine besonderen Anforderungen.</li>
</ul>

<h3>Konfigurierte Einschränkungen</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Anzahl an Empfängern: </strong>{cfg:max_transfer_recipients} E-Mail-Adressen mittels Komma oder Semikolon getrennt</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Anzahl an Dateien: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Gesamtgröße einer Übertragung: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Dateigröße bei Browsern ohne HTML5-Unterstützung: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Gültigkeit einer Übertragung: </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid}) Tage</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Gültigkeit einer Einladung: </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid}) Tage</li>
</ul>

<h3>Technische Details</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> verwendet die Software <a href="http://www.filesender.org/" target="_blank">FileSender</a>.
        FileSender zeigt im entsprechenden Browser an, ob das Hochladen mittels HTML5 möglich ist oder nicht.
        Dies hängt hauptsächlich davon ab, ob Ihr Browser die erweiterten Funktionen der HTML5 FileAPI unterstützt.
        Bitte verwenden Sie die Webseite <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> um den Fortschritt der Implementation des HTML5 FileAPI in allen wichtigen Browsern zu beobachten.
        Insbesonder ist die Unterstützung des <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> und der  <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> notwendig. Diese sollten für Ihren Browser hellgrün (=unterstützt) hervorgehoben sein, um ein Hochladen von Dateien die größer sind als {size:cfg:max_legacy_file_size} zu ermöglichen.
        Bitte beachten Sie, dass obwohl laut dieser Liste Opera 12 das HTML5 FileAPI unterstützt, dieser Browser aktuell nicht alle notwendigen Funktionen für ein Hochladen mittels HTML5 durch den FileSender implementiert hat.
    </li>
</ul>

<p>Für weitere Informationen besuchen Sie bitte <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
