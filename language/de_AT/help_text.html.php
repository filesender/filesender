<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Willkommen bei FileSender</h3>

<p>
    FileSender ist eine webbasierte Applikation, die es authentifizierten Benutzern
    erlaubt, sicher und einfach große Dateien an andere zu verschicken.
    Benutzern ohne Login kann von einem authentifizierten Benutzer eine Einladung
    geschickt werden, sodass diese dann ebenfalls Dateien verschicken können.
    FileSender wurde für die Anforderungen von Hochschulen und Forschungseinrichtungen entwickelt.
</p>

<h4>Für Gäste...</h4>

<p>
    Wenn Sie eine Einladung von dieser Website bekommen haben,
    können Sie ein- oder mehrmals Dateien verschicken.
    Alle notwendigen Informationen sind in der Einladungs-E-Mail enthalten.
    Wenn Sie als Gast eine Datei hochladen, vergewissern Sie sich,
    dass Sie den Einladungs-Link von einer Institution erhalten haben,
    der Sie vertrauen. Wenn Sie keine Einladung zum Upload von Dateien
    von dieser Institution erwarten, handelt es sich möglicherweise
    um eine nicht legitimierte E-Mail.
</p>
<p>
    Der Benutzer, der Sie eingeladen hat, dieses System zu nutzen,
    hat dies getan, um Ihnen das Hochladen von Dateien zu ermöglichen.
    Dadurch können die Empfänger per Link diese Dateien herunterladen.
    Die Empfänger können ggf. durch Angabe der jeweiligen E-Mail-Adresssen
    selbst bestimmt werden.
</p>

<h4>Für registrierte Benutzer...</h4>

<p>
    Wenn sich diese Installation von FileSender in Ihrer Institution
    befindet, sollten Sie sich über den Login-Button oben rechts
    mit Ihren Standard-Account-Daten anmelden können.
    Sollten Sie unsicher sein, welche Login-Daten Sie verwenden müssen,
    setzen Sie sich bitte mit Ihrem IT-Support in Verbindung.
</p>

<p>
    Als registrierter Benutzer sollten Sie die Möglichkeit haben,
    Dateien hochzuladen. Sie können dabei Empfänger per E-Mail automatisch
    benachrichtigen oder sich einen Link geben lassen, sodass
    Sie diesen selbst z.B. per E-Mail weiter geben können.
    Außerdem sollte es möglich sein, dass Sie andere Personen als Gast einladen können oder Ihnen Dateien per FileSender zu schicken.
</p>

<h3>Mögliche Limitierungen beim Herunterladen von Dateien</h3>

<p>
    Jeder moderne Browser wird funktionieren. Es gibt zum Herunterladen der Dateien keine besonderen Anforderungen.</li>
</p>

<h3>Mögliche Limitierungen beim Hochladen von Dateien</h3>

<p>
    Wenn Ihr Browser HTML5 unterstützt, dann sollte es Ihnen möglich sein,
    Dateien bis zu einer Größe von {size:cfg:max_transfer_size} hoch zu laden.
    Aktuelle Versionen von Firefox und Chrome unter
    Windows, macOS und Linux unterstützen HTML5.
</p>

<h3>Was unterstützt Ihr Browser?</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="hochladen mittels HTML5 ist aktiviert" />Sie können Dateien jeder Größe bis zu einer Gesamtgröße von {size:cfg:max_transfer_size} pro Dateiübertragung hochladen.</li
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="hochladen mittels HTML5 ist inaktiv" />Sie können Dateien bis zu einer Größe von {size:cfg:max_legacy_file_size} bis zu einer Gesamtgröße von  {size:cfg:max_transfer
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
        Sie können einen unterbrochenen oder abgebrochenen Dateiübertragung  <strong>fortsetzen</strong>. Um dies zu tun, schicken Sie einfach <strong>exakt die selben Dateien</strong> noch einmal!
        Stellen Sie sicher, dass die Dateien exakt <strong>die gleichen Namen und Dateigrößen</strong> wie vorher haben.
        Wenn der Upload beginnt, sollten Sie bemerken, dass der Fortschirittsbalken einen Sprung zur letzten Position macht und dann weitergeführt wird.
    </li>
</ul>

<h3>Hochladen von Dateien mit einer Größe bis zu  {size:cfg:max_legacy_file_size} ohne HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
FileSender wird Sie warnen, falls Sie versuchen, Dateien die größer sind zu übertragen.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Ein fortsetzen von abgebrochenen Dateiübertragungen  ist mit dieser Methode nicht möglich.</li>
</ul>


<h3>Konfigurierte Einschränkungen</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Anzahl an Empfängern: </strong>{cfg:max_transfer_recipients} E-Mail-Adressen mittels Komma oder Semikolon getrennt</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Anzahl an Dateien: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Gesamtgröße einer Dateiübertragung: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Maximale Dateigröße bei Browsern ohne HTML5-Unterstützung: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>
Gültigkeit einer Dateiübertragung: </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid}) Tage</li>
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
        Insbesonder ist die Unterstützung des <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> und der  <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> notwendig. Diese sollten für Ihren B
        Bitte beachten Sie, dass obwohl laut dieser Liste Opera 12 das HTML5 FileAPI unterstützt, dieser Browser aktuell nicht alle notwendigen Funktionen für ein Hochladen mittels HTML5 durch den FileSender implementiert hat.
    </li>
</ul>

<p>Für weitere Informationen besuchen Sie bitte <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
