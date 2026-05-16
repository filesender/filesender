<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Файл{if:transfer.files>1}и{endif} успішно завантажені

{alternative:plain}

Шановні панове,

Наступні {if:transfer.files>1}файли були{else}файл був{endif} успішно завантажені на {cfg:site_name}.

Ці файли можна завантажити за наступним посиланням: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Додаткова інформація: {transfer.link}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Наступні {if:transfer.files>1}файли були{else}файл був{endif} успішно завантажені на <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<p>
Ці файли можна завантажити за посиланням <a href="{transfer.download_link}">{transfer.download_link}</a>
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Деталі операції</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Файл{if:transfer.files>1}и{endif}</td>
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
        <tr>
            <td>Розмір</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Додаткова інформація</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
