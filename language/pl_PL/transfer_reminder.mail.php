<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (Przypomnienie) Plik dostępny{if:transfer.files>1}Pliki dostępne{endif} do pobrania
subject: (Przypomnienie) {transfer.subject}

{alternative:plain}

Szanowni Państwo,

To jest przypomnienie że {if:transfer.files>1}pliki zostały przesłane{else}plik został przesłany{endif} do {cfg:site_name} przez {transfer.user_email} i zostały Ci nadane uprawnienia do pobrania {if:transfer.files>1}ich{else}jego{endif} zawartości:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link pobrania: {recipient.download_link}

Transakcja jest dostępna do {date:transfer.expires}, po tym czasie wygaśnie.

{if:transfer.message || transfer.subject}
Wiadomość osobista {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    To jest przypomnienie że {if:transfer.files>1}pliki zostały przesłane{else}plik został przesłany{endif} do <a href="{cfg:site_url}">{cfg:site_name}</a> przez <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> i zostały Ci nadane uprawnienia do pobrania {if:transfer.files>1}ich{else}jego{endif} zawartości.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Szczegóły Transferu</th>
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
                {transfer.files.first().name} ({size:transfer.files.first().size})
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
    Wiadomość osobista {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>

