<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (påminning) Fil{if:transfer.files>1}er tilgjengelege{else} tilgjengeleg{endif} for nedlasting
subject: (påminning) {transfer.subject}

{alternative:plain}

Hei,

Dette er ei påminning om at {if:transfer.files>1}desse filene er{else}denne fila er{endif} lasta opp til {cfg:site_name} av {transfer.user_email}, og du har fått løyve til å laste ned {if:transfer.files>1}innhaldet i dei{else}innhaldet i fila{endif}:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Nedlastingslenkje: {recipient.download_link}

Overføringa er tilgjengeleg til {date:transfer.expires}. Etter dette blir ho sletta automatisk.

{if:transfer.message || transfer.subject}
Personleg melding frå {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Dette er ei påminning om at {if:transfer.files>1}desse filene er{else}denne fila er{endif} lasta opp til <a href="{cfg:site_url}">{cfg:site_name}</a> av <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>, og du har fått løyve til å laste ned {if:transfer.files>1}innhaldet i dei{else}innhaldet i fila{endif}.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaljar om overføringa</th>
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
            <td>Storleik på overføringa</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Utløpsdato</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Nedlastingslenkje</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Personleg melding frå {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
