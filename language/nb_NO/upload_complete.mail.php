<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Filer{else}Fil{endif} lastet opp

{alternative:plain}

Hei,

{if:transfer.files>1}Disse filene er{else}Denne filen er{endif} lastet opp til {cfg:site_name}.

{if:transfer.files>1}Filene kan{else}Filen kan{endif} lastes ned fra denne lenken: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Mer informasjon: {transfer.link}

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    {if:transfer.files>1}Disse filene er{else}Denne filen er{endif} lastet opp til <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<p>
{if:transfer.files>1}Filene kan{else}Filen kan{endif} lastes ned fra denne lenken: <a href="{transfer.download_link}">{transfer.download_link}</a>
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
        <tr>
            <td>Størrelse</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Mer informasjon</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
