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

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
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

</td></tr>
 <tr style="border-style:none">
    <td align="center">
       <p style="font-size:12px; text-decoration:none">
       Meer informatie over de SURFfilesender dienst is beschikbaar op
       <a rel="nofollow" href="https://www.surffilesender.nl/" target="_blank">www.surffilesender.nl</a>
       </p>
       <p style="font-size:10px; text-decoration:none"> SURFfilesender is powered by <a rel="nofollow" href="https://www.surf.nl/" target="_blank">SURF</a>.
       </p>
    </td>
</tr>
</table>
