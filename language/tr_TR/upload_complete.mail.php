<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Dosyalar {if:transfer.files>1} {endif} başarıyla yüklendi

{alternative:plain}

Merhaba,

Aşağıdaki dosyalar {if:transfer.files>1} {else} {endif} b {cfg:site_name} başarıyla yüklenmiştir.

Bu dosyalar aşağıdaki bağlantı kullanarak indirilebilir: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Daha fazla bilgi: {transfer.link}

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    Aşağıdaki dosyalar {if:transfer.files>1} {else} {endif} <a href="{cfg:site_url}">{cfg:site_name}</a> aşarıyla yüklenmiştir.
</p>

<p>
Bu dosyalar aşağıdaki bağlantı kullanarak indirilebilir: <a href="{transfer.download_link}">{transfer.download_link}</a>
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transaction details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Dosyalar{if:transfer.files>1} {endif}</td>
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
            <td>Boyut</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Daha fazla bilgi</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>