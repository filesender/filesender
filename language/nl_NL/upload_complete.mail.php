<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Bestand{if:transfer.files>1}en{endif} succesvol geüpload

{alternative:plain}

Geachte heer, mevrouw,

{if:transfer.files>1}De volgende bestanden zijn{else}Het volgende bestand is{endif} succesvol geüpload naar {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Meer informatie: {transfer.link}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
 {if:transfer.files>1}De volgende bestanden zijn{else}Het volgende bestand is{endif} succesvol geüpload naar <a href="{cfg:site_url}">{cfg:site_name}</a>.
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
        <tr>
            <td>Grootte</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Meer informatie</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>

