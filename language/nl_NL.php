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
 * nl_NL Language File
 * Maintained by the FileSender Core Team
 * ---------------------------------
 * 
 */
// Hoofdmenu items
$lang["_ADMIN"] = "Beheer";
$lang["_NEW_UPLOAD"] = "Nieuwe Upload";
$lang["_VOUCHERS"] = "Uitnodiging";
$lang["_LOGON"] = "Aanmelden";
$lang["_LOG_OFF"] = "Afmelden";
$lang["_MY_FILES"] = "Mijn bestanden";

// Pagina titels
$lang["_HOME"] = "Home";
$lang["_ABOUT"] = "Over";
$lang["_HELP"] = "Help";
$lang["_DELETE_VOUCHER"] = "Trek uitnodiging in";
$lang["_UPLOAD_COMPLETE"] = "Het bestand is succesvol geüpload en een download-uitnodiging is verzonden aan de ontvanger(s).";
$lang["_UPLOAD_COMPLETE_ACCOUNT_REFERENCE"] = "Zie <a href=\"index.php?s=files\">".$lang["_MY_FILES"]."</a> voor meer informatie.";
$lang["_UPLOAD_PROGRESS"] = "Voortgang Upload";
$lang["_DOWNLOAD"] = "Download";
$lang["_CANCEL_UPLOAD"] = "Annuleer Upload";

// Admin categorie namen

$lang["_PAGE"] = "Pagina";
$lang["_UP"] = "Terug";
$lang["_DOWN"] = "Verder";                        
$lang["_FILES"] = "Bestanden";
$lang["_DRIVE"] = "Disk";                        
$lang["_TOTAL"] = "Totaal";
$lang["_USED"] = "In gebruik";
$lang["_AVAILABLE"] = "Beschikbaar";
$lang["_TEMP"] = "Tijdelijk"; // as in Temporary files

// Admin tabblad namen
$lang["_GENERAL"] = "Algemeen";
$lang["_UPLOADS"] = "Uploads";
$lang["_DOWNLOADS"] = "Downloads";
$lang["_ERRORS"] = "Fouten";
$lang["_FILES_AVAILABLE"] = "Beschikbare Bestanden";
$lang["_ACTIVE_VOUCHERS"] = "Geldige uitnodigingen";
$lang["_COMPLETE_LOG"] = "Volledig logboek";

// Upload page
$lang["_WELCOME"] = "Welkom";
$lang["_WELCOMEGUEST"] = "Welkom Gast";

// Upload pagina: Formuliervelden
$lang["_TO"] = "Aan";
$lang["_FROM"] = "Van";
$lang["_SIZE"] = "Grootte";
$lang["_CREATED"] = "Aangemaakt";
$lang["_FILE_NAME"] = "Bestandsnaam";
$lang["_SUBJECT"] = "Onderwerp";
$lang["_MESSAGE"] = "Bericht";
$lang["_OPTIONAL"] = "Optioneel";
$lang["_EXPIRY"] = "Vervaldatum";
$lang["_EXPIRY_DATE"] = "Vervaldatum";
$lang["_TYPE"] = "Type";

$lang["_TERMS_OF_AGREEMENT"] = "Algemene voorwaarden";
$lang["_SHOW_TERMS"] = "Toon Voorwaarden";
$lang["_SHOWHIDE"] = "Toon/Verberg";
$lang["_SELECT_FILE"] = "Selecteer een bestand om te uploaden";
$lang["_UPLOADING_WAIT"] = "Uploaden bestand - even geduld a.u.b. ...";
$lang["_EMAIL_SEPARATOR_MSG"] = "Meerdere e-mailadressen gescheiden door, of ;";
$lang["_NO_FILES"] = "Er zijn momenteel geen bestanden beschikbaar";
$lang["_NO_VOUCHERS"] = "Er zijn momenteel geen uitnodigingen beschikbaar";

$lang["_AUPTERMS"] = "Algemene voorwaarden";
$lang["_ACCEPTTOC"] = "Ik ga akkoord met de bepalingen en voorwaarden van deze dienst.";	
$lang["_AGREETOC"] = "U moet akkoord gaan met de voorwaarden.";

$lang["_ERROR_INCORRECT_FILE_SIZE"] = "Er is een probleem opgetreden bij het uploaden van uw bestand. <br />De bestandsgrootte op de server wijkt af van de grootte van uw eigen kopie.<br /><br />Neem contact op met de beheerder.";
$lang["_MAXEMAILS"] = "Het maximum aantal toegestane e-mailadressen is ";
$lang["_INVALID_DATE_FORMAT"] = "De datum is in een ongeldig formaat.";
$lang["_DISK_SPACE_ERROR"] = "Er is niet genoeg vrije ruimte op deze service. Neemt u s.v.p. contact op met de beheerder of upload een kleiner bestand.";
$lang["_CRYPTO_NOT_SUPPORTED_ERROR"] = "Encryptie wordt niet ondersteund.";
$lang["_ERROR_ATTRIBUTES"] = "Uw Identity Provider levert niet de benodigde attributen. Neemt u s.v.p. contact op met uw lokale IT-afdeling";
$lang["_PERMISSION_DENIED"] = "U heeft geen rechten om dit te doen.";

// Vouchers
$lang["_SEND_NEW_VOUCHER"] = "Met een Uitnodiging kunt u iemand een bestand laten sturen.<br />
Om een Uitnodiging te maken, voer een e-mailadres in en klik op Stuur Uitnodiging.<br />
Er wordt dan een e-mail verstuurd naar de ontvanger met daarin een link om de uitnodiging te gebruiken.";
$lang["_SEND_VOUCHER_TO"] = "Stuur uitnodiging naar";
$lang["_SEND_VOUCHER"] = "Stuur uitnodiging";
$lang["_VOUCHER_USED"] = "Deze uitnodiging is reeds gebruikt.";

// My Files
$lang["_SHOW_ALL"] = "Toon/Verberg Details";
$lang["_DETAILS"] = "Details";
$lang["_DOWNLOADED"] = "Gedownload";

// Upload pagina: button menu
$lang["_UPLOAD"] = "Upload";
$lang["_BROWSE"] = "Blader";
$lang["_CANCEL"] = "Annuleer";
$lang["_OPEN"] = "Open";
$lang["_CLOSE"] = "Sluit";
$lang["_OK"] = "OK";
$lang["_SEND"] = "Verzend";
$lang["_DELETE"] = "Verwijder";
$lang["_YES"] = "Ja";
$lang["_NO"] = "Nee";
$lang["_CLICK_TO_SAVE_FILE"] = "Klik hier voor opslaan bestand";

// steps
$lang["_STEP1"] = "Voer één of meer e-mailadressen in";
$lang["_STEP2"] = "Stel een vervaldatum in";
$lang["_STEP3"] = "Selecteer een bestand";
$lang["_STEP4"] = "Selecteer Verzend";
$lang["_HTML5Supported"] = "Uploads groter dan 2GB mogelijk!";
$lang["_HTML5NotSupported"] = "Uploads groter dan 2GB niet mogelijk!";

// Upload page: error messages, displayed on-input
$lang["_ERROR_CONTACT_ADMIN"] = "Er is een probleem opgetreden, neem contact op met de beheerder.";	
$lang["_INVALID_MISSING_EMAIL"] = "Ongeldig of ontbrekend e-mailadres";
$lang["_INVALID_EXPIRY_DATE"] = "Ongeldige vervaldatum";
$lang["_INVALID_FILE"] = "Ongeldig bestand";
$lang["_INVALID_FILEVOUCHERID"] = "Ongeldig bestands- of uitnodigings-ID";	
$lang["_INVALID_FILESIZE_ZERO"] = "Ongeldige bestandsgrootte van 0 bytes. Kies een ander bestand.";
$lang["_INVALID_FILE_EXT"] = "Ongeldig bestandstype.";
$lang["_INVALID_TOO_LARGE_1"] = "Bestand kan niet groter zijn dan";
$lang["_AUTH_ERROR"] = "U bent niet meer aangemeld.<br />Wellicht is uw sessie verlopen of was er een probleem op de server.<br /><br />Logt u s.v.p. opnieuw in en probeer het nogmaals.";
$lang["_SELECT_ANOTHER_FILE"] = "Kies een ander bestand.";
$lang["_INVALID_VOUCHER"] = "Deze uitnodiging is niet meer geldig.<br />Neem contact op met degene die u uitnodigde.";
$lang["_INVALID_FILE_NAME"] = "Ongeldige bestandsnaam. Hernoem het bestand en probeer het opnieuw.";
$lang["_INVALID_SIZE_USEHTML5"] = "Selecteer een ander bestand of gebruik een geschikte HTML5-browser voor grotere bestanden.";
$lang["_FILE_TO_BE_RESENT"] = "Bestand om opnieuw te versturen";
$lang["_ERROR_UPLOADING_FILE"] = "Fout bij het uploaden van het bestand";
$lang["_ERROR_SENDING_EMAIL"] = "Er is een probleem opgetreden bij het verzenden van de e-mail, neem contact op met de beheerder.";
$lang["_LOGOUT_COMPLETE"] = "U bent afgemeld";

$lang["_ARE_YOU_SURE"] = "Weet u dit zeker?";
$lang["_DELETE_FILE"] = "Verwijder bestand";
$lang["_EMAIL_SENT"] = "E-mail verstuurd";
$lang["_FILE_SIZE"] = "Bestandsgrootte";
$lang["_FILE_RESENT"] = "Bestand opnieuw verstuurd";
$lang["_MESSAGE_RESENT"] = "Bericht opnieuw verstuurd";
$lang["_ME"] = "Mij";
$lang["_RE_SEND_EMAIL"] = "E-mail opnieuw versturen";
$lang["_NEW_RECIPIENT"] = "Ontvanger toevoegen";
$lang["_START_DOWNLOAD"] = "Start Download";
$lang["_VOUCHER_SENT"] = "Uitnodiging verstuurd";
$lang["_VOUCHER_DELETED"] = "Uitnodiging ingetrokken";
$lang["_VOUCHER_CANCELLED"] = "Deze uitnodiging is ingetrokken.";
$lang["_STARTED_DOWNLOADING"] = "De download van het bestand zal beginnen.";
$lang["_FILE_DELETED"] = "Dit bestand is verwijderd.";

// confirmation
$lang["_CONFIRM_DELETE_FILE"] = "Wilt u dit bestand echt verwijderen?";
$lang["_CONFIRM_DELETE_VOUCHER"] = "Wilt u deze uitnodiging echt intrekken?";
$lang["_CONFIRM_RESEND_EMAIL"] = "Wilt u deze e-mail echt opnieuw versturen?";

// standard date display format
$lang['datedisplayformat'] = "d-m-Y"; // Format for displaying date/time, use PHP date() format string syntax 

// datepicker localization
$lang["_DP_closeText"] = 'Sluiten'; // Done
$lang["_DP_prevText"] = '←'; //Prev
$lang["_DP_nextText"] = '→'; // Next
$lang["_DP_currentText"] = 'Vandaag'; // Today
$lang["_DP_monthNames"] = "['januari', 'februari', 'maart', 'april', 'mei', 'juni','juli', 'augustus', 'september', 'oktober', 'november', 'december']";
$lang["_DP_monthNamesShort"] = "['jan', 'feb', 'mrt', 'apr', 'mei', 'jun','jul', 'aug', 'sep', 'okt', 'nov', 'dec']";
$lang["_DP_dayNames"] = "['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag']";
$lang["_DP_dayNamesShort"] = "['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat']";
$lang["_DP_dayNamesMin"] = "['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za']";
$lang["_DP_weekHeader"] = 'Wk';
$lang["_DP_dateFormat"] = 'dd-mm-yy';
$lang["_DP_firstDay"] = '1';
$lang["_DP_isRTL"] = 'false';
$lang["_DP_showMonthAfterYear"] = 'false';
$lang["_DP_yearSuffix"] = '';

// Login Splash text
$lang["_SITE_SPLASHHEAD"] = "Welkom bij ". htmlspecialchars($config['site_name']);
$lang["_SITE_SPLASHTEXT"] = htmlspecialchars($config['site_name']) ." is een veilige manier om bestanden te delen met iedereen! Meld u aan om een bestand te versturen of om iemand uit te nodigen om een bestand te sturen."; 

// Footer to display
$lang["_SITE_FOOTER"] = ""; 

// site help
$lang["_HELP_TEXT"] = '
<div>
<div align="left" style="padding:5px">
<h4>Aanmelden</h4> 
<ul>
    <li>U kunt inloggen door middel van uw bestaande instellings-logingegevens; kies daartoe uw instellingsnaam uit de lijst van Identity Providers. Als uw instelling niet voorkomt in de lijst of u heeft problemen met het inloggen, neemt u dan alstublieft contact op met uw locale IT-helpdesk.</li>
</ul>

<h4>Uploads van <i>willekeurig welke grootte</i> met de HTML5-methode</h4>
<ul>
        <li>Als het <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> symbooltje vertoond wordt dan kunt u de HTML5-methode gebruiken.</li>
	<li>Hiervoor is een recente browserversie nodig die HTML5 ondersteunt, de nieuwste editie van de "taal van het web".</li>
	<li>Momenteel geldt dit in ieder geval voor Firefox 4 (en hoger) en Chrome op Windows, Mac OS X en Linux en voor Safari 6 (en hoger) op Mac OS X en IE 10 (en hoger) op Windows.</li>
	<li>Een ongewenst onderbroken upload kan <b><i>hervat</i></b> worden. Om een upload te hervatten vertuurt u eenvoudigweg exact hetzelfde bestand opnieuw. De voortgangs-indicator moet dan verspringen naar het percentage waar de upload eerder was gestopt, en dan de upload voortzetten. <br /><br />
Als u tussentijds het bestand <b><i>gewijzigd</i></b> hebt, hernoem het dan eerst alvorens een nieuwe upload te starten, zodat de upload begint bij het begin van het nieuwe bestand.</li>
</ul>

<h4>Downloads van willekeurig welke grootte</h4>
<ul>
        <li>Hiervoor heeft u alleen een moderne browser nodig; u hoeft zich geen zorgen te maken over Adobe Flash of HTML5 - die zijn alleen van belang bij uploads, voor downloads is niets speciaals vereist.</li>
</ul>

<h4>Uploads kleiner dan 2 Gigabyte (2GB) via Adobe Flash</h4>
<ul>
	<li>Als u YouTube-video\'s kunt bekijken dan zou deze methode ook moeten werken.</li>
	<li>U heeft een moderne browser nodig met minimaal versie 10 van <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash.</a></li>
	<li><i>'. htmlspecialchars($config['site_name']) .'</i> waarschuwt u als u een bestand wilt uploaden dat te groot is voor deze methode.</li>
	<li>Het hervatten van afgebroken uploads is met deze methode niet mogelijk.</li>
</ul>



<h4>Instellingen van deze dienst</h4>
<ul>
    <li><strong>
      Maximum aantal e-mail-ontvangers:</strong> Tot '. $config["max_email_recipients"].' e-mailadressen gescheiden door een komma of puntkomma</li>
    <li><strong>Maximum aantal bestanden per upload:</strong> &eacute;&eacute;n - om meerdere bestanden ineens te versturen, kunt u ze eerst samenpakken in een archiefbestand zoals zip</li>
    <li><strong>Maximum bestandsgrootte per upload, alleen gebruikmakend van Adobe Flash:</strong> '. formatBytes($config["max_flash_upload_size"]).'</li>
    <li><strong>Maximum bestandsgrootte per upload, via HTML5:</strong> '. formatBytes($config["max_html5_upload_size"]).'</li>
    <li><strong>Maximum geldigheidsduur van bestanden en uitnodigingen:</strong> '. $config["default_daysvalid"].' dagen</li>
</ul>

<h4>Technische details</h4>
<ul>
	<li><i>'. htmlspecialchars($config['site_name']) .'</i> maakt gebruik van de <a href="http://www.filesender.org/" target="_blank">FileSender software</a>. FileSender geeft aan of de HTML5 upload-methode ondersteund wordt voor de op dat moment gebruikte browser. Deze ondersteuning is voornamelijk afhankelijk van de beschiklbaarheid van geavanceerde browserfunctionaliteit, met name de HTML5 FileAPI. De website <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> geeft bruikbare informatie om de voortgang van de implementatie van HTML5 FileAPI in 
de belangrijkste browsers te volgen. Met name ondersteuning voor de <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> en voor <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> moet groen licht (=ondersteund) krijgen, wil een browser uploads groter dan 2GB kunnen doen.  Belangrijk: alhoewel Opera 12 wordt weergegeven als zou het de HTML5 FileAPI volledig ondersteunen is gebleken dat Opera 12 desondanks niet voldoende elementen van de FileAPI ondersteunt om al gebruik te kunnen maken van de HTML5 upload-methode.</li>
</ul>
<p>Voor meer informatie, bezoek <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a>.</p>
</div>
</div>';


// site about
$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlspecialchars($config['site_name']) .' is een instantie van FileSender (<a rel="nofollow" href="http://www.filesender.org/" target="_blank">www.filesender.org</a>), ontwikkeld om te voldoen aan de eisen van de hoger onderwijs en onderzoeksgemeenschap.</div>';

// Added for crypto
$lang["_DOWNLOAD_PROGRESS"] = "Voortgang Download";
$lang["_CANCEL_DOWNLOAD"] = "Annuleer Download";
$lang["_ERROR_MESSAGE"] = "Kon niet doorgaan vanwege fouten";
$lang["_ENCRYPTION"] = "Versleuteling";
$lang["_ENCRYPT_FILE"] = "Versleutel dit bestand";
$lang["_ENCRYPT_PASSWDPROMPT"] = "Voer het wachtwoord in";
$lang["_DECRYPTION"] = "Ontsleuteling";
$lang["_DECRYPT_FILE"] = "Ontsleutel dit bestand";
$lang["_DECRYPT_PASSWDPROMPT"] = "Voer het wachtwoord in";
$lang["_FILE_IS_ENCRYPTED"] = "Dit bestand is versleuteld";
$lang["_ENCRYPT_DOWNLOAD_NOT_POSSIBLE"] = "Het downloaden van versleutelde bestanden is niet mogelijk met uw browser";
$lang["_ENCRYPT_DOWNLOAD_NOT_SUPPORTED"] = "Het downloaden van versleutelde bestanden wordt niet ondersteund.";
$lang["_ENCRYPT_PASSWORD_NOTE"] = "Let op: zorg ervoor dat de ontvanger deze sleutel ontvangt om het bestand te kunnen ontcijferen. <b>Het wordt afgeraden om dit per mail te versturen aan de ontvanger (:1)!<b>!";
$lang["_MISSING_PASSWORD"] = "Fout: er moet een wachtwoord worden ingevuld.";
$lang["_UPLOAD_ENCRYPT_PROGRESS_MESSAGE"] = "Het versleutelen en uploaden van het bestand kan even duren.";
$lang["_UPLOAD_COMPLETE_ENCRYPTED"] = "Het bestand is versleuteld. Vergeet niet om het wachtwoord op een veilige manier aan de ontvanger te geven. Zonder het wachtwoord is de download niet mogelijk";
$lang["_RANDOM_NOT_READY"] = "Random number is nog niet klaar; :1% voortgang. Probeer het over een paar seconden nogmaals.";
$lang["_GENERATE_RANDOM"] = "Genereer";
$lang["_ENCSIZE_WARNING"] = "Het bestand ':1' is te groot om als versleuteld bestand verwerkt te kunnen worden.";		
?>
