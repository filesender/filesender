<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Звіт про {target.type} #{target.id}

{alternative:plain}

Шановні панове,

Ось звіт про ваше {target.type}:

Номер {target.type} : {target.id}

{if:target.type == "Transfer"}
Це пересилання містить {transfer.files} файлів із загальним обсягом {size:transfer.size}.

Це пересилання доступне/було доступне до {date:transfer.expires}.

Це пересилання було надіслано {transfer.recipients} отримувачам.
{endif}
{if:target.type == "File"}
Цей файл має назву {file.path}, розмір {size:file.size} і доступний/був доступний до {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Цей отримувач має email-адресу {recipient.email} і був дійсним до {date:recipient.expires}.
{endif}

Нижче наведено повний журнал подій цього пересилання :

{raw:content.plain}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Ось звіт про ваше {target.type}:<br /><br />
    
    Номер {target.type} : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Це пересилання містить {transfer.files} файлів із загальним обсягом {size:transfer.size}.<br /><br />
    
    Це пересилання доступне/було доступне до {date:transfer.expires}.<br /><br />
    
    Це пересилання було надіслано {transfer.recipients} отримувачам.
    {endif}
    {if:target.type == "File"}
    Цей файл має назву {file.path}, розмір {size:file.size} і доступний/був доступний до {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Цей отримувач має email-адресу {recipient.email} і був дійсним до {date:recipient.expires}.
    {endif}
</p>

<p>
    Нижче наведено повний журнал подій цього пересилання :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Дата</th>
            <th>Подія</th>
            <th>IP-адреса</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>З повагою,<br/>
{cfg:site_name}</p>
