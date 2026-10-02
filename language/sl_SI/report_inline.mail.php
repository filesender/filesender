<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Poročilo o {target.type} #{target.id}

{alternative:plain}

Spoštovani,

Poročilo o {target.type} se nahaja tu:

{target.type} število : {target.id}

{if:target.type == "Transfer"}
Ta prenos zajema {transfer.files} datotek s skupno velikostjo {size:transfer.size}.

Prenos je (bil) na voljo do {date:transfer.expires}.

Ta prenos je bil poslan {transfer.recipients} prejemnikom.
{endif}
{if:target.type == "File"}
Ta datoteka se imenuje {file.path}, je velikosti {size:file.size} in je (bila) na voljo do {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Ta prejemnik ima e-poštni naslov {recipient.email} in je (bil) veljaven do {date:recipient.expires}.
{endif}

Tukaj se nahaja poln dnevniški zapis aktivnosti tega prenosa :

{raw:content.plain}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Poročilo o {target.type} se nahaja tu:<br /><br />
    
    {target.type} število : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Ta prenos zajema {transfer.files} datotek s skupno velikostjo {size:transfer.size}.<br /><br />
    
    Prenos je (bil) na voljo do {date:transfer.expires}.<br /><br />
    
    Ta prenos je bil poslan {transfer.recipients} prejemnikom.
    {endif}
    {if:target.type == "File"}
    Ta datoteka se imenuje {file.path}, je velikosti {size:file.size} in je (bila) na voljo do {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Ta prejemnik ima e-poštni naslov {recipient.email} in je (bil) veljaven do {date:recipient.expires}.
    {endif}
</p>

<p>
    Tukaj se nahaja poln dnevniški zapis aktivnosti tega prenosa :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Datum</th>
            <th>Dogodek</th>
            <th>IP naslov</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Lep pozdrav,<br/>
{cfg:site_name}</p>