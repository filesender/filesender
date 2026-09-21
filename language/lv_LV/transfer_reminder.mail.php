<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (atgādinājums) Fails{if:transfer.files>1}faili{endif} pieejami lejupielādei
subject: (atgādinājums) {transfer.subject}

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Šis ir atgādinājums, sekojošiem {if:transfer.files>1}failiem{else}failam{endif}, ko {transfer.user_email} augšupielādēja {cfg:site_name},  Jums piešķirta atļauja lejupielādēt {if:transfer.files>1}to{else}tā{endif} saturam :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Lejupielādes saite {recipient.download_link}

Darbība ir pieejama līdz {date:transfer.expires}, pēc kā tā tiks automātiski izdzēsta.

{if:transfer.message || transfer.subject}
Personiska ziņa no {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Šis ir atgādinājums, sekojošiem {if:transfer.files>1}failiem{else}failam{endif}, ko <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> augšupielādēja <a href="{cfg:site_url}">{cfg:site_name}</a>, Jums piešķirta atļauja lejupielādēt {if:transfer.files>1}to{else}tā{endif} saturu.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Pārsūtīšanas detalizēta informācija</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fails{if:transfer.files>1}Faili{endif}</td>
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
            <td>Pārsūtīšanas lielums</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Derīguma termiņš</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Lejupielādes saite</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Personiska ziņa no {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    Ar cieņu<br />
    {cfg:site_name}
</p>
