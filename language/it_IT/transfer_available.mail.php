<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: File disponibil{if:transfer.files>1}i{else}e{endif} per il download
subject: {transfer.subject}

{alternative:plain}

Gentile utente,

{if:transfer.files>1}I seguenti file sono stati caricati{else}Il file è stato caricato{endif} su {cfg: site_name} da {transfer.user_email} e ti è stata concessa l'autorizzazione per scaricarl{if:transfer.files>1}i{else}o{endif}:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link per il download: {recipient.download_link}

La transazione è disponibile fino al {data:transfer.expires}, dopodiché verrà automaticamente cancellata.

{if:transfer.message || transfer.subject}
Messaggio personale da {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    {if:transfer.files>1}I seguenti file sono stati caricati{else}Il file è stato caricato{endif} su <a href="{cfg:site_url}">{cfg:site_name}</a> da <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> e ti è stata concessa l'autorizzazione per scaricarl{if:transfer.files>1}i{else}o{endif}.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Dettagli della transazione</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>File</td>
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
            <td>Dimensione</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Scadenza</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Link per il download</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Messaggio personale da {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

