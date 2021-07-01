<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (podsjetnik) Datotek{if:transfer.files>1}e dostupne{else}a dostupna{endif} za preuzimanje
subject: {transfer.subject}

{alternative:plain}

Poštovani,

Ovo je podsjetnik da {if:transfer.files>1}su datoteke prenesene{else}je datoteka prenesena{endif} na {cfg:site_name} od {transfer.user_email} i vi imate ovlasti za preuzimanje {if:transfer.files>1}njihovog{else}njenog{endif} sadržaja :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Poveznica preuzimanja: {recipient.download_link}

Prijenos je dotupan do {date:transfer.expires} i nakon toga će biti automatski obrisan.

{if:transfer.message || transfer.subject}
Osobna poruka od {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Slijedeć{if:transfer.files>1}fe datoteke su prenesene{else}a datoteka je prenesena{endif} na <a href="{cfg:site_url}">{cfg:site_name}</a> od <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> i Vi imate ovlasti za preuzimanje {if:transfer.files>1}njihovog{else}njenog{endif} sadržaja.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalji prijenosa</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Datotek{if:transfer.files>1}e{else}a{endif}</td>
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
            <td>Vrijedi do</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Poveznica preuzimanja</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Osobna poruka od {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>
