<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Dateien stehen{else}Datei steht{endif} zum Download zur Verfügung
subject: {transfer.subject}

{alternative:plain}

Sehr geehrte Damen und Herren,

{if:transfer.files>1}die folgenden Dateien wurden{else}die folgende Datei wurde{endif} von {transfer.user_email} auf {cfg:site_name} hochgeladen und Sie haben die Erlaubnis diese {if:transfer.files>1}Dateien{else}Datei{endif} herunterzuladen:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Download-Link: {recipient.download_link}

Der Dateitransfer wird bis zum {date:transfer.expires} bestehen bleiben, nach dieser Zeit {if:transfer.files>1}werden die Dateien{else}wird die Datei{endif} automatisch gelöscht.

{if:transfer.message || transfer.subject}
Persönliche Nachricht von {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    {if:transfer.files>1}die folgenden Dateien wurden{else}die folgende Datei wurde {endif} von <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> auf <a href="{cfg:site_url}">{cfg:site_name}</a> hochgeladen  und Sie haben die Erlaubnis diese {if:transfer.files>1}Dateien{else}Datei{endif} herunterzuladen.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Details zur Dateiübertragung</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Datei{if:transfer.files>1}en{endif}</td>
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
            <td>Dateiübertragungsgröße</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Gültigkeitsdatum</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Download-Link</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Persönliche Nachricht von {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Mit freundlichen Grüßen,<br />
    {cfg:site_name}
</p>
