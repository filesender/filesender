<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (reminder) File{if:transfer.files>1}s{endif} available for download
subject: (reminder) {transfer.subject}

{alternative:plain}

Товарищ!

Это напоминание о том, что {if:transfer.files>1}файлы были{else}файл был{endif} загружен на {cfg:site_name} товарищем {transfer.user_email} и ты получил доступ для скачивания содержимого :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Ссылка для скачивания: {recipient.download_link}

Срок истечения трансфера: {date:transfer.expires}, после которого он будет автоматически удален.

{if:transfer.message || transfer.subject}
Персональное сообщение от {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Это напоминание о том, что {if:transfer.files>1}файлы были{else}файл был{endif} загружен на <a href="{cfg:site_url}">{cfg:site_name}</a> товарищем <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> и ты получил доступ для скачивания содержимого.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Детали трансфера</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Файл{if:transfer.files>1}ы{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        {if:transfer.files>1}
        <tr>
            <td>Размер трансфера</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Срок истечения</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Ссылка для скачивания</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Персональное сообщение от {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
