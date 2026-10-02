<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Der er {if:transfer.files>1}en fil{else}filer{endif} klar til hentning
subject: {transfer.subject}

{alternative:plain}

Kære modtager!

Følgende {if:transfer.files>1}filer{else}fil{endif} er blevet uploadet til {cfg:site_name} af {transfer.user_email}, og du har fået lov til at hente indholdet i de{if:transfer.files>1}m{else}e{endif}:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link til hentning: {recipient.download_link}

Du kan hente fil{if:transfer.files>1}erne{else}en{endif} indtil {date:transfer.expires}, hvorefter overførslen automatisk slettes.

{if:transfer.message || transfer.subject}
Personlig meddelelse fra {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære modtager!
</p>

<p>
Følgende {if:transfer.files>1}filer{else}fil{endif} er blevet uploadet til <a href="{cfg:site_url}">{cfg:site_name}</a> af <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>, og du har fået lov til at hente indholdet i de{if:transfer.files>1}m{else}n{endif}:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaljer om overførslen</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fil{if:transfer.files>1}er{endif}</td>
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
            <td>Størrelse af overførsel</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Udløbsdato</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Link til hentning</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Personlig meddelelse fra {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>
