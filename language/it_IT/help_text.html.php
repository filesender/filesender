<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Accedi</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Accedi con uno dei provider di identità elencati usando il tuo account istituzionale standard. Se non vedi il tuo istituto nell'elenco o il tuo login fallisce, contatta il tuo referente di ente locale</li>
</ul>

<h3>Funzioni del tuo browser</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> Puoi caricare file di qualsiasi dimensione fino a {size:cfg:max_transfer_size} per trasferimento.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> Puoi caricare file di massimo {size:cfg:max_legacy_file_size} ciascuno e fino a {size:cfg:max_transfer_size} per trasferimento.</li>
</ul>

<h3>Upload di <i>qualsiasi dimensione</i> con HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Sarai in grado di utilizzare questo metodo se questo simbolo<img src="images/html5_installed.png" alt="HTML5 upload enabled" /> viene visualizzato sopra</li>
    <li><i class="fa-li fa fa-caret-right"></i>Per abilitare questa funzione usa semplicemente un browser aggiornato che supporta HTML5, l'ultima versione del "linguaggio del web".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Funzionano le versioni aggiornate di Firefox e Chrome su Windows, Mac OS X e Linux. </li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Puoi <strong>riprendere</strong> un caricamento interrotto o annullato. Per riprendere un caricamento, semplicemente <strong>invia di nuovo gli stessi file </strong> di nuovo !
        Assicurati che i file abbiano <strong>stessi nomi e dimensioni</strong> di prima.
Quando inizia il caricamento, dovresti notare la barra di avanzamento saltare dove il caricamento è stato interrotto e continuare da lì.
    </li>
</ul>

<h3>Upload fino a {size:cfg:max_legacy_file_size} per file senza HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender ti avviserà se dovessi provare a caricare un file troppo grande per questo metodo.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Il ripristino dei caricamenti non è supportato con questo metodo.</li>
</ul>

<h3>Downloads di qualsiasi dimensione</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Qualsiasi browser moderno andrà bene, non è richiesto nulla di speciale per i download</li>
</ul>

<h3>Limitazioni del servizio</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Numero massimo di destinatari : </strong>{cfg:max_transfer_recipients} indirizzi email separati da virgola o punto e virgola</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Numero massimo di file per trasferimento : </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Dimensione massima per trasferimento : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Dimensione massima file per file per browser non HTML5 : </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Giorni di scadenza del trasferimento : </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Giorni di scadenza degli ospiti : </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Dettagli tecnici</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> utilizza il <a href="http://www.filesender.org/" target="_blank">software FileSender</a>.
        FileSender indica se il metodo di caricamento HTML5 è supportato o meno per un determinato browser.
        Ciò dipende principalmente dalla disponibilità di funzionalità avanzate del browser, in particolare da FileAPI HTML5.
        Utilizza il sito <a href="http://caniuse.com/fileapi" target="_blank">"Can I use..."</a> per monitorare l'avanzamento dell'implementazione di FileBI HTML5 per tutti i principali browser.
        In particolare il supporto per <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> e <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> devono essere di colore verde chiaro (= supportato) per un browser che supporti caricamenti superiori a {size:cfg:max_legacy_file_size}.
        Si noti che sebbene Opera 12 sia elencato per supportare l'FileIPI HTML5, attualmente non supporta tutto ciò che è necessario per supportare l'uso del metodo di caricamento HTML5 in FileSender.
    </li>
</ul>

<p>Per ulteriori informazioni, visita <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>

