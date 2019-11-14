<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {target.type} #{target.id} aruanne

{alternative:plain}

Tere,

{target.type} aruanne on järgmine:

{target.type} number : {target.id}

{if:target.type == "Transfer"}
Antud failijagamine sisaldab {transfer.files} faili kogumahuga {size:transfer.size}.

Antud failijagamine on/oli saadaval kuni {date:transfer.expires}.

Antud failijagamisega on seotud e-posti aadressid: {transfer.recipients}.
{endif}
{if:target.type == "File"}
Antud fail nimega {file.path} ning mahuga {size:file.size} ja on/oli saadaval kuni {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Antud saaja e-postiaadress on {recipient.email} ja on/oli kehtiv kuni {date:recipient.expires}.
{endif}

Failijagamise logi on järgmine :

{raw:content.plain}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    {target.type} aruanne on järgmine:<br /><br />
    
    {target.type} number : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Antud failijagamine sisaldab {transfer.files} faili kogumahuga {size:transfer.size}.<br /><br />
    
    Antud failijagamine on/oli saadaval kuni {date:transfer.expires}.<br /><br />
    
    Antud failijagamisega on seotud e-posti aadressid: {transfer.recipients}.
    {endif}
    {if:target.type == "File"}
    Antud fail nimega {file.path} ning mahuga {size:file.size} ja on/oli saadaval kuni {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Antud saaja e-postiaadress on {recipient.email} ja on/oli kehtiv kuni {date:recipient.expires}.
    {endif}
</p>

<p>
    Failijagamise logi on järgmine :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Kuupäev</th>
            <th>Sündmus</th>
            <th>IP aadress</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Lugupidamisega,<br/>
{cfg:site_name}</p>
