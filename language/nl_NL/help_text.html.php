<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Inloggen</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>U kunt inloggen door middel van uw bestaande instellings-logingegevens. Kies daartoe uw instellingsnaam uit de lijst van Identity Providers. Als uw instelling niet voorkomt in de lijst of u heeft problemen met het inloggen, neemt u dan alstublieft contact op met uw instellings-helpdesk</li>
</ul>

<h3>De mogelijkheden van uw browser</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload toegestaan" /> U kunt bestanden uploaden tot maximaal  {size:cfg:max_transfer_size} per overdracht.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload niet toegestaan" /> U kunt bestanden uploaden met de maximale bestandsgrootte van {size:cfg:max_legacy_file_size} per stuk en tot maximaal  {size:cfg:max_transfer_size} totaal per overdracht.</li>
</ul>

<h3>Uploads van <i>willekeurig welke grootte</i> met HTML5</h3>
<ul class="fa-ul">
        <li><i class="fa-li fa fa-caret-right"></i>Als het <img src="images/html5_installed.png" alt="HTML5 upload toegestaan" /> symbooltje vertoond wordt dan kunt u de HTML5-methode gebruiken.</li>
	<li><i class="fa-li fa fa-caret-right"></i>Hiervoor is een recente browserversie nodig die HTML5 ondersteunt, de "taal van het web".</li>
        <li><i class="fa-li fa fa-caret-right"></i>Alle moderne versies van Firefox en Chrome voor Windows, Mac OS en Linux zullen werken.</li>
	<li><i class="fa-li fa fa-caret-right"></i>
     Een ongewenst onderbroken upload kan <strong>hervat</strong> worden. Om een upload te hervatten verstuurt u eenvoudigweg <strong>exact dezelfde bestanden</strong> opnieuw!
     Verzeker u ervan dat de bestanden <strong>dezelfde namen en groottes</strong> hebben als voorheen.
     De voortgangs-indicator moet dan verspringen naar het percentage waar de upload eerder was gestopt, en dan de upload voortzetten
   </li>
</ul>

<h3>Uploads tot {size:cfg:max_legacy_file_size} per bestand zonder HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} zal een waarschuwing tonen wanneer je een te groot bestand probeert te versturen. </li>
    <li><i class="fa-li fa fa-caret-right"></i>Het hervatten van een onderbroken upload wordt niet ondersteund als de browser geen HTML5 ondersteunt.</li>
</ul>

<h3>Downloads van willekeurig welke grootte</h3>
<ul class="fa-ul">
        <li><i class="fa-li fa fa-caret-right"></i>Hiervoor heeft u alleen een moderne browser nodig, voor downloads is niets speciaals vereist</li>
</ul>

<h3>Instellingen van deze dienst</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum aantal e-mail-ontvangers:</strong> Tot {cfg:max_email_recipients} e-mailadressen gescheiden door een komma of puntkomma</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum aantal bestanden per upload:</strong> {cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum bestandsgrootte per upload: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximum bestandsgrootte per bestand voor browsers die geen HTML5 ondersteunen: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Geldigheidsduur van bestanden: </strong> {cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid}) dagen</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Geldigheidsduur van uitnodigingen: </strong> {cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid}) dagen</li>
</ul>

<h3>Technische details</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
	<strong>{cfg:site_name}</strong> maakt gebruik van de <a href="http://www.filesender.org/" target="_blank">FileSender software</a>.   
     FileSender geeft aan of de HTML5 upload-methode ondersteund wordt voor de op dat moment gebruikte browser.
     Deze ondersteuning is voornamelijk afhankelijk van de beschikbaarheid van geavanceerde browserfunctionaliteit, met name de HTML5 FileAPI. 
     De website <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> geeft bruikbare informatie om de voortgang van de implementatie van HTML5 FileAPI in de belangrijkste browsers te volgen. 
       Met name ondersteuning voor de <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> en voor <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> moet lichtgroen (=ondersteund) zijn, wil een browser uploads groter dan {size:cfg:max_legacy_file_size} kunnen doen.  
     Let op, alhoewel Opera 12 wordt weergegeven als zou het de HTML5 FileAPI volledig ondersteunen is gebleken dat Opera 12 desondanks niet voldoende elementen van de FileAPI ondersteunt om gebruik te kunnen maken van de HTML5 upload-methode.
  </li>
</ul>

<p>Voor meer informatie, bezoek <a href="http://filesender.org/" target="_blank">www.filesender.org</a></p>
