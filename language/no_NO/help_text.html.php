<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<div>
<div style="padding: 5px; text-align: left;">
<h4>Pålogging</h4>
<ul>
	<li>Du logger på med brukerkontoen du bruker hos din institusjon.  Ta kontakt med din lokale IT-helpdesk hvis pålogging feiler eller du ikke finner din institusjon i listen.</li>
</ul>

<h4>Opplastninger av <i>enhver størrelse</i> med HTML5</h4>
<ul>
	<li>Du kan bruke denne metoden hvis dette symbolet vises: <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /></li>
	<li>For å få <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> symbolet, trenger du en modern nettleser som støtter HTML5, siste versjonen av webbens språk.</li>
	<li>Nyere versjoner av Firefox og Chrome, på Windows, Mac OS X og Linux er kjent for å fungere</li>
	<li>Du kan <b><i>gjenoppta</i></b> en opplastning som er avbrutt ved å sende nøyaktig samme fil på nytt. Så lenge filen har akkurat samme navn som sist vil <i>{cfg:site_name}</i> gjenoppta opplastningen.  Når opplastningen starter burde du se framdriftsindikatoren hoppe til hvor den var ved siste avbrudd, og fortsette derfra.<br /><br />
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
<li><b>Maks. antall mottakere per sending: </b>{cfg:max_email_recipients} To eller flere epost-adresser skilles med komma eller semikolon (f.eks. ole@norge.no, per@uninettt.no).</li>
      <li><b>Maks. antall filer per sending: </b> 1 - for å sende flere filer samtidig kan du pakke dem i f.eks. en zip-fil først.</li>
      <li>Største mulige filstørrelse per sending <b>uten</b> HTML 5: {size:cfg:max_legacy_file_size}</li>
      <li>Største mulige filstørrelse per sending <b>med</b> HTML 5: {size:cfg:max_html5_upload_size}</li>
      <li>Maksimum antall dager før utløp av sending: {cfg:default_days_valid}</li>
</ul>

<h4>Tekniske detaljer</h4>
<ul>
<li>
<i>{cfg:site_name}</i> bruker <a href="http://www.filesender.org/" target="_blank">FileSender programvaren</a>.  FileSender antyder om HTML5 opplastning er støttet i en bestemt nettleser, eller ikke.  Dette er primært avhengig av avansert nettleserfunksjonalitet, primært støtte for HTML5 File APIen.  På <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> nettsiden kan implementasjonsframgang av HTML5-støtten i alle populære nettlesere følges.  Spesielt støtte av <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> og <a href="http://caniuse.com/bloburls" target="_blank">Blob URL</a> trenger å være lyse-grønt (=støttet) for at opplastninger større enn 2GB er støttet.  Vær obs på at selv om det står at Opera 12 støtter HTML5 FileAPI, er det i skrivende stund fortsatt mangler i Operas implementasjon som fører til at HTML5 opplastning ikke fungerer med Opera.</li>
</ul>

    <p>For mer informasjon henvises det til <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
</div>
  </div>
