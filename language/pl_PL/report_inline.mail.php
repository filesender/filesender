<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Raport dotyczący {target.type} #{target.id}

{alternative:plain}

Szanowni Państwo,

Poniżej raport dotyczący {target.type}

{target.type} numer: {target.id}

{if:target.type == "Transfer"}
Liczba plików transferu wynosi {transfer.files} o łącznym rozmiarze {size:transfer.size}.

Transfer jest/był dostępny do {date:transfer.expires}.

Transfer wysłano do {transfer.recipients} odbiorców.
{endif}
{if:target.type == "File"}
Nazwa pliku to {file.name}, rozmiar {size:file.size} i jest/był dostępny do {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Adres odbiorcy to {recipient.email} i jest/był aktywny do  {date:recipient.expires}.
{endif}

Poniżej pełen dziennik zdarzeń transferu:

{raw:content.plain}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    Poniżej raport dotyczący {target.type}:<br /><br />
    
    {target.type} numer: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Liczba plików transferu wynosi {transfer.files} o łącznym rozmiarze {size:transfer.size}.<br /><br />
    
    Transfer jest/był dostępny do {date:transfer.expires}.<br /><br />
    
    Transfer został przesłany do {transfer.recipients} odbiorców.
    {endif}
    {if:target.type == "File"}
    Nazwa pliku to {file.name}, rozmiar {size:file.size} i jest/był dostępny do {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Adres odbiorcy to {recipient.email} i jest/był aktywny do  {date:recipient.expires}.
    {endif}
</p>

<p>
    Poniżej pełen dziennik zdarzeń transferu:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Data</th>
            <th>Zdarzenie</th>
            <th>Adres IP</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Z Poważaniem,<br/>
{cfg:site_name}</p>

