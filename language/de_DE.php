<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* ---------------------------------
 * de_DE Language File
 * Contributed by Claude Tompers (RESTENA)
 * ---------------------------------
 */
// main menu items
$lang["_ADMIN"] = "Administration";
$lang["_NEW_UPLOAD"] = "Datei senden";
$lang["_VOUCHERS"] = "Gast einladen";
$lang["_LOGON"] = "Einloggen";
$lang["_LOG_OFF"] = "Abmelden";
$lang["_MY_FILES"] = "Meine Dateien";

// page titles
$lang["_HOME"] = "Startseite";
$lang["_ABOUT"] = "Impressum";
$lang["_HELP"] = "Hilfe";
$lang["_DELETE_VOUCHER"] = "Voucher löschen";
$lang["_UPLOAD_COMPLETE"] = "Ihre Datei wurde hochgeladen und eine Nachricht geschickt.";
$lang["_UPLOAD_PROGRESS"] = "Fortschritt beim Hochladen";
$lang["_DOWNLOAD"] = "Herunterladen";
$lang["_CANCEL_UPLOAD"] = "Hochladen abbrechen";

// admin
$lang["_PAGE"] = "Seite";
$lang["_UP"] = "Hoch";
$lang["_DOWN"] = "Runter";
$lang["_FILES"] = "Dateien";
$lang["_DRIVE"] = "Laufwerk";
$lang["_TOTAL"] = "Gesamt";
$lang["_USED"] = "Benutzt";
$lang["_AVAILABLE"] = "Verfügbar";
$lang["_TEMP"] = "Temp"; // as in Temporary files

// admin tab names
$lang["_GENERAL"] = "Allgemein";
$lang["_UPLOADS"] = "Hochgeladene Dateien";
$lang["_DOWNLOADS"] = "Heruntergeladene Dateien";
$lang["_ERRORS"] = "Fehler";
$lang["_FILES_AVAILABLE"] = "Verfügbare Dateien";
$lang["_ACTIVE_VOUCHERS"] = "Aktive Voucher";
$lang["_COMPLETE_LOG"] = "Komplettes Ereignisprotokoll";


// Form Fields
$lang["_TO"] = "An";
$lang["_FROM"] = "Von";
$lang["_SIZE"] = "Größe";
$lang["_CREATED"] = "Erstellt";
$lang["_FILE_NAME"] = "Dateiname";
$lang["_SUBJECT"] = "Betreff";
$lang["_EXPIRY"] = "Ablaufdatum";
$lang["_MESSAGE"] = "Nachricht";
$lang["_TYPE"] = "Typ";

$lang["_TERMS_OF_AGREEMENT"] = "Nutzungsbedingungen";
$lang["_SHOW_TERMS"] = "Nutzungsbedingungen anzeigen";
$lang["_SHOWHIDE"] = "Anzeigen/Verstecken";
$lang["_UPLOADING_WAIT"] = "Datei wird hochgeladen - bitte warten...";

// Flash button menu
$lang["_UPLOAD"] = "Datei senden";
$lang["_BROWSE"] = "Durchsuchen";
$lang["_CANCEL"] = "Abbrechen";
$lang["_OPEN"] = "Öffnen";
$lang["_CLOSE"] = "Schließen";
$lang["_OK"] = "OK";
$lang["_SEND"] = "Senden";
$lang["_DELETE"] = "Löschen";
$lang["_YES"] = "Ja";
$lang["_NO"] = "Nein";

$lang["_ERROR_CONTACT_ADMIN"] = "Ein Fehler ist aufgetreten - bitte benachrichtigen Sie Ihren Administrator.";
$lang["_INVALID_MISSING_EMAIL"] = "Ungültige oder fehlende E-Mail-Adresse";
$lang["_INVALID_EXPIRY_DATE"] = "Ungültiges Ablaufdatum";
$lang["_INVALID_FILE"] = "Ungültige Datei";
$lang["_INVALID_FILEVOUCHERID"] = "Ungültige Datei oder Voucher ID";
$lang["_INVALID_FILESIZE_ZERO"] = "Dateigrösse darf nicht 0 sein. Bitte wählen Sie eine andere Datei.";
$lang["_INVALID_FILE_EXT"] = "Ungültige Dateierweiterung.";
$lang["_INVALID_TOO_LARGE_1"] = "Datei darf nicht grösser sein als";
$lang["_AUTH_ERROR"] = "Sie sind nicht länger authentifiziert. <br/>Ihre Sitzung is möglicherweise abgelaufen oder es gab einen Serverfehler. <br /><br />Bitte loggen Sie sich neu ein und versuchen Sie es nochmals.";
$lang["_SELECT_ANOTHER_FILE"] = "Bitte wählen Sie eine andere Datei.";
$lang["_INVALID_VOUCHER"] = "Dieser Voucher is nicht länger gültig. <br />Bitte benachrichtigen Sie die Person die diesen Voucher erstellt hat.";
$lang["_SELECT_FILE"] = "Datei wählen";
$lang["_INVALID_FILE_NAME"] = "Dieser Dateiname ist ungültig. Bitte benennen Sie die Datei um und versuchen Sie es nochmals.";
$lang["_INVALID_SIZE_USEHTML5"] = "Bitte wählen Sie eine andere Datei oder benutzen Sie einen HTML5-fähigen Browser zum Hochladen grösserer Dateien.";
$lang["_ACCEPTTOC"] = "Ich bin mit den Nutzungsbedingungen einverstanden.";
$lang["_AGREETOC"] = "Sie müssen den Nutzungsbedingungen zustimmen, wenn Sie die Datei herunterladen wollen.";
$lang["_FILE_TO_BE_RESENT"] = "Datei zum weiterverteilen";
$lang["_ERROR_UPLOADING_FILE"] = "Fehler beim Hochladen Ihrer Datei";
$lang["_ERROR_SENDING_EMAIL"] = "Beim Senden der E-mail ist ein Fehler aufgetreten, bitte benachrichtigen Sie Ihren Administrator.";
$lang["_ERROR_INCORRECT_FILE_SIZE"] = "Beim Hochladen Ihrer Datei ist ein Problem aufgetreten. <br />Die Dateigrösse auf dem Server ist unterschiedlech von jener der Originaldatei. <br /><br />Bitte benachrichtigen Sie Ihren Administrator.";
$lang["_MAXEMAILS"] = "Die maximal erlaubte Zahl an E-Mail Adressen ist ";
$lang["_INVALID_DATE_FORMAT"] = "Das Datumsformat ist ungültig.";
$lang["_DISK_SPACE_ERROR"] = "Es ist nicht genügend Speicherplatz vorhanden. Bitte benachrichtigen Sie den Service Administrator oder laden Sie eine kleinere Datei hoch.";
$lang["_ERROR_ATTRIBUTES"] = "Ihr Identity Provider stellt die nötigen Attribute nicht zur Verfügung. Benachrichtigen Sie Ihren Administrator";
$lang["_PERMISSION_DENIED"] = "Sie sind nicht berechtigt dies zu tun.";
// Logout page
$lang["_LOGOUT_COMPLETE"] = "Logout abgeschlossen";

// vouchers
$lang["_SEND_NEW_VOUCHER"] = "Ein Voucher erlaubt es einem Anderen, Ihnen eine Datei zu schicken.<br />
Um einen Voucher zu erstellen, geben Sie seine Email Adresse ein und klicken sie auf 'Voucher senden'.<br />
Dem Empfänger wird eine E-Mail mit einem Link zum Voucher erhalten.";
$lang["_EMAIL_SEPARATOR_MSG"] = "Mehrere E-Mail Adressen trennen durch , oder ;";

$lang["_NO_FILES"] = "Im Augenblick sind keine Dateien verfügbar";
$lang["_NO_VOUCHERS"] = "Im Augenblick sind keine Voucher verfügbar";
$lang["_ARE_YOU_SURE"] = "Sind Sie sicher?";
$lang["_DELETE_FILE"] = "Datei löschen";
$lang["_EMAIL_SENT"] = "E-Mail wurde versandt";
$lang["_EXPIRY_DATE"] = "Ablaufdatum";
$lang["_FILE_SIZE"] = "Dateigröße";
$lang["_FILE_RESENT"] = "Datei wurde erneut versandt";
$lang["_MESSAGE_RESENT"] = "Nachricht wurd erneut versandt";
$lang["_ME"] = "Mir";
$lang["_SEND_VOUCHER"] = "Voucher senden";
$lang["_RE_SEND_EMAIL"] = "E-Mail nochmals senden";
$lang["_NEW_RECIPIENT"] = "Neuen Empfänger hinzufügen";
$lang["_SEND_VOUCHER_TO"] = "Voucher senden an";
$lang["_START_DOWNLOAD"] = "Herunterladen starten";
$lang["_VOUCHER_SENT"] = "Voucher gesandt";
$lang["_VOUCHER_DELETED"] = "Voucher gelöscht";
$lang["_VOUCHER_CANCELLED"] = "Dieser Voucher wurde widerrufen.";
$lang["_VOUCHER_USED"] = "Dieser voucher wurde bereits verwendet.";
$lang["_STARTED_DOWNLOADING"] = "Der Download Ihrer Datei sollte starten.";

// files
$lang["_FILE_DELETED"] = "Datei gelöscht";
// steps
$lang["_STEP1"] = "Geben Sie die E-Mail Adressen der Empfänger ein";
$lang["_STEP2"] = "Setzen Sie das Ablaufdatum";
$lang["_STEP3"] = "Suchen Sie Ihre Datei";
$lang["_STEP4"] = "Senden klicken";
$lang["_HTML5Supported"] = "Dateien über 2 GB können hochgeladen werden!";
$lang["_HTML5NotSupported"] = "Die maximale Dateigröße ist auf 2 GB beschränkt!";

$lang["_OPTIONAL"] = "optional";

// confirmation
$lang["_CONFIRM_DELETE_FILE"] = "Sind Sie sicher, dass Sie diese Datei löschen wollen?";
$lang["_CONFIRM_DELETE_VOUCHER"] = "Sind Sie sicher, dass Sie diesen Voucher löschen wollen?";
$lang["_CONFIRM_RESEND_EMAIL"] = "Sind Sie sicher, dass Sie diese E-mail nochmals senden wollen?";

// standard date display format
$lang['datedisplayformat'] = "d.m.Y"; // Format for displaying date/time, use PHP date() format string syntax

// datepicker localization
$lang["_DP_closeText"] = 'OK'; // Done
$lang["_DP_prevText"] = 'Zurück'; //Prev
$lang["_DP_nextText"] = 'Weiter'; // Next
$lang["_DP_currentText"] = 'Heute'; // Today
$lang["_DP_monthNames"] = "['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember']";
$lang["_DP_monthNamesShort"] = "['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun','Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez']";
$lang["_DP_dayNames"] = "['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag']";
$lang["_DP_dayNamesShort"] = "['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam']";
$lang["_DP_dayNamesMin"] = "['So','Mo','Di','Mi','Do','Fr','Sa']";
$lang["_DP_weekHeader"] = 'Wo';
$lang["_DP_dateFormat"] = 'dd.mm.yy';
$lang["_DP_firstDay"] = '1';
$lang["_DP_isRTL"] = 'false';
$lang["_DP_showMonthAfterYear"] = 'false';
$lang["_DP_yearSuffix"] = '';

// Login Splash text
$lang["_SITE_SPLASHHEAD"] = "Willkommen bei ". htmlspecialchars(Config::get('site_name'));
$lang["_SITE_SPLASHTEXT"] = htmlspecialchars(Config::get('site_name')) ." ist eine sichere Methode grosse Dateien mit jedermann zu teilen! Verbinden Sie sich um Dateien zu verschicken oder um jemanden aufzufordern Ihnen eine Datei zu schicken.";

// Footer to display
$lang["_SITE_FOOTER"] = ""; 

// site help
$lang["_HELP_TEXT"] = '

<div>

<div style="padding: 5px; text-align: left;">

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
	<li>Bitte benutzen Sie die <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> Webseite um den Fortschritt der HTML5 FileAPI in allen größeren Browsern zu verfolgen.  Speziell die Unterstützung für die <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> und die <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> müssen hellgrün sein (=unterstützt) damit ein Browser Dateien die grösser als 2GB sind hochladen kann </li>
</ul>

<h4>Herunterladen von Dateien beliebiger Größe</h4>
<ul>
        <li>Sie brauchen nur einen modernen Browser, Adobe Flash oder HTML5 werden <b>nicht</b> benötigt fürs Herunterladen</li>
</ul>


<h4>Einschränkungen dieser FileSender Installation</h4>
<ul>
    <li><strong>
      Maximale Anzahl der E-mail Empfänger: </strong>'. Config::get('max_email_recipients').' verschiedene E-mail Adressen (getrennt durch Komma oder Strichpunkt)</li>
    <li><strong>Maximale Anzahl der Dateien beim Hochladen:</strong> Eine - um mehrere Dateien gleichzeitig hochzuladen, verpacken Sie diese zuerst zu einem Zip-Archiv</li>
    <li><strong>Maximale Dateigröße beim Hochladen, mit Adobe Flash: </strong>'. formatBytes(Config::get('max_flash_upload_size')).' </li>
    <li><strong>Maximale Dateigröße beim Hochladen, mit HTML5: </strong>'. formatBytes(Config::get('max_html5_upload_size')).'</li>
    <li><strong>Maximale Ablaufzeit für Dateien und Voucher: </strong>'. Config::get('default_daysvalid').' Tage </li>
</ul>
<p>Mehr Informationen über FileSender gibt es auf der folgenden Internetseite: <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
</div>
</div>';

// site about
$lang["_ABOUT_TEXT"] = ' <div style="padding: 5px; text-align: left;">'. htmlspecialchars(Config::get('site_name')) .' ist eine Installation von FileSender (<a rel="nofollow" href="http://www.filesender.org/" target="_blank">www.filesender.org</a>), entwickelt für die Anforderungen der Hochschul- und Forschungsgemeinschaften.</div>';

// site AUP terms
$lang["_AUPTERMS"] = "Nutzungsbedingungen...";

?>
