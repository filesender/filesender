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
 * *    Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
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

/* -----------------------------------------------
 * hu_HU Language File contibuted by Csillag Tamas
 * -----------------------------------------------
 * 
 */
// main menu items
$lang["_ADMIN"] = "Adminisztráció";
$lang["_NEW_UPLOAD"] = "Fájl küldés";
$lang["_VOUCHERS"] = "Vendég meghívása";
$lang["_LOGON"] = "Bejelentkezés";
$lang["_LOG_OFF"] = "Kijelentkezés";
$lang["_MY_FILES"] = "Feltöltött fájlok";

// page titles
$lang["_HOME"] = "Kezdőlap";
$lang["_ABOUT"] = "Névjegy";
$lang["_HELP"] = "Súgó";
$lang["_DELETE_VOUCHER"] = "Meghívó törlése";
$lang["_UPLOAD_COMPLETE"] = "A fájlt feltöltöttük, az email is elment.";
$lang["_UPLOAD_PROGRESS"] = "A feltöltés folyamatban";
$lang["_DOWNLOAD"] = "Letöltés";
$lang["_CANCEL_UPLOAD"] = "Feltöltés megszakítása";

// admin
//$lang["_PAGE"] = "Page";
//$lang["_UP"] = "Up";
//$lang["_DOWN"] = "Down";
//$lang["_FILES"] = "Files";
//$lang["_DRIVE"] = "Drive";
//$lang["_TOTAL"] = "Total";
//$lang["_USED"] = "Used";
//$lang["_AVAILABLE"] = "Available";
//$lang["_TEMP"] = "Temp"; // as in Temporary files

// Greetings
$lang["_WELCOME"] = "Üdv!"; 
$lang["_WELCOMEGUEST"] = "Üdv. vendég!"; 

// admin tab names
//$lang["_GENERAL"] = "General";
//$lang["_UPLOADS"] = "Uploads";
//$lang["_DOWNLOADS"] = "Downloads";
//$lang["_ERRORS"] = "Errors";
//$lang["_FILES_AVAILABLE"] = "Files Available";
//$lang["_ACTIVE_VOUCHERS"] = "Active Vouchers";
//$lang["_COMPLETE_LOG"] = "Complete Log";

// Form Fields
$lang["_TO"] = "Címzett";
$lang["_FROM"] = "Feladó";
$lang["_SIZE"] = "Méret";
$lang["_CREATED"] = "Létrehozva";
$lang["_FILE_NAME"] = "Fájl neve";
$lang["_SUBJECT"] = "Tárgy";
$lang["_EXPIRY"] = "Lejárat ideje";
$lang["_MESSAGE"] = "Üzenet";
$lang["_TYPE"] = "Típus";

$lang["_TERMS_OF_AGREEMENT"] = "Terms of Agreement";
$lang["_SHOW_TERMS"] = "Lássuk a feltételeket!";
$lang["_SHOWHIDE"] = "Megjelenítés/Elrejtés";
$lang["_UPLOADING_WAIT"] = "Fájl feltöltése - kis türelmet...";

// button labels
$lang["_UPLOAD"] = "Fájl küldés";
$lang["_BROWSE"] = "Tallózás";
$lang["_CANCEL"] = "Mégsem";
$lang["_OPEN"] = "Megnyit";
$lang["_CLOSE"] = "Bezár";
$lang["_OK"] = "Ok";
$lang["_SEND"] = "Küldés";
$lang["_DELETE"] = "Törlés";
$lang["_YES"] = "Igen";
$lang["_NO"] = "Nem";

$lang["_INVALID_MISSING_EMAIL"] = "Érvénytelen vagy hiányzó email cím";   
$lang["_INVALID_EXPIRY_DATE"] = "Érvénytelen lejárati dátum";  
$lang["_INVALID_FILE"] = "Érvénytelen fájl";        
$lang["_INVALID_FILEVOUCHERID"] = "Érvénytelen fájl vagy meghívási azonosító"; 
$lang["_INVALID_FILESIZE_ZERO"] = "A fájl méretnek 0-nál nagyobbnak kell lennie. Váasszon másik fájlt.";
$lang["_INVALID_FILE_EXT"] = "Érvénytelen fájl kiterjesztés.";
$lang["_INVALID_TOO_LARGE_1"] = "A fájl méret nem lehet nagyobb mint: ";
$lang["_AUTH_ERROR"] = "Nincs bejelentkezve. <br />A munkamenet lejárt vagy a szerver nem válaszol. <br /><br />Jelentkezzen be újra!";    
$lang["_SELECT_ANOTHER_FILE"] = "Válasszon másik fájlt.";
$lang["_INVALID_VOUCHER"] = "Ez a meghívó érvénytelen. <br />Vegye fel a kapcsolatot a küldővel.";
$lang["_SELECT_FILE"] = "Fájl kiválasztása";
$lang["_INVALID_FILE_NAME"] = "A feltöltött fájl neve érvénytelen. Nevezze át a fájlt és próbálja meg újra!";
$lang["_INVALID_SIZE_USEHTML5"] = "Válasszon másik fájlt vagy használjon HTML5 kompatibilis böngészőt a nagyobb fájlok feltöltéséhez.";
$lang["_ACCEPTTOC"] = "Felhasználási feltételek elfogadása.";     
$lang["_AGREETOC"] = "A felhasználási feltételeket el KELL fogadni!";
$lang["_FILE_TO_BE_RESENT"] = "A fájl újraküldére került";
$lang["_ERROR_UPLOADING_FILE"] = "Fájl feltöltési hiba";
$lang["_ERROR_INCORRECT_FILE_SIZE"] = "Probléma a fájl feltöltése közben. <br />A szerveren tárolt fájl mérete nem egyezik meg az eredeti mérettel. <br /><br />Forduljon az adminisztrátorhoz.";
$lang["_MAXEMAILS"] = "A maximális email címek száma: ";
$lang["_INVALID_DATE_FORMAT"] = "Az dátum formátuma érvénytelen.";
$lang["_DISK_SPACE_ERROR"] = "Nincs elegendő hely a szolgáltatáshoz. Forduljon az adminisztrátorhoz vagy töltsön fel kisebb fájlt.";

// Logout page
$lang["_LOGOUT_COMPLETE"] = "A kijelentkezés sikerült";

// vouchers
$lang["_SEND_NEW_VOUCHER"] = "A meghívó lehetővé teszi, hogy valaki fájlt küldjön Önnek.<br />
Meghívó készítéséhez írja be a meghívni kívánt személy email címét, majd válassza a meghívó küldését.<br />
A meghívó emailben elküldésre kerül egy link amit a meghívott használhat.";

// User interaction
$lang["_EMAIL_SEPARATOR_MSG"] = "Többszörös email cím hozzáadása ,-vel vagy ;-vel elválasztva lehetséges";
$lang["_NO_FILES"] = "Jelenleg nincs elérhető fájl";
$lang["_ARE_YOU_SURE"] = "Bizos benne?";
$lang["_DELETE_FILE"] = "Fájl törlése";
$lang["_EMAIL_SENT"] = "Üzenet elküldve";
$lang["_EXPIRY_DATE"] = "Lejárati dátum";
$lang["_FILE_SIZE"] = "Fájl méret";
$lang["_FILE_RESENT"] = "Fájl újraküldve"; 
$lang["_MESSAGE_RESENT"] = "Üzenet újraküldve";
$lang["_ME"] = "Én";
$lang["_SEND_VOUCHER"] = "Meghívó küldése";
$lang["_RE_SEND_EMAIL"] = "Üzenet újraküldése";
$lang["_NEW_RECIPIENT"] = "Címzett hozzáadása";
$lang["_SEND_VOUCHER_TO"] = "Meghívó küldése";
$lang["_START_DOWNLOAD"] = "Letöltés";
$lang["_VOUCHER_SENT"] = "Meghívó elküldve";
$lang["_VOUCHER_DELETED"] = "Meghívó törölve";
$lang["_VOUCHER_CANCELLED"] = "Meghívót visszavonták.";
$lang["_VOUCHER_USED"] = "Ezt a meghívót már felhasználták.";
$lang["_STARTED_DOWNLOADING"] = "A letöltésnek el kellett indulnia.";
$lang["_FILE_DELETED"] = "A fájl törölve.";

// steps
$lang["_STEP1"] = "Írja be a címzett(ek) emailcímét";
$lang["_STEP2"] = "Állítsa be a lejárati dátumot";
$lang["_STEP3"] = "Válassza ki a küldendő fájlt";
$lang["_STEP4"] = "Kattintson a küldésre";
$lang["_HTML5Supported"] = "2GB-nál nagyobb fájl feltöltése támogatott!";
$lang["_HTML5NotSupported"] = "2GB-nál nagyobb fájl feltöltése<br /><b>nem támogatott</b>!";                   

$lang["_OPTIONAL"] = "választható";

// confirmation
$lang["_CONFIRM_DELETE_FILE"] = "Biztosan törölni akarja a fájlt?";
$lang["_CONFIRM_DELETE_VOUCHER"] = "Biztosan törölni akarja a meghívót?";
$lang["_CONFIRM_RESEND_EMAIL"] = "Are you sure you want to resend this email?";

// standard date display format
$lang['datedisplayformat'] = "Y-m-d"; // Format for displaying date/time, use PHP date() format string syntax 

// datepicker localization
$lang["_DP_closeText"] = 'Kész'; // Done
$lang["_DP_prevText"] = 'Előző'; //Prev
$lang["_DP_nextText"] = 'Következő'; // Next
$lang["_DP_currentText"] = 'Ma'; // Today
$lang["_DP_monthNames"] = "['Január','Február','Március','Április','Május','Június','Július','Augusztus','Szeptember','Október','November','December']";
$lang["_DP_monthNamesShort"] = "['Jan', 'Feb', 'Már', 'Ápr', 'Máy', 'Jún','Júl', 'Aug', 'Szep', 'Okt', 'Nov', 'Dec']";
$lang["_DP_dayNames"] = "['Vasárnap', 'Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat']";
$lang["_DP_dayNamesShort"] = "['Vas', 'Hét', 'Kedd', 'Sze', 'Csü', 'Pén', 'Szo']";
$lang["_DP_dayNamesMin"] = "['V','H','K','Sz','Cs','P','Sz']";
$lang["_DP_weekHeader"] = 'Wk';
$lang["_DP_dateFormat"] = 'yy-mm-dd';
$lang["_DP_firstDay"] = '2';
$lang["_DP_isRTL"] = 'false';
$lang["_DP_showMonthAfterYear"] = 'false';
$lang["_DP_yearSuffix"] = '';

// Login Splash text
$lang["_SITE_SPLASHTEXT"] = "A FileSenderrel biztonságos módon oszthatunk meg nagy fájlokat bárkivel. Lépjen be és küldjön fájlt vagy meghívót, hogy más küldhessen önnek.";

// site about
// site help
// site AUP terms

?>
