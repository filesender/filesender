<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Файл{if:transfer.files>1}ы{endif} успешно загружен{if:transfer.files>1}ы{endif}

{alternative:plain}

Товарищ!

{if:transfer.files>1}Файлы были загружены{else}Файл был загружен{endif} на {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Получить полную информацию: {transfer.link}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    {if:transfer.files>1}Файлы были загружены{else}Файл был загружен{endif} на <a href="{cfg:site_url}">{cfg:site_name}</a>.
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
        <tr>
            <td>Size</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Получить полную информацию</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
