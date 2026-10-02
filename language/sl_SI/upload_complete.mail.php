<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Datoteke{else}Datoteka{endif} uspešno naložena

{alternative:plain}

Spoštovani,

{if:transfer.files>1}Naslednje datoteke so bile uspešno naložene{else}Naslednja datoteka je bila uspešno naložena{endif} na {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Več informacij: {transfer.link}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    The following {if:transfer.files>1}files have{else}file has{endif} been successfully uploaded to <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Podrobnosti prenosa</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{if:transfer.files>1}Datoteke{else}Datoteka{endif}</td>
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
            <td>Velikost</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Več informacij</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>