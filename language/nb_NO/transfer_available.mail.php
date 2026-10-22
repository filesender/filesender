<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fil{if:transfer.files>1}er tilgjengelige{else} tilgjengelig{endif} for nedlasting
subject: {transfer.subject}

{alternative:plain}

Hei,

{if:transfer.files>1}Disse filene er{else}Denne filen er{endif} lastet opp til {cfg:site_name} av {transfer.user_email}, og du har fått tillatelse til å laste ned {if:transfer.files>1}innholdet i dem{else}innholdet i filen{endif}:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Nedlastingslenke: {recipient.download_link}

Overføringen er tilgjengelig til {date:transfer.expires}. Etter dette slettes den automatisk.

{if:transfer.message || transfer.subject}
Personlig melding fra {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    {if:transfer.files>1}Disse filene er{else}Denne filen er{endif} lastet opp til <a href="{cfg:site_url}">{cfg:site_name}</a> av <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>, og du har fått tillatelse til å laste ned {if:transfer.files>1}innholdet i dem{else}innholdet i filen{endif}.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaljer om overføringen</th>
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
            <td>Størrelse på overføringen</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Utløpsdato</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Nedlastingslenke</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Personlig melding fra {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
