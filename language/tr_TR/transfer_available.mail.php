<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Dosyalar{if:transfer.files>1}{endif} indirmeye hazır
konu: {transfer.subject}

{alternative:plain}

Merhaba,

Aşağıdaki dosyalar {if:transfer.files>1} {else} {endif}  {cfg:site_name} {transfer.user_email} tarafından yüklenmiştir ve size de {if:transfer.files>1}their{else}its{endif} contents indirme izni verilmiştir:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

İndirme bağlantısı: {recipient.download_link}

Bu işlem {date:transfer.expires} tarihine kadar mevcuttur ve o tarihten sonra otomatik olarak silinecektir.

{if:transfer.message || transfer.subject}
{transfer.user_email} adresinden kişisel ileti: {transfer.subject}

{transfer.message}
{endif}

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
  Aşağıdaki dosyalar  {if:transfer.files>1} {else} {endif} <a href="{cfg:site_url}">{cfg:site_name}</a> tarafından yüklenmiştir ve size de <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> {if:transfer.files>1}their{else}its{endif} indirme izni verilmiştir.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transaction details</th>
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
        {if:transfer.files>1}
        <tr>
            <td>Transfer size</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Expiry date</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Download link</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    {transfer.user_email} adresinden kişisel ileti:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Saygılar,<br />
    {cfg:site_name}
</p>
