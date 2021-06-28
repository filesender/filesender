<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Datotek{if:transfer.files>1}e uspješno prenesene{else}a uspješno prenesena{endif}

{alternative:plain}

Poštovani,

{if:transfer.files>1}Slijedeće datoteke su uspješno prenesene{else}Slijedeća datoteka je uspješno prenesena{endif} na {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Više informacija: {transfer.link}

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    {if:transfer.files>1}Slijedeće datoteke su uspješno prenesene{else}Slijedeća datoteka je uspješno prenesena{endif} na <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalji prijenosa</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>File{if:transfer.files>1}s{endif}</td>
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
            <td>Veličina</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Više informacija</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>