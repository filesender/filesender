<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: File caricat{if:transfer.files>1}i{else}o{endif} correttamente

{alternative:plain}

Gentile utente,

{if:transfer.files>1}I file sono stati caricati{else}Il file è stato caricato{endif} con successo su {cfg: site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Ulteriori informazioni: {transfer.link}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
   {if:transfer.files>1}I file sono stati caricati{else}Il file è stato caricato{endif} con successo su <a href="{cfg:site_url}">{cfg:site_name}</a>.
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
        <tr>
            <td>Dimensione</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Ulteriori informazioni</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

