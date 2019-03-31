<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Отчет о {target.type} #{target.id}

{alternative:plain}

Товарищ!

Вот отчет о {target.type}:

{target.type} номер : {target.id}

{if:target.type == "Transfer"}
Этот трансфер включает в себя файлы {transfer.files} общим размером {size:transfer.size}.

Трансфер:
 - доступен до: {date:transfer.expires}.
 - был отправлен получателям: {transfer.recipients}
{endif}
{if:target.type == "File"}
Файл {file.path} с размером {size:file.size} доступен до {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Получатель, имеющий адрес {recipient.email} был действителен до {date:recipient.expires}.
{endif}

Вот полный отчет по трансферу:

{raw:content.plain}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Вот отчет о {target.type}:<br /><br />
    
    {target.type} номер : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Этот трансфер включает в себя файлы {transfer.files} общим размером {size:transfer.size}.<br /><br />
    Трансфер:<br />
     - доступен до: {date:transfer.expires}.<br />
     - был отправлен получателям: {transfer.recipients}<br />
    {endif}
    {if:target.type == "File"}
    Файл {file.path} с размером {size:file.size} доступен до {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Получатель, имеющий адрес {recipient.email} был действителен до {date:recipient.expires}.
    {endif}
</p>

<p>
    Вот полный отчет по трансферу:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Дата</th>
            <th>Событие</th>
            <th>IP-адрес</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>С наилучшими пожеланиями,<br/>
{cfg:site_name}</p>
