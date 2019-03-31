<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Rapport over {target.type} #{target.id}

{alternative:plain}

Geachte heer, mevrouw,

Hier is het rapport over uw {target.type}:

{target.type} nummer : {target.id}

{if:target.type == "Transfer"}
Deze transfer had {transfer.files} bestanden met een totale grootte van {size:transfer.size}.

Deze transfer is/was beschikbaar tot {date:transfer.expires}.

Deze transfer werd verstuurd naar {transfer.recipients} ontvangers.
{endif}
{if:target.type == "File"}
Dit bestand heeft de naam {file.path}, heeft een grootte van {size:file.size} en is/was beschikbaar tot {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Deze ontvanger met e-mailadres {recipient.email} en is/was geldig tot {date:recipient.expires}.
{endif}

Hier is de volledige log van wat er met de transfer gebeurd is :

{raw:content.plain}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    Hier is het rapport over {target.type}:<br /><br />
    
    {target.type} nummer : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Deze transfer heeft {transfer.files} bestanden met een totale grootte van {size:transfer.size}.<br /><br />
    
    Deze transfer is/was beschikbaar tot {date:transfer.expires}.<br /><br />
    
    Deze transfer was verstuurd naar {transfer.recipients} ontvangers.
    {endif}
    {if:target.type == "File"}
    Dit bestand heeft de naam {file.path}, heeft een grootte van {size:file.size} en is/was beschikbaar tot {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Deze ontvanger met e-mailadres {recipient.email} en is/was geldig tot {date:recipient.expires}.
    {endif}
</p>

<p>
    Hier is de volledige log van wat er met de transfer gebeurd is :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Date</th>
            <th>Event</th>
            <th>IP address</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Hoogachtend,<br/>
{cfg:site_name}</p>

