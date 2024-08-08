<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Tiedostosiirto valmistui

{alternative:plain}

Hei!

Seuraava tiedosto tai tiedostot on siirretty onnistuneesti palveluun {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Lisätietoja: {transfer.link}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Seuraava tiedosto tai tiedostot on siirretty onnistuneesti palveluun <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Tiedonsiirto</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Tiedosto{if:transfer.files>1}t{endif}</td>
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
            <td>Koko</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Lisätietoja</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>

