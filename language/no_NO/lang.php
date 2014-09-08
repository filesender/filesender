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
 * no_NO Language File
 * Maintained by the FileSender Core Team
 * ---------------------------------
 * 
 */
// Main meny-elementene
$lang["_ADMIN"] = "Administrasjon";
$lang["_NEW_UPLOAD"] = "Ny filsending";
$lang["_VOUCHERS"] = "Gjeste-invitasjon";
$lang["_LOGON"] = "Logg på";
$lang["_LOG_OFF"] = "Logg av";
$lang["_MY_FILES"] = "Mine filer";

// Siden titler
$lang["_HOME"] = "Hjem";
$lang["_ABOUT"] = "Om";
$lang["_HELP"] = "Hjelp";
$lang["_DELETE_VOUCHER"] = "Kanseller invitasjon";
$lang["_UPLOAD_COMPLETE"] = "Opplasting ferdig";
$lang["_UPLOAD_PROGRESS"] = "Opplastingsframgang";
$lang["_DOWNLOAD"] = "Last ned";
$lang["_CANCEL_UPLOAD"] = "Avbryt opplasting";

// Admin meny


// Admin kategorien navn

$lang["_PAGE"] = "Side";
$lang["_UP"] = "Opp";
$lang["_DOWN"] = "Ned";                        
$lang["_FILES"] = "Filer";
$lang["_DRIVE"] = "Disk";                        
$lang["_TOTAL"] = "Totall";
$lang["_USED"] = "I bruk";
$lang["_AVAILABLE"] = "Tilgjengelig";
$lang["_TEMP"] = "Midlertidig"; // as in Temporary files

// Admin interface: tab names
$lang["_GENERAL"] = "Generelt";
$lang["_UPLOADS"] = "Opplastninger";
$lang["_DOWNLOADS"] = "Nedlastninger";
$lang["_ERRORS"] = "Feil";
$lang["_FILES_AVAILABLE"] = "Tilgjengelige filer";
$lang["_ACTIVE_VOUCHERS"] = "Aktive invitasjoner";
$lang["_COMPLETE_LOG"] = "Alle logdata";

// Upload page: Form Fields
$lang["_TO"] = "Til";
$lang["_FROM"] = "Fra";
$lang["_SIZE"] = "Størrelse";
$lang["_CREATED"] = "Opprettet";
$lang["_FILE_NAME"] = "Filnavn";
$lang["_SUBJECT"] = "Emne";
$lang["_MESSAGE"] = "Melding";
$lang["_OPTIONAL"] = "opsjonell";
$lang["_EXPIRY"] = "Utløpsdato";
$lang["_EXPIRY_DATE"] = "Utløpsdato";
$lang["_TYPE"] = "Type";

$lang["_TERMS_OF_AGREEMENT"] = "Brukervilkår";
$lang["_SHOW_TERMS"] = "Vis vilkår";
$lang["_INVALID_VOUCHER"] = "Invitasjonen kan ikke lenger brukes. <br />Ta kontakt med personen som sendte deg invitasjonen for å få en ny en.";
$lang["_SELECT_FILE"] = "Velg en fil til å laste opp";
$lang["_UPLOADING_WAIT"] = "Laster opp fil - vennligst vent ...";
$lang["_EMAIL_SEPARATOR_MSG"] = "Flere epost-adresser avskilles med , eller ;";

$lang["_ACCEPTTOC"] = "Jeg aksepterer vilkårene for denne tjenesten.";	
$lang["_AGREETOC"] = "Du må akseptere vilkårene.";
$lang["_SHOWHIDE"] = "Vis/gjem";

// Upload page: Flash button menu
$lang["_UPLOAD"] = "Last opp";
$lang["_BROWSE"] = "Velg fil";
$lang["_CANCEL"] = "Avbryt";
$lang["_OPEN"] = "Open";
$lang["_CLOSE"] = "Lukk";
$lang["_OK"] = "OK";
$lang["_SEND"] = "Send";
$lang["_DELETE"] = "Slett";
$lang["_YES"] = "Ja";
$lang["_NO"] = "Nei";

// Upload page: error messages, displayed on-input
$lang["_INVALID_MISSING_EMAIL"] = "Feilaktig eller manglende epostadresse";
$lang["_INVALID_EXPIRY_DATE"] = "Feil i utløpsdato";
$lang["_INVALID_FILE"] = "Noe er galt med filen som ble valgt";
$lang["_INVALID_FILEVOUCHERID"] = "Ugyldig fil-ID eller invitasjons-ID";
$lang["_INVALID_FILESIZE_ZERO"] = "Filer med størrelse 0 kan ikke velges.  Velg en annen fil";
$lang["_INVALID_FILE_EXT"] = "Filtypen ikke tillatt.";
$lang["_INVALID_TOO_LARGE_1"] = "Filstørrelse kan ikke være mer enn";
$lang["_AUTH_ERROR"] = "Du er ikke lenger pålogget.<br />Din sesjon kan ha gått ut eller det har blitt en feil på tjeneren. <br /><br />Logg på igjen og prøv igjen.";
$lang["_SELECT_ANOTHER_FILE"] = "Velg en annen fil.";
$lang["_INVALID_FILE_NAME"] = "Feilaktig filnavn, filen kan ikke lastes opp.  Gi filen et annet navn og prøv på nytt.";
$lang["_INVALID_SIZE_USEHTML5"] = "For å sende filer større enn 2GB trenger du en nettleser som støtter HTML5, som f.eks. Chrome og nyerenyer FireFox.";

$lang["_ERROR_CONTACT_ADMIN"] = "Det oppsto en feil.  <br />  Ta kontakt med tjeneste-ansvarlig.";
$lang["_ERROR_UPLOADING_FILE"] = "Feil under filopplasting";
$lang["_ERROR_SENDING_EMAIL"] = "Feil under sending av epostene. <br />Ta kontakt med tjeneste-ansvarlig.";
$lang["_ERROR_INCORRECT_FILE_SIZE"] = "Det oppsto en feil under fil-opplasting.  <br />Fil har en annen størrelse på tjeneren enn på datamaskinen din. <br />Ta kontakt med tjeneeste-ansvarlig.";
$lang["_MAXEMAILS"] = "Tillat antall epost-adresser er ";
$lang["_INVALID_DATE_FORMAT"] = "Feil dato-format";
$lang["_DISK_SPACE_ERROR"] = "Tjenesten har ikke nok diskplass.  Ta kontakt med tjeneste-ansvarlig og/eller prøv med en mindre stor fil";
$lang["_ERROR_ATTRIBUTES"] = "Din påloggings-tjeneste leverer ikke alle obligatoriske attributter knyttet til din nett-identitet. <br />Ta kontakt med tjeneste-ansvarlig.";
$lang["_PERMISSION_DENIED"] = "Det du vil gjøre er ikke tillat.";


$lang["_LOGOUT_COMPLETE"] = "Avlogging utført";

$lang["_ARE_YOU_SURE"] = "Er du sikker?";
$lang["_EMAIL_SENT"] = "Epost sendt";

$lang["_FILE_SIZE"] = "Filstørrelse";
$lang["_FILE_RESENT"] = "Sendt fil på nytt";
$lang["_ME"] = "Meg";

// MYFILES

$lang["_NO_FILES"] = "Ingen aktive filer"; // Myfiles: re-send email: tooltips, dialogue box and on-screen message 
$lang["_RE_SEND_EMAIL"] = "Send epost på nytt";
$lang["_FILE_TO_BE_RESENT"] = "Fil:";
$lang["_MESSAGE_RESENT"] = "Epost med nedlastningslenken sendt på nytt"; 

$lang["_NEW_RECIPIENT"] = "Legg til mottaker(e)";

// Myfiles: delete file: tooltips, dialogue box and on-screen message 
$lang["_DELETE_FILE"] = "Slett fil";
$lang["_FILE_DELETED"] = "Fil slettet";


$lang["_DOWNLOAD_SELECTED"] = "Last ned valgte filer";
$lang["_START_DOWNLOAD"] = "Begynn nedlastning";
$lang["_VOUCHER_SENT"] = "Invitasjon sendt";
$lang["_VOUCHER_DELETED"] = "Invitasjon slettet";
$lang["_VOUCHER_CANCELLED"] = "Denne filen eller invitasjonen har blitt utilgjengeliggjort.";
$lang["_VOUCHER_USED"] = "Fileoverførings-invitasjon kan kun brukes 1 gang, invitasjonen er allerede brukt";
$lang["_STARTED_DOWNLOADING"] = "Filnedlastning burde starte nå";

// Upload page: information on steps user needs to perform
$lang["_STEP1"] = "Fyll ut epost-adress(ene)";
$lang["_STEP2"] = "Set utløpsdato";
$lang["_STEP3"] = "Velg en fil";
$lang["_STEP4"] = "Klikk Send";
$lang["_HTML5Supported"] = "Filopplasting over 2GB støttet!";
$lang["_HTML5NotSupported"] = "Filopplasting over 2GB ikke støttet!";	

// Voucher page
$lang["_SEND_NEW_VOUCHER"] = "En gjeste-invitasjon gir tilgang til tjenesten for å sende <b><i>en</i></b> fil.<br /><br />Skriv inn epost-adressen til gjesten og klikk på <b><i>Send invitasjon</i></b>. En epost vil bli sendt med en lenke som gir gjesten mulighet til å sende 1 fil.<br />";

$lang["_SEND_VOUCHER_TO"] = "Send en invitasjon til";
$lang["_SEND_VOUCHER"] = "Send invitasjon";
$lang["_NO_VOUCHERS"] = "Ingen aktive invitasjoner";

// confirmation
$lang["_CONFIRM_DELETE_FILE"] = "Er du sikker at du vil slette filen?";
$lang["_CONFIRM_DELETE_VOUCHER"] = "Er du sikker at du ønsker å tilbaketrekke invitasjonen?";
$lang["_CONFIRM_RESEND_EMAIL"] = "Er du sikker at du ønsker å sende eposten på nytt?";

// standard date display format
$lang['date_format'] = "Y-m-d"; // Format for displaying date/time, use PHP date() format string syntax 

// datepicker localization
$lang["_DP_closeText"] = 'Lukk'; // Done
$lang["_DP_prevText"] = '&laquo;Forrige'; //Prev
$lang["_DP_nextText"] = 'Neste&raquo;'; // Next
$lang["_DP_currentText"] = 'I dag'; // Today
$lang["_DP_monthNames"] = "['Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember']";
$lang["_DP_monthNamesShort"] = "['Søn','Man','Tir','Ons','Tor','Fre','Lør']";
$lang["_DP_dayNames"] = "['Søndag','Mandag','Tirsdag','Onsdag','Torsdag','Fredag','Lørdag']";
$lang["_DP_dayNamesShort"] = "['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']";
$lang["_DP_dayNamesMin"] = "['Sø','Ma','Ti','On','To','Fr','Lø']";
$lang["_DP_weekHeader"] = 'Uke';
$lang["_DP_dateFormat"] = 'yy-mm-dd'; // uses datepicker specific format http://docs.jquery.com/UI/Datepicker/formatDate
$lang["_DP_firstDay"] = '1';
$lang["_DP_isRTL"] = 'false';
$lang["_DP_showMonthAfterYear"] = 'false';
$lang["_DP_yearSuffix"] = '';

// Login Splash text
$lang["_SITE_SPLASHHEAD"] = "Velkommen til ". htmlspecialchars(Config::get('site_name'));
$lang["_SITE_SPLASHTEXT"] = htmlspecialchars(Config::get('site_name')) ." tilbyr en sikker og enkel måte for å sende store filer til alle dine samarbeidspartnere!  Logg på for å overføre filer eller for å invitere andre til å sende deg en fil.";

// Footer to display
$lang["_SITE_FOOTER"] = ""; 

// site help
$lang["_HELP_TEXT"] = '
<div>
<div style="padding: 5px; text-align: left;">
<h4>Pålogging</h4>
<ul>
	<li>Du logger på med brukerkontoen du bruker hos din institusjon.  Ta kontakt med din lokale IT-helpdesk hvis pålogging feiler eller du ikke finner din institusjon i listen.</li>
</ul>

<h4>Opplastninger av <i>enhver størrelse</i> med HTML5</h4>
<ul>
	<li>Du kan bruke denne metoden hvis dette symbolet vises: <img src="res/images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /></li>
	<li>For å få <img src="res/images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> symbolet, trenger du en modern nettleser som støtter HTML5, siste versjonen av webbens språk.</li>
	<li>Nyere versjoner av Firefox og Chrome, på Windows, Mac OS X og Linux er kjent for å fungere</li>
	<li>Du kan <b><i>gjenoppta</i></b> en opplastning som er avbrutt ved å sende nøyaktig samme fil på nytt. Så lenge filen har akkurat samme navn som sist vil <i>'. htmlspecialchars(Config::get('site_name')) .'</i> gjenoppta opplastningen.  Når opplastningen starter burde du se framdriftsindikatoren hoppe til hvor den var ved siste avbrudd, og fortsette derfra.<br /><br />
	Har du <b><i>endret filen</i></b> mellom første og andre forsøk, bør du først gi filen et nytt navn.  Dermed forsikres det at det startes en ny, fersk opplastning og at alle dine endringer vil bli overført uten problemer.</li>
</ul>

<h4>Nedlastninger av enhver størrelse</h4>
<ul>
	<li>Alle nyere populære nettlesere vil fungere fint når det gjelder nedlastninger av vilkårlig store filer.  Ikke bekymre deg om Adobe Flash eller HTML5 - disse er bare relevant for opplastninger; ikke noe spesielt kreves for nedlastninger.</li>
</ul>

<h4>Opplastninger opp til 2 Gigabytes (2GB) med Adobe Flash</h4>
<ul>
	<li>Hvis du kan se video på YouTube burde denne metoden fungere for deg.</li>
	<li>Du trenger en nyere nettleser med versjon 10 (og oppover) av <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash</a>.</li>
	<li>Ved bruk av Adobe Flash kan du laste opp filer opp til 2 Gigabytes (2GB).  Du vil bli varslet skulle du prøve å laste opp en fil som er for stor.</li>
	<li>Gjennoptagelse av opplastninger er ikke støttet med denne metoden.</li>

</ul>

<h4>Konfigurerte begrensninger til tjenesten</h4>
<ul>
<li><b>Maks. antall mottakere per sending: </b>'. Config::get('max_email_recipients').' To eller flere epost-adresser skilles med komma eller semikolon (f.eks. ole@norge.no, per@uninettt.no).</li>
      <li><b>Maks. antall filer per sending: </b> 1 - for å sende flere filer samtidig kan du pakke dem i f.eks. en zip-fil først.</li>
      <li>Største mulige filstørrelse per sending <b>uten</b> HTML 5: '. Utilities::formatBytes(Config::get('max_legacy_upload_size')) .'</li>
      <li>Største mulige filstørrelse per sending <b>med</b> HTML 5: ' .Utilities::formatBytes(Config::get('max_html5_upload_size')).'</li>
      <li>Maksimum antall dager før utløp av sending: '. Config::get('default_daysvalid').'</li>
</ul>

<h4>Tekniske detaljer</h4>
<ul>
<li>
<i>'. htmlspecialchars(Config::get('site_name')) .'</i> bruker <a href="http://www.filesender.org/" target="_blank">FileSender programvaren</a>.  FileSender antyder om HTML5 opplastning er støttet i en bestemt nettleser, eller ikke.  Dette er primært avhengig av avansert nettleserfunksjonalitet, primært støtte for HTML5 File APIen.  På <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> nettsiden kan implementasjonsframgang av HTML5-støtten i alle populære nettlesere følges.  Spesielt støtte av <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> og <a href="http://caniuse.com/bloburls" target="_blank">Blob URL</a> trenger å være lyse-grønt (=støttet) for at opplastninger større enn 2GB er støttet.  Vær obs på at selv om det står at Opera 12 støtter HTML5 FileAPI, er det i skrivende stund fortsatt mangler i Operas implementasjon som fører til at HTML5 opplastning ikke fungerer med Opera.</li>
</ul>

    <p>For mer informasjon henvises det til <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
</div>
  </div>';

// site about
$lang["_ABOUT_TEXT"] = ' <div style="padding: 5px; text-align: left;">'. htmlspecialchars(Config::get('site_name')) .' er en FileSender (<a rel="nofollow" href="http://www.filesender.org/" target="_blank">www.filesender.org</a>) installasjon.  FileSender programvaren er optimalisert for bruk i høgere utdanning og forskning.</div>';

// site AUP terms
$lang["_AUPTERMS"] = "Oppfør deg pent ellers så kommer trollan...";

?>
