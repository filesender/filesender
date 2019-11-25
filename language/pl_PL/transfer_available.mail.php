<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Plik{if:transfer.files>1}i{endif} są dostępne do pobrania
subject: {transfer.subject}

{alternative:plain}

Szanowni Państwo,

Następując{if:transfer.files>1}e pliki zostały wysłane {else}y plik został wysłany {endif} do  {cfg:site_name} przez {transfer.user_email} i możesz pobrać  {if:transfer.files>1}ich{else}jego{endif} zawartość:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link pobrania: {recipient.download_link}

Transakcja jest dostępna do {date:transfer.expires}, po tym czasie wygaśnie.

{if:transfer.message || transfer.subject}
Wiadomość osobista od {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    Następując{if:transfer.files>1}e pliki zostały wysłane {else}y plik został wysłany {endif} do <a href="{cfg:site_url}">{cfg:site_name}</a> przez <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> i możesz pobrać  {if:transfer.files>1}ich{else}jego{endif} zawartość.
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
        {if:transfer.files>1}
        <tr>
            <td>Wielkość</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Wygaśnięcie</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Link</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Wiadomość osobista od {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>

