<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Plik{if:transfer.files>1}i{endif} z powodzeniem wysłano

{alternative:plain}

Szanowni Państwo,

{if:transfer.files>1}Następujące pliki zostały z powodzeniem przesłane{else}Następujący plik został z powodzeniem przesłany{endif} do {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Więcej informacji: {transfer.link}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    {if:transfer.files>1}Następujące pliki zostały z powodzeniem przesłane{else}Następujący plik został z powodzeniem przesłany{endif} do <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Szczegóły Transakcji</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Plik{if:transfer.files>1}i{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.name} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        <tr>
            <td>Wielkośc</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Więcej informacji</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>