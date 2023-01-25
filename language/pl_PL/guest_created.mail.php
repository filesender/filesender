<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Otrzymano kupon Gościa
subject: {guest.subject}

{alternative:plain}

Szanowni Państwo,

Poniżej znajdziecie Państwo kupon Gościa który pozwala na dostęp do serwisu {cfg:site_name}. Można go wykorzystać do przesłania jednej grupy plików, którą będzie mogła pobrać grupa osób.

Wystawca: {guest.user_email}
Link do Kuponu: {guest.upload_link}

Kupon jest ważny do {date:guest.expires}, po tym okresie automatycznie straci ważność.

{if:guest.message}Wiadomość osobista od {guest.user_email}: {guest.message}{endif}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    Poniżej znajdziecie Państwo kupon Gościa który pozwala na dostęp do serwisu <a href="{cfg:site_url}">{cfg:site_name}</a>. Można go wykorzystać do przesłania jednej grupy plików, którą będzie mogła pobrać grupa osób.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Szczególy Kuponu</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Wystawca</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Link</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Ważność</td>
{if:guest.does_not_expire}
            <td>nigdy</td>
{else}
            <td>{date:guest.expires}</td>
{endif}
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Wiadomość osobista od {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>

