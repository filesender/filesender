
onderwerp: Rapport over {target.type} #{target.id}

{alternative:plain}

Geachte mevrouw, heer,

Hier is het rapport over {target.type}:

{target.type} nummer : {target.id}

{if:target.type == "Transfer"}
Deze transfer heeft {transfer.files} bestanden met een totale grootte van {size:transfer.size}.

Deze transfer is/was beschikbaar tot {date:transfer.expires}.

Deze transfer was verstuurd naar {transfer.recipients} ontvangers.
{endif}
{if:target.type == "File"}
Dit bestand heeft de naam {file.path}, heeft een grootte van {size:file.size} en is/was beschikbaar tot {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Deze ontvanger met e-mailadres {recipient.email} en is/was geldig tot {date:recipient.expires}.
{endif}

Hier is de volledige log van wat er met de transfer gebeurt is :

{raw:content.plain}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
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
    Hier is de volledige log van wat er met de transfer gebeurt is :
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

