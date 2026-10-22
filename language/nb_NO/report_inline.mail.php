<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Rapport om {target.type} nr. {target.id}

{alternative:plain}

Hei,

Her er rapporten om {target.type}:

{target.type} nummer: {target.id}

{if:target.type == "Transfer"}
Denne overføringen har {transfer.files} filer med en samlet størrelse på {size:transfer.size}.

Overføringen er/var tilgjengelig til {date:transfer.expires}.

Overføringen ble sendt til {transfer.recipients} mottakere.
{endif}
{if:target.type == "File"}
Filen heter {file.path}, har størrelsen {size:file.size} og er/var tilgjengelig til {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Denne mottakeren har e-postadressen {recipient.email} og er/var gyldig til {date:recipient.expires}.
{endif}

Her er den fullstendige loggen over det som har skjedd med overføringen:

{raw:content.plain}

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Her er rapporten om {target.type}:<br /><br />
    
    {target.type} nummer: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Denne overføringen har {transfer.files} filer med en samlet størrelse på {size:transfer.size}.<br /><br />
    
    Overføringen er/var tilgjengelig til {date:transfer.expires}.<br /><br />
    
    Overføringen ble sendt til {transfer.recipients} mottakere.
    {endif}
    {if:target.type == "File"}
    Filen heter {file.path}, har størrelsen {size:file.size} og er/var tilgjengelig til {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Denne mottakeren har e-postadressen {recipient.email} og er/var gyldig til {date:recipient.expires}.
    {endif}
</p>

<p>
    Her er den fullstendige loggen over det som har skjedd med overføringen:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Dato</th>
            <th>Hendelse</th>
            <th>IP-adresse</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Med vennlig hilsen,<br/>
{cfg:site_name}</p>
