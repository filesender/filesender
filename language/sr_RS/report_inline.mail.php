<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Izveštaj o {target.type} #{target.id}

{alternative:plain}

Poštovani,

Ovde je izveštaj o {if:target.type == "Transfer"}transferu{else}{target.type}{endif}:

{if:target.type == "Transfer"}Transfer{else}{target.type}{endif} broj : {target.id}

{if:target.type == "Transfer"}
Ovaj transfer ima sledeći broj fajlova: {transfer.files}, ukupne veličine {size:transfer.size}.

Ovaj transfer je dostupan do {date:transfer.expires}.

Ovaj transfer je poslat sledećem broju primalaca: {transfer.recipients}.
{endif}
{if:target.type == "File"}
Fajl {file.path}, veličine {size:file.size} dostupan do {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Adresa primaoca {recipient.email} važeća do {date:recipient.expires}.
{endif}

Ovde je cela evidencija događaja s transferom :

{raw:content.plain}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Ovde je izveštaj o {if:target.type == "Transfer"}transferu{else}{target.type}{endif}::<br /><br />

    {if:target.type == "Transfer"}Transfer{else}{target.type}{endif} broj :<br /><br />

    {if:target.type == "Transfer"}
    Ovaj transfer ima sledeći broj fajlova: {transfer.files}, ukupne veličine {size:transfer.size}.<br /><br />

    Ovaj transfer je dostupan do {date:transfer.expires}.<br /><br />

    Ovaj transfer je poslat sledećem broju primalaca: {transfer.recipients}.
    {endif}
    {if:target.type == "File"}
    Fajl {file.path}, veličine {size:file.size} važeći do {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Adresa primaoca {recipient.email} važeća do {date:recipient.expires}.
    {endif}
</p>

<p>
    Ovde je cela evidencija događaja s transferom :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Datum</th>
            <th>Događaj</th>
            <th>IP adresa</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Lep pozdrav,<br/>
{cfg:site_name}</p>
