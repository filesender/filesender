<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Rapport for {if:target.type == "Transfer"}overførsel{endif}{if:target.type == "File"}fil{endif}{if:target.type == "Recipient"}modtager{endif} #{target.id}

{alternative:plain}

Kære afsender!

Her er rapporten om din {if:target.type == "Transfer"}overførsel{endif}{if:target.type == "File"}fil{endif}{if:target.type == "Recipient"}modtager{endif}:

{if:target.type == "Transfer"}Overførsel{endif}{if:target.type == "File"}Fil{endif}{if:target.type == "Recipient"}Modtager{endif} nr. {target.id}

{if:target.type == "Transfer"}
Overførslen her omfatter {transfer.files} fil(er) med en samlet størrelse på {size:transfer.size}.

Overførslen er/var tilgængelig indtil {date:transfer.expires}.

Overførslen blev afsendt til {transfer.recipients} modtager(e).
{endif}
{if:target.type == "File"}
Filen hedder {file.path}, fylder {size:file.size} og er/var tilgængelig indtil {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Modtageren har e-mailaddressen {recipient.email} og er berettiget indtil {date:recipient.expires}.
{endif}

Her er den fuldstændige log over hvad der er sket med overførslen:

{raw:content.plain}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>
Her er rapporten om din {if:target.type == "Transfer"}overførsel{endif}{if:target.type == "File"}fil{endif}{if:target.type == "Recipient"}modtager{endif}:<br /><br />
    
    {if:target.type == "Transfer"}Overførsel{endif}{if:target.type == "File"}Fil{endif}{if:target.type == "Recipient"}Modtager{endif} nr.: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Overførslen her omfatter {transfer.files} fil(er) med en samlet størrelse på {size:transfer.size}.<br /><br />
    
    Overførslen er/var tilgængelig indtil {date:transfer.expires}.<br /><br />
    
    Overførslen blev afsendt til {transfer.recipients} modtager(e).
    {endif}
    {if:target.type == "File"}
    Filen hedder {file.path}, fylder {size:file.size} og er/var tilgængelig indtil {date:file.transfer.expires}.{date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Modtageren har e-mailaddressen {recipient.email} og er berettiget indtil {date:recipient.expires}.
    {endif}
</p>

<p>
    Her er den fuldstændige log over hvad der er sket med overførslen:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Dato</th>
            <th>Begivenhed</th>
            <th>IP-adresse</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Med venlig hilsen<br/>
{cfg:site_name}</p>