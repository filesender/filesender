<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
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
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
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
 * NL_nl Language File
 * ---------------------------------
 * 
 */
// Hoofdmenu items
$lang["_ADMIN"] = "Administratie";
$lang["_NEW_UPLOAD"] = "Nieuwe Upload";
$lang["_VOUCHERS"] = "Uitnodiging";
$lang["_LOGON"] = "Aanmelden";
$lang["_LOG_OFF"] = "Uitloggen";
$lang["_MY_FILES"] = "Mijn bestanden";

// Pagina titels
$lang["_HOME"] = "Home";
$lang["_ABOUT"] = "Over";
$lang["_HELP"] = "Help";
$lang["_VOUCHER_CANCELLED"] = "Uitnodiging ingetrokken";
$lang["_DELETE_VOUCHER"] = "Trek uitnodiging in";
$lang["_UPLOAD_COMPLETE"] = "Upload voltooid";
$lang["_UPLOAD_PROGRESS"] = "Voortgang Upload";
$lang["_DOWNLOAD"] = "Download";
$lang["_CANCEL_UPLOAD"] = "Annuleer Upload";

// Admin menu

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

$lang["_TERMS_OF_AGREEMENT"] = "Algemene Overeenkomst";
$lang["_SHOW_TERMS"] = "Toon Voorwaarden";
$lang["_SHOWHIDE"] = "Toon/Verberg";
$lang["_SELECT_FILE"] = "Selecteer een bestand om te uploaden";
$lang["_UPLOADING_WAIT"] = "Uploaden bestand - even geduld aub ...";
$lang["_EMAIL_SEPARATOR_MSG"] = "Meerdere e-mailadressen gescheiden door, of ;";

$lang["_AUPTERMS"] = "Algemene voorwaarden";
$lang["_ACCEPTTOC"] = "Ik ga akkoord met de bepalingen en voorwaarden van deze dienst.";	
$lang["_AGREETOC"] = "U moet akkoord gaan met de voorwaarden.";

// Vouchers
$lang["_SEND_NEW_VOUCHER"] = "Met een Uitnodiging kunt u iemand een bestand laten sturen.<br>
Om een Uitnodiging te maken voer een e-mailadres in en klik op Stuur Uitnodiging.<BR>
Er wordt dan een e-mail verstuurd naar de ontvanger met daarin een link om de uitnodiging te gebruiken.";
$lang["_SEND_VOUCHER_TO"] = "Stuur uitnodiging naar";

// Upload pagina: button menu
$lang["_UPLOAD"] = "Upload";
$lang["_BROWSE"] = "Blader";
$lang["_CANCEL"] = "Annuleer";
$lang["_OPEN"] = "Open";
$lang["_CLOSE"] = "Sluit";
$lang["_OK"] = "OK";
$lang["_SEND"] = "Stuur";
$lang["_DELETE"] = "Verwijder";

// steps
$lang["_STEP1"] = "Voer één of meer e-mailadresen in";
$lang["_STEP2"] = "Stel een vervaldatum in";
$lang["_STEP3"] = "Selecteer een bestand";
$lang["_STEP4"] = "Selecteer Upload";

// Upload page: error messages, displayed on-input
$lang["_INVALID_MISSING_EMAIL"] = "Ongeldig of ontbrekend e-mailadres";
$lang["_INVALID_EXPIRY_DATE"] = "Ongeldige vervaldatum";
$lang["_INVALID_FILE"] = "Ongeldig bestand";
$lang["_INVALID_FILESIZE_ZERO"] = "Ongeldige bestandsgrootte van 0 bytes. Kies een ander bestand.";
$lang["_INVALID_FILE_EXT"] = "Ongeldig bestandstype. Kies een ander bestand..";
$lang["_INVALID_TOO_LARGE_1"] = "Bestand kan niet groter zijn dan";
$lang["_SELECT_ANOTHER_FILE"] = "Kies een ander bestand.";
$lang["_INVALID_FILE_NAME"] = "Ongeldige bestandsnaam. Hernoem het bestand en probeer het opnieuw.";
$lang["_INVALID_2GB_USEHTML5"] = "Bestand is groter dan 2GB. Gebruik een geschikte HTML5 browser voor grotere bestanden.";
$lang["_FILE_TO_BE_RESENT"] = "Bestand om opnieuw te versturen";
$lang["_ERROR_UPLOADING_FILE"] = "Fout bij het uploaden van het bestand";
$lang["_LOGOUT_COMPLETE"] = "U bent uitgelogt";

$lang["_ARE_YOU_SURE"] = "Weet U dit zeker?";
$lang["_DELETE_FILE"] = "Verwijder bestand";
$lang["_EMAIL_SENT"] = "E-mail verstuurd";
$lang["_FILE_SIZE"] = "Bestandsgrootte";
$lang["_FILE_RESENT"] = "Bestand opnieuw verstuurd";
$lang["_ME"] = "Ik";
$lang["_START_DOWNLOAD"] = "Start Download";
$lang["_VOUCHER_SENT"] = "Uitnodiging verstuurd";
$lang["_VOUCHER_DELETED"] = "Uitnodiging ingetrokken";
$lang["_VOUCHER_CANCELLED"] = "Deze uitnodiging is ingetrokken.";
$lang["_STARTED_DOWNLOADING"] = "De download van het bestand zal beginnen.";

?>
