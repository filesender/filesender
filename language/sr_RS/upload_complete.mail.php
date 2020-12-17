<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fajl{if:transfer.files>1}ovi uspešno transferisani{else} uspešno transferisan{endif}

{alternative:plain}

Poštovani,

{if:transfer.files>1}Sledeći fajlovi su uspešno transferisani{else}Sledeći fajl je uspešno transferisan{endif} na {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Više informacija: {transfer.link}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    {if:transfer.files>1}Sledeći fajlovi su uspešno transferisani{else}Sledeći fajl je uspešno transferisan{endif} na <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalji transfera</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fajl{if:transfer.files>1}ovi{endif}</td>
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
    Lep pozdrav,<br />
    {cfg:site_name}
</p>