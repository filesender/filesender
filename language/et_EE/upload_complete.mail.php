<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fail{if:transfer.files>1}id{endif} on üleslaetud

{alternative:plain}

Tere,

{if:transfer.files>1}Failid{else}Fail{endif} on üleslaetud {cfg:site_name} veebisaiti.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Lisateave: {transfer.link}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    {if:transfer.files>1}Failid{else}Fail{endif} on üleslaetud <a href="{cfg:site_url}">{cfg:site_name}</a> veebisaiti.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Täpsemalt</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fail{if:transfer.files>1}id{endif}</td>
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
            <td>Suurus</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Allalaadimise link</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
