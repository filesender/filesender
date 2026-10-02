<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Atskaite par {target.type} #{target.id}

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Zemāk pieejama atskaite par Jūsu {target.type}:

{target.type} Nr. : {target.id}

{if:target.type == "Transfer"}
Šajā pārsūtīšanā ir {transfer.files} faili, kuru kopējais izmērs ir {size:transfer.size}.

Šī pārsūtīšana ir/bija pieejama līdz {date:transfer.expires}.

Šī pārsūtīšana tika nosūtīta {transfer.recipients} adresātam.
{endif}
{if:target.type == "File"}
Šī faila nosaukums ir {file.path}, tā lielums ir {size:file.size} un tas ir/bija pieejams līdz {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Šim adresātam ir e-pasta adrese {recipient.email} un tā ir/bija derīga līdz  {date:recipient.expires}.
{endif}

Zemāk redzams pilns žurnāls par to, kas noticis ar pārsūtīšanu :

{raw:content.plain}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Zemāk pieejama atskaite par Jūsu {target.type}:<br /><br />
    
    {target.type} Nr. : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Šajā pārsūtīšanā ir {transfer.files} faili, kuru kopējais izmērs ir {size:transfer.size}.<br /><br />
    
    Šī pārsūtīšana ir/bija pieejama līdz {date:transfer.expires}.<br /><br />
    
    Šī pārsūtīšana tika nosūtīta {transfer.recipients} adresātam.
    {endif}
    {if:target.type == "File"}
    Šī faila nosaukums ir {file.path}, tā lielums ir {size:file.size} un tas ir/bija pieejams līdz {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Šim adresātam ir e-pasta adrese {recipient.email} un tā ir/bija derīga līdz {date:recipient.expires}.
    {endif}
</p>

<p>
    Zemāk redzams pilns žurnāls par to, kas noticis ar pārsūtīšanu :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Datums</th>
            <th>Notikums</th>
            <th>IP adrese</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Ar cieņu<br/>
{cfg:site_name}</p>