<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Autentificare</h3>
<ul class="fa-ul">
<li><i class="fa-li fa fa-caret-right"></i>Vă autentificați prin unul dintre Furnizorii de Identitate enumerați, utilizând contul dvs. instituțional standard. Dacă nu găsiți instituția dvs. în listă sau dacă autentificarea eșuează, vă rugăm să contactați serviciul local de asistență IT.</li>
</ul>

<h3>Funcțiile browserului dvs.</h3>
<ul class="fa-ul">
<li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> Puteți încărca fișiere de orice dimensiune, până la {size:cfg:max_transfer_size} per transfer.</li>
<li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> Puteți încărca fișiere de cel mult {size:cfg:max_legacy_file_size} fiecare și până la {size:cfg:max_transfer_size} per transfer.</li>
</ul>

<h3>Încărcarea fișierelor de <i>orice dimensiune</i> cu HTML5</h3>
<ul class="fa-ul">
<li><i class="fa-li fa fa-caret-right"></i>Veți putea utiliza această metodă dacă semnul <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> este afișat mai sus.</li>
<li><i class="fa-li fa fa-caret-right"></i>Pentru a activa această funcționalitate, utilizați pur și simplu un browser actualizat care acceptă HTML5, cea mai recentă versiune a „limbajului web”.</li>
<li><i class="fa-li fa fa-caret-right"></i>Se știe că versiunile actualizate ale Firefox și Chrome pe Windows, Mac OS X și Linux funcționează.</li>
<li><i class="fa-li fa fa-caret-right"></i> Puteți <strong>relua</strong> o încărcare întreruptă sau anulată. Pentru a relua o încărcare, pur și simplu <strong>trimiteți exact aceleași fișiere</strong> încă o dată! Asigurați-vă că fișierele au <strong>aceleași nume și dimensiuni</strong> ca înainte. Când începe încărcarea, veți observa că bara de progres va fi resetată la locul unde a fost întreruptă încărcarea, continuând de acolo.</li>
</ul>

<h3>Încărcarea fișierelor de până la {size:cfg:max_legacy_file_size} per fișier fără HTML5</h3>
<ul class="fa-ul">
<li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} vă va avertiza dacă încercați să încărcați un fișier prea mare pentru această metodă.</li>
<li><i class="fa-li fa fa-caret-right"></i>Reluarea încărcărilor nu este acceptată în așa caz.</li>
</ul>

<h3>Descărcarea fișierelor de orice dimensiune</h3>
<ul class="fa-ul">
<li><i class="fa-li fa fa-caret-right"></i>Orice browser modern este potrivit, nu este necesar nimic special pentru descărcări</li>
</ul>

<h3>Constrângeri de configurare ale serviciului</h3>
<ul class="fa-ul">
<li><i class="fa-li fa fa-caret-right"></i><strong>Număr maxim de destinatari: </strong>{cfg:max_transfer_recipients} adrese de e-mail separate prin virgulă sau punct și virgulă</li>
<li><i class="fa-li fa fa-caret-right"></i><strong>Număr maxim de fișiere per transfer: </strong>{cfg:max_transfer_files}</li>
<li><i class="fa-li fa fa-caret-right"></i><strong>Dimensiune maximă per transfer: </strong>{size:cfg:max_transfer_size}</li>
<li><i class="fa-li fa fa-caret-right"></i><strong>Dimensiunea maximă a fișierului pentru browserele non-HTML5: </strong>{size:cfg:max_legacy_file_size}</li>
<li><i class="fa-li fa fa-caret-right"></i><strong>Zile de expirare a transferului: </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
<li><i class="fa-li fa fa-caret-right"></i><strong>Zile de expirare pentru invitați: </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Detalii tehnice</h3>
<ul class="fa-ul">
<li><i class="fa-li fa fa-caret-right"></i> <strong>{cfg:site_name}</strong> utilizează <a href="http://www.filesender.org/" target="_blank">software-ul FileSender</a>. FileSender indică dacă metoda de încărcare HTML5 este acceptată sau nu pentru un anumit browser. Aceasta depinde în principal de disponibilitatea funcționalității avansate a browserului, în special HTML5 FileAPI. Vă rugăm să utilizați site-ul web <a href="http://caniuse.com/fileapi" target="_blank">"Când pot utiliza..."</a> pentru a monitoriza progresul implementării HTML5 FileAPI pentru toate browserele importante. În special, suportul pentru <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> și <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> trebuie să fie verde deschis (=suportat) pentru ca un browser să suporte încărcări mai mari decât {size:cfg:max_legacy_file_size}. Vă rugăm să rețineți că, deși Opera 12 este menționat ca suportând HTML5 FileAPI, în prezent nu are tot ceea ce este necesar pentru a suporta utilizarea metodei de încărcare HTML5 în FileSender. </li>
</ul>

<p>Pentru mai multe informații, accesați <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>