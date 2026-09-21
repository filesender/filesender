<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Herzlich willkommen bei {cfg:site_name}</h1>
<p>
Damit dieser Service funktionieren kann, müssen einige Informationen bzgl. der Dateien und wer darauf Zugriff hat gespeichert werden. Übertragene Dateien werden automatisch vom System gelöscht, sobald deren Gültigkeit abgelaufen ist. Andere zugehörige Informationen werden ebenfalls nach einer gewissen Zeit gelöscht. Auf dieser Seite können Sie sehen, wie lange welche Informationen gespeichert werden.</p>
<p>
Beachten Sie, dass alle einer Übertragung zugehörigen Dateien und Kopien versendeter E-Mails mit dieser gelöscht werden.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Diese Webseite ist so konfiguriert, dass hochgeladene Dateien beim Löschen geschreddert werden.";
    echo "Beim Schreddern einer Datei werden nach dem Löschen Daten an die Position der ursprünglichen Dateien auf der Festplatte geschrieben. ";
    echo "Dies geschieht mehrfach, sodass wirklich keine Daten mehr wiederherstellbar sind.";
    echo "Dadurch wird der Datenschutz zusätzliche verbessert.</p>";
}
?>
