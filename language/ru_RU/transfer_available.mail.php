<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Файл{if:transfer.files>1}ы{endif} доступны для скачивания
subject: {transfer.subject}

{alternative:plain}

Товарищ!

{if:transfer.files>1}Файлы загружены{else}Файл загружен{endif} на {cfg:site_name} пользователем {transfer.user_email} и вам предоставлен доступ к скачиванию.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Ссылка: {recipient.download_link}

Трансфер будет доступен до {date:transfer.expires} после чего автоматически будет удален.

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
    {if:transfer.files>1}Файлы загружены{else}Файл загружен{endif} на <a href="{cfg:site_url}">{cfg:site_name}</a> пользователем <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> и вам предоставлен доступ к скачиванию.
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
    {transfer.message}
</p>
{endif}

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
