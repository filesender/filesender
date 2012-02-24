<?php

$lang["_HELP_TEXT"] = '

<div>

<div align="left" style="padding:5px">

<h4>Login</h4> 
<ul>
    <li>Falls Sie Ihre Institution nicht auf der Liste der Identity Provider (IdPs) sehen, oder Ihr Login mit jenem Username und Passwort fehlschlägt, benachrichtigen Sie bitte Ihren lokalen IT Dienst.</li>
</ul>

<h4>Hochladen von Dateien mit einer Größe von weniger als 2 Gigabyte (2 GB) mit Adobe Flash</h4>
<ul>
	<li>Wenn Sie Youtube Videos anschauen können, sollte diese Methode für Sie funktionieren</li>
	<li>Sie benötigen einen modernen Browser mit Version 10 (oder höher) von <a target="_blank" href="http://www.adobe.com/de/software/flash/about/">Adobe Flash</a></li>
	<li>FileSender wird Sie warnen, falls Sie versuchen eine Datei hochzuladen die zu groß für diese Methode ist</li>
</ul>

<h4>Hochladen von Dateien <i>beliebiger Größe</i> mit HTML5</h4>
<ul>
        <li>Wenn Sie <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> in der rechten oberen Ecke sehen, funktioniert dies Methode für Sie</li>
	<li>Sie brauchen einen sehr neuen Browser der HTML5 unterstützt, die neueste Version der "Sprache des Web"</li>
	<li>Momentan trifft das auf Firefox 4 (oder höher) und Chrome in Windows, Mac OSX und Linux zu</li>
	<li>Bitte benutzen Sie die <a href="http://caniuse.com/#feat=fileapi" target="_blank">"When can I use..."</A> Webseite um den Fortschritt der HTML5 FileAPI in allen größeren Browsern zu verfolgen.  Speziell die Unterstützung für die <a href="http://caniuse.com/#feat=filereader" target="_blank">FileReader API</A> und die <A href="http://caniuse.com/#feat=bloburls" target="_blank">Blob URLs</A> müssen hellgrün sein (=unterstützt) damit ein Browser Dateien die grösser als 2GB sind hochladen kann </li>
</ul>

<h4>Herunterladen von Dateien beliebiger Größe</h4>
<ul>
        <li>Sie brauchen nur einen modernen Browser, Adobe Flash oder HTML5 werden <b>nicht</b> benötigt fürs Herunterladen</li>
</ul>


<h4>Einschränkungen dieser FileSender Installation</h4>
<ul>
    <li><strong>
      Maximale Anzahl der E-mail Empfänger: </strong>'. $config["max_email_recipients"].' verschiedene E-mail Adressen (getrennt durch Komma oder Strichpunkt)</li>
    <li><strong>Maximale Anzahl der Dateien beim Hochladen:</strong> Eine - um mehrere Dateien gleichzeitig hochzuladen, verpacken Sie diese zuerst zu einem Zip-Archiv</li>
    <li><strong>Maximale Dateigröße beim Hochladen, mit Adobe Flash: </strong>'. formatBytes($config["max_flash_upload_size"]).' </li>
    <li><strong>Maximale Dateigröße beim Hochladen, mit HTML5: </strong>'. formatBytes($config["max_html5_upload_size"]).'</li>
    <li><strong>Maximale Ablaufzeit für Dateien und Voucher: </strong>'. $config["default_daysvalid"].' Tage </li>
</ul>
<p>Mehr Informationen über FileSender gibt es auf der folgenden Internetseite: <a href="http://www.filesender.org/">www.filesender.org</a></p>
</div>
</div>';

$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlentities($config['site_name']) .' ist eine Installation von FileSender (<a rel="nofollow" href="http://www.filesender.org/">www.filesender.org</a>), entwickelt für die Anforderungen der Hochschul- und Forschungsgemeinschaften.</div>';

$lang["_AUPTERMS"] = "Nutzungsbedingungen...";

?>
