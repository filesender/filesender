<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Prijava</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Prijavite se putem jednog od navedenih provajdera koristeći svoj standardni institucionalni nalog. Ako svoju instituciju ne vidite na listi, ili vam prijava ne uspe, obratite se lokalnoj informatičkoj podršci </li>
</ul>

<h3>Karakteristike vašeg pregledača</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload omogućen" /> Možete izvršiti otpremanje fajlova veličine do {size:cfg:max_transfer_size} po transferu.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload onemogućen" /> Možete izvršiti otpremanje fajlova od najviše {size:cfg:max_legacy_file_size} svaki i do {size:cfg:max_transfer_size} po transferu.</li>
</ul>

<h3>Otpremanja <i>bilo koje veličine</i> sa HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Ovu metodu možete koristiti ako je iznad (u "Karakteristike vašeg pregledača") prikazan znak <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> </li>
    <li><i class="fa-li fa fa-caret-right"></i>Da biste omogućili ovu funkciju, jednostavno koristite ažurni pregledač koji podržava HTML5, najnoviju verziju "language of the web".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Poznato je da najnovije verzije Firefox-a i Chrome-a u sistemu Windows, Mac OS X i Linux podržavaju HTML5.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Možete <strong> nastaviti </strong> prekinuto ili otkazano otpremanje fajlova. Da biste nastavili otpremanje, jednostavno <strong> ponovno pošaljite iste fajlove </strong>!
       Proverite imaju li fajlovi <strong> ista imena i veličine </strong> kao i pre.
       Kad započne otpremanje, trebali biste primetiti skok trake napretka do mesta zaustavljanja/prekida i nastavak odatle.
    </li>
</ul>

<h3>Transfer fajlova do {size:cfg:max_legacy_file_size} po fajlu bez korišćenja HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender će vas upozoriti ako pokušate izvršiti otpremanje fajla koji je prevelik za ovu metodu.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Nastavak otpremanja nije podržan ovom metodom.</li>
</ul>

<h3>Preuzimanja bilo koje veličine </h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Svaki moderan pregledač će biti u redu, ništa posebno nije potrebno za preuzimanja.</li>
</ul>


<h3>Konfigurisana ograničenja usluge</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalan broj primalaca : </strong>{cfg:max_transfer_recipients} adrese e-pošte odvojene zarezom ili tačka-zarezom
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalan broj fajlova po transferu : </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalna veličina po transferu : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalna veličina fajla za pregledače koji ne podržavaju HTML5 : </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Dani isteka transfera : </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Dani isteka transfera za gosta : </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Tehnički detalji</h3>
<ul class="fa-ul">
    <li><p style="text-align: justify;"><i class="fa-li fa fa-caret-right"></i><strong>{cfg:site_name}</strong> koristi <a href="http://filesender.org/" target="_blank">FileSender software</a>.
        FileSender pokazuje da li pregledač podržava HTML5 način otpremanja fajlova ili ne.
        To uglavnom zavisi o dostupnosti naprednih funkcija pregledača, posebno "HTML5 FileAPI".
        Molim koristite <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> stranicu za praćenje implementacije "HTML5 FileAPI" za sve pregledače.
	Konkretno, podrška za <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> i <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> mora biti svetlo zelene boje (=supported) za pregledač koji podržava transfere veće od {size:cfg:max_legacy_file_size}.
        Imajte na umu da iako je navedeno da Opera 12 podržava HTML5 FileAPI, ona trenutno ne podržava sve što je potrebno za HTML5 otpremanje fajlova u FileSender aplikaciji.</p>
    </li>
</ul>

<p>Za više informacija posetite <a href="http://filesender.org/" target="_blank">filesender.org</a></p>