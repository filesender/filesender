<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Izvještaj o {target.type} #{target.id}

{alternative:plain}

Poštovani,

Ovdje je izvještaj o {if:target.type == "Transfer"}prijenosu{else}{target.type}{endif}:

{if:target.type == "Transfer"}Prijenos{else}{target.type}{endif} broj : {target.id}

{if:target.type == "Transfer"}
Ovaj prijenos ima slijedeći broj datoteka: {transfer.files}, ukupne veličine {size:transfer.size}.

Ovaj prijenos je dostupan do {date:transfer.expires}.

Ovaj prijenos je poslan slijedećem broju primatelja: {transfer.recipients}.
{endif}
{if:target.type == "File"}
Datoteka {file.path}, veličine {size:file.size} dostupna do {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Primateljeva adresa {recipient.email} važeća do {date:recipient.expires}.
{endif}

Ovdje je cijeli zapisnik događaja s prijenosom :

{raw:content.plain}

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Ovdje je izvještaj o {if:target.type == "Transfer"}prijenosu{else}{target.type}{endif}::<br /><br />

    {if:target.type == "Transfer"}Prijenos{else}{target.type}{endif} broj :<br /><br />

    {if:target.type == "Transfer"}
    Ovaj prijenos ima slijedeći broj datoteka: {transfer.files}, ukupne veličine {size:transfer.size}.<br /><br />

    Ovaj prijenos je dostupan do {date:transfer.expires}.<br /><br />

    Ovaj prijenos je poslan slijedećem broju primatelja: {transfer.recipients}.
    {endif}
    {if:target.type == "File"}
    Datoteka {file.path}, veličine {size:file.size} važeća do {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Primateljeva adresa {recipient.email} važeća do {date:recipient.expires}.
    {endif}
</p>

<p>
    Ovdje je cijeli zapisnik događaja s prijenosom :
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

<p>Lijepi pozdrav,<br/>
{cfg:site_name}</p>
