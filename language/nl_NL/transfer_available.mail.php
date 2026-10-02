<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Bestand{if:transfer.files>1}en{endif} beschikbaar voor download
subject: {transfer.subject}

{alternative:plain}

Geachte heer, mevrouw,

{if:transfer.files>1}De volgende bestanden zijn{else}Het volgende bestand is{endif} geüpload naar {cfg:site_name} door {transfer.user_email} en u hebt toestemming gekregen om {if:transfer.files>1}ze{else}het{endif} te downloaden :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Download link: {recipient.download_link}

De transactie is beschikbaar tot {date:transfer.expires} na die tijd wordt het automatisch verwijderd.

{if:transfer.message || transfer.subject}
Persoonlijk bericht van {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
 {if:transfer.files>1}De volgende bestanden zijn{else}Het volgende bestand is{endif} geüpload naar <a href="{cfg:site_url}">{cfg:site_name}</a> door <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> en u hebt toestemming gekregen om {if:transfer.files>1}ze{else}het{endif} te downloaden :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transactie details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Bestand{if:transfer.files>1}en{endif}</td>
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
            <td>Transfer grootte</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Vervaldatum</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Download link</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Persoonlijk bericht van {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>