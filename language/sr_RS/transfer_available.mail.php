<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fajl{if:transfer.files>1}ovi dostupni{else} dostupan{endif} za preuzimanje
subject: {transfer.subject}

{alternative:plain}

Poštovani,

Sledeći {if:transfer.files>1}fajlovi su transferisani{else}fajl je transferisan{endif} na {cfg:site_name} od {transfer.user_email} i Vi imate ovlašćenje za preuzimanje  {if:transfer.files>1}njihovog{else}njegovog{endif} sadržaja :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link za preuzimanje: {recipient.download_link}

Transfer je dostupan do {date:transfer.expires} i nakon toga će biti automatski obrisan.

{if:transfer.message || transfer.subject}
Lična poruka od {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Sledeći {if:transfer.files>1}fajlovi su transferisani{else}fajl je transferisan{endif}  na <a href="{cfg:site_url}">{cfg:site_name}</a> od <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> i Vi imate ovlašćenje za preuzimanje {if:transfer.files>1}njihovog{else}njegovog{endif} sadržaja.
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
        {if:transfer.files>1}
        <tr>
            <td>Veličina</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Važi do</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Link za preuzimanje</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Lična poruka od {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>
