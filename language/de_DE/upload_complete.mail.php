<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Datei{if:transfer.files>1}en{endif} erfolgreich hochgeladen

{alternative:plain}

Sehr geehrte Damen und Herren,

die {if:transfer.files>1}folgenden Dateien wurden{else}folgende Datei wurde{endif} erfolgreich zum {cfg:site_name} hochgeladen.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Weitere Informationen: {transfer.link}

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    die {if:transfer.files>1}folgenden Dateien wurden{else}folgende Datei wurde{endif} erfolgreich zum <a href="{cfg:site_url}">{cfg:site_name}</a> hochgeladen.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transaktionsdetails</th>
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
        <tr>
            <td>Size</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Weitere Informationen</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Mit freundlichen Grüßen,<br />
    {cfg:site_name}
</p>