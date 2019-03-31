<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Rapporto su {target.type} #{target.id}

{alternative:plain}

Gentile utente,

Ecco il rapporto sul tuo {target.type}:

Numero {target.type} : {target.id}

{if:target.type == "Transfer"}
Questo trasferimento contiene {transfer.files} file con una dimensione complessiva di  {size:transfer.size}.

Questo trasferimento è/era disponibile fino al {date:transfer.expires}.

Questo trasferimento è stato inviato a {transfer.recipients} recipients.
{endif}
{if:target.type == "File"}
Questo file è denominato {file.path}, ha una dimensione di {size:file.size} ed è/era disponibile fino al {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Questo destinatario ha l'indirizzo email {recipient.email} ed è / era valido fino al {date:recipient.expires}.
{endif}

Ecco il registro completo di ciò che è successo al trasferimento :

{raw:content.plain}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Ecco il rapporto sul tuo {target.type}:<br /><br />
    
    Numero {target.type} : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Questo trasferimento contiene {transfer.files} file con una dimensione complessiva di  {size:transfer.size}.<br /><br />
    
    Questo trasferimento è/era disponibile fino al {date:transfer.expires}.<br /><br />
    
    Questo trasferimento è stato inviato a {transfer.recipients} recipients.
    {endif}
    {if:target.type == "File"}
    Questo file è denominato {file.path}, ha una dimensione di {size:file.size} ed è/era disponibile fino al {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Questo destinatario ha l'indirizzo email {recipient.email} ed è / era valido fino al {date:recipient.expires}.
    {endif}
</p>

<p>
    Ecco il registro completo di ciò che è successo al trasferimento :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Data</th>
            <th>Evento</th>
            <th>Indirizzo IP</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Cordiali saluti,<br/>
{cfg:site_name}</p>

