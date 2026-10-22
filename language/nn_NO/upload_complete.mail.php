<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Filer{else}Fil{endif} lasta opp

{alternative:plain}

Hei,

{if:transfer.files>1}Desse filene er{else}Denne fila er{endif} lasta opp til {cfg:site_name}.

{if:transfer.files>1}Filene kan{else}Fila kan{endif} lastast ned frå denne lenkja: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Meir informasjon: {transfer.link}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    {if:transfer.files>1}Desse filene er{else}Denne fila er{endif} lasta opp til <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<p>
{if:transfer.files>1}Filene kan{else}Fila kan{endif} lastast ned frå denne lenkja: <a href="{transfer.download_link}">{transfer.download_link}</a>
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
        <tr>
            <td>Storleik</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Meir informasjon</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
