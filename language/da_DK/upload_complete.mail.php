<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Vellykket filupload

{alternative:plain}

Kære afsender!

Følgende fil{if:transfer.files>1}er{endif} er blevet uploadet til {cfg:site_name} uden problemer.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Yderligere oplysninger: {transfer.link}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>
Følgende fil{if:transfer.files>1}er{endif} er blevet uploadet til  <a href="{cfg:site_url}">{cfg:site_name}</a> uden problemer.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaljer om overførslen</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>File{if:transfer.files>1}er{endif}</td>
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
            <td>Yderligere oplysninger</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>
