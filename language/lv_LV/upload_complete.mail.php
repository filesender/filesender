<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
temats: Fails{if:transfer.files>1}(-i){endif} veiksmīgi augšupielādēti

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Sekojoši {if:transfer.files>1}faili tika{else}fails tika{endif} sekmīgi augšupielādēts {cfg:site_name}.

Šos failus var lejupielādēt, izmantojot šādu saiti:
{transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Papildinformācija: {transfer.link}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Sekojoši {if:transfer.files>1}faili tika{else}fails tika{endif} sekmīgi augšupielādēts <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<p>
Šos failus var lejupielādēt, izmantojot šādu saiti <a href="{transfer.download_link}">{transfer.download_link}</a>
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Pārsūtīšanas detalizēta informācija</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fails{if:transfer.files>1}i{endif}</td>
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
            <td>Lielums</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Papildinformācija</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Ar cieņu<br />
    {cfg:site_name}
</p>