<div>
<div style="padding: 5px; text-align: left;">
<h4>Aanmelden</h4> 
<ul>
    <li>U kunt inloggen door middel van uw bestaande instellings-logingegevens; kies daartoe uw instellingsnaam uit de lijst van Identity Providers. Als uw instelling niet voorkomt in de lijst of u heeft problemen met het inloggen, neemt u dan alstublieft contact op met uw locale IT-helpdesk.</li>
</ul>

<h4>Uploads van <i>willekeurig welke grootte</i> met de HTML5-methode</h4>
<ul>
        <li>Als het <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> symbooltje vertoond wordt dan kunt u de HTML5-methode gebruiken.</li>
	<li>Hiervoor is een recente browserversie nodig die HTML5 ondersteunt, de nieuwste editie van de "taal van het web".</li>
	<li>Momenteel geldt dit in ieder geval voor Firefox 4 (en hoger) en Chrome op Windows, Mac OS X en Linux.</li>
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
	<li><i>{cfg:site_name}</i> waarschuwt u als u een bestand wilt uploaden dat te groot is voor deze methode.</li>
	<li>Het hervatten van afgebroken uploads is met deze methode niet mogelijk.</li>
</ul>



<h4>Instellingen van deze dienst</h4>
<ul>
    <li><strong>
      Maximum aantal e-mail-ontvangers:</strong> Tot {cfg:max_email_recipients} e-mailadressen gescheiden door een komma of puntkomma</li>
    <li><strong>Maximum aantal bestanden per upload:</strong> &eacute;&eacute;n - om meerdere bestanden ineens te versturen, kunt u ze eerst samenpakken in een archiefbestand zoals zip</li>
    <li><strong>Maximum bestandsgrootte per upload, alleen gebruikmakend van Adobe Flash:</strong> {size:cfg:max_legacy_file_size}</li>
    <li><strong>Maximum bestandsgrootte per upload, via HTML5:</strong> {size:cfg:max_html5_upload_size}</li>
    <li><strong>Maximum geldigheidsduur van bestanden en uitnodigingen:</strong> {cfg:default_transfer_days_valid} dagen</li>
</ul>

<h4>Technische details</h4>
<ul>
	<li><i>{cfg:site_name}</i> maakt gebruik van de <a href="http://www.filesender.org/" target="_blank">FileSender software</a>. FileSender geeft aan of de HTML5 upload-methode ondersteund wordt voor de op dat moment gebruikte browser. Deze ondersteuning is voornamelijk afhankelijk van de beschiklbaarheid van geavanceerde browserfunctionaliteit, met name de HTML5 FileAPI. De website <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> geeft bruikbare informatie om de voortgang van de implementatie van HTML5 FileAPI in 
de belangrijkste browsers te volgen. Met name ondersteuning voor de <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> en voor <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> moet groen licht (=ondersteund) krijgen, wil een browser uploads groter dan 2GB kunnen doen.  Belangrijk: alhoewel Opera 12 wordt weergegeven als zou het de HTML5 FileAPI volledig ondersteunen is gebleken dat Opera 12 desondanks niet voldoende elementen van de FileAPI ondersteunt om al gebruik te kunnen maken van de HTML5 upload-methode.</li>
</ul>
<p>Voor meer informatie, bezoek <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a>.</p>
</div>
</div>
