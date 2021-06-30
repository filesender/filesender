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
    <li><i class="fa-li fa fa-caret-right"></i>Prijavite se putem jednog od navedenih pružatelja identiteta koristeći svoj standardni institucionalni račun. Ako svoju instituciju ne vidite na popisu, ili vam prijava ne uspije, obratite se lokalnoj informatičkoj podršci </li>
</ul>

<h3>Značajke vašeg preglednika</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload omogućen" /> Možete prenositi datoteke veličine do {size:cfg:max_transfer_size} po prijenosu.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload onemogućen" /> Možete prenijeti datoteke od najviše {size:cfg:max_legacy_file_size} svaka i do {size:cfg:max_transfer_size} po prijenosu.</li>
</ul>

<h3>Prijenosi <i>bilo koje veličine</i> with HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Ovu metodu možete koristiti ako je iznad (u "Značajke vašeg preglednika") prikazan znak <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> </li>
    <li><i class="fa-li fa fa-caret-right"></i>Da biste omogućili ovu funkciju, jednostavno koristite ažurni preglednik koji podržava HTML5.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Poznato je da najnovije verzije Firefoxa i Chromea u sustavu Windows, Mac OS X i Linux podržavaju HTML5.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Možete <strong> nastaviti </strong> prekinuti ili otkazani prijenos datoteka. Da biste nastavili prijenos, jednostavno <strong> ponovno pošaljite iste datoteke </strong>!
       Provjerite imaju li datoteke <strong> ista imena i veličine </strong> kao i prije.
       Kad započne prijenos, trebali biste primijetiti skok trake napretka do mjesta zaustavljanja/prekida i nastavak odatle.
    </li>
</ul>

<h3>Prijenos datoteka do {size:cfg:max_legacy_file_size} po datoteci bez korištenja HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender će vas upozoriti ako pokušate prenijeti datoteku koja je prevelika za ovu metodu.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Nastavak prijenosa nije podržan ovom metodom.</li>
</ul>

<h3>Preuzimanja bilo koje veličine </h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Svaki moderan preglednik će biti u redu, ništa nije potrebno.</li>
</ul>


<h3>Konfigurirana ograničenja usluge</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalni broj primatelja : </strong>{cfg:max_transfer_recipients} adresa e-pošte odvojene zarezom ili točka-zarezom
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalni broj datoteka po prijenosu : </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalna veličina po prijenosu : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimalna veličina datoteke za preglednike koji ne podržavaju HTML5 : </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Dani isteka prijenosa : </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Dani isteka prijenosa za gosta : </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Tehnički detalji</h3>
<ul class="fa-ul">
    <li><p style="text-align: justify;"><i class="fa-li fa fa-caret-right"></i><strong>{cfg:site_name}</strong> koristi <a href="http://filesender.org/" target="_blank">FileSender software</a>.
        FileSender pokazuje da li preglednik podržava HTML5 način prijenosa datoteka ili ne.
        To uglavnom ovisi o dostupnosti napredne funkcionalnosti preglednika, posebno "HTML5 FileAPI".
        Molim koristite <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> stranicu za praćenje implementacije "HTML5 FileAPI" za sve preglednike.
	Konkretno, podrška za <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> i <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> moraju biti svijetlo zelene boje (=supported) za preglednik koji podržava prijenose veće od {size:cfg:max_legacy_file_size}.
        Imajte na umu da iako je navedeno da Opera 12 podržava HTML5 FileAPI, ona trenutno ne podržava sve što je potrebno za HTML5 prijenos datoteka u FileSender aplikaciji.</p>
    </li>
</ul>

<p>Za više informacija posjetite <a href="http://filesender.org/" target="_blank">filesender.org</a></p>