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
Denne overføringa har {transfer.files} filer med ein samla storleik på {size:transfer.size}.

Overføringa er/var tilgjengeleg til {date:transfer.expires}.

Overføringa vart send til {transfer.recipients} mottakarar.
{endif}
{if:target.type == "File"}
Fila heiter {file.path}, har storleiken {size:file.size} og er/var tilgjengeleg til {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Denne mottakaren har e-postadressa {recipient.email} og er/var gyldig til {date:recipient.expires}.
{endif}

Her er den fullstendige loggen over det som har skjedd med overføringa:

{raw:content.plain}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Her er rapporten om {target.type}:<br /><br />
    
    {target.type} nummer: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Denne overføringa har {transfer.files} filer med ein samla storleik på {size:transfer.size}.<br /><br />
    
    Overføringa er/var tilgjengeleg til {date:transfer.expires}.<br /><br />
    
    Overføringa vart send til {transfer.recipients} mottakarar.
    {endif}
    {if:target.type == "File"}
    Fila heiter {file.path}, har storleiken {size:file.size} og er/var tilgjengeleg til {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Denne mottakaren har e-postadressa {recipient.email} og er/var gyldig til {date:recipient.expires}.
    {endif}
</p>

<p>
    Her er den fullstendige loggen over det som har skjedd med overføringa:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Dato</th>
            <th>Hending</th>
            <th>IP-adresse</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Med venleg helsing,<br/>
{cfg:site_name}</p>
