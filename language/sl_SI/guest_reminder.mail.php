<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (opomnik) vavčer gosta prejet
subject: (opomnik) {guest.subject}

{alternative:plain}

Spoštovani,

to je opomnik o prejetju vavčerja, ki dodeljuje dostop do strani {cfg:site_name}. Uporabite ga za nalaganje enega niza datotek, ki ga lahko date na razpolago skupini ljudi.

Izdajatelj: {guest.user_email}
Povezava vavčerja: {guest.upload_link}

Vavčer je na voljo do {date:guest.expires}. Po tem datumu bo avtomatsko izbrisan.

{if:guest.message}Osebno sporočilo od {guest.user_email}: {guest.message}{endif}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    to je opomnik o prejetju vavčerja, ki dodeljuje dostop do strani <a href="{cfg:site_url}">{cfg:site_name}</a>. Uporabite ga za nalaganje enega niza datotek, ki ga lahko date na razpolago skupini ljudi.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Podrobnosti o vavčerju</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Izdajatelj</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Povezava vavčerja</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Veljavno do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Osebno sporočilo od {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>