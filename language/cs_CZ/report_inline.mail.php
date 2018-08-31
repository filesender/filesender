subject: Zpráva o {target.type} #{target.id}

{alternative:plain}

Vážený uživateli,

Zde je zpráva o {target.type}:

{target.type} číslo: {target.id}

{if:target.type == "Transfer"}
Počet souborů: {transfer.files} s celkovou velikostí {size:transfer.size}.

Přenos je/byl dostupný do {date:transfer.expires}.

Počet notifikovaných příjemců:  {transfer.recipients} .
{endif}
{if:target.type == "File"}
Jméno souboru: {file.path}, velikost: {size:file.size}, soubor je/byl dostupný do {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Příjemce {recipient.email}  je/byl dostupný do {date:recipient.expires}.
{endif}

Níže naleznete kompletní protokol o přenosu:

{raw:content.plain}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Zde je zpráva o {target.type}:<br /><br />
    
    {target.type} číslo: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Počet souborů: {transfer.files}, velikost {size:transfer.size}.<br /><br />
    
    Přenos je/byl dostupný do {date:transfer.expires}.<br /><br />
    
    Počet notifikovaných příjemců: {transfer.recipients}.
    {endif}
    {if:target.type == "File"}
    Jméno souboru: {file.path}, velikost: {size:file.size}, soubor je/byl dostupný do {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Příjemce {recipient.email} je/byl dostupný do {date:recipient.expires}.
    {endif}
</p>

<p>
    Níže naleznete kompletní protokol o přenosu:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Datum</th>
            <th>Událost</th>
            <th>IP adresa</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>S pozdravem,<br/>
{cfg:site_name}</p>

