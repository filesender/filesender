<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fail{if:transfer.files>1}id{endif} on allalaadimiseks valmis
subject: {transfer.subject}

{alternative:plain}

Tere,

{if:transfer.files>1}Failid{else}Fail {endif} on üleslaetud {cfg:site_name} keskkonda {transfer.user_email} poolt ning Teile on antud allalaadimise õigus:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Allalaadimise link: {recipient.download_link}

Aegumise kuupäev {date:transfer.expires}.

{if:transfer.message || transfer.subject}
Personaalne teade aadressilt {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    {if:transfer.files>1}Failid{else}Fail{endif} on üleslaetud <a href="{cfg:site_url}">{cfg:site_name}</a> keskkonda <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> poolt ning Teile on antud allalaadimise õigus.
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
        {if:transfer.files>1}
        <tr>
            <td>Suurus</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Aegumise kuupäev</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Allalaadimise link</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Personaalne sõnum aadressilt {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
