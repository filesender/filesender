<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Primljen kupon za gosta
subject: {guest.subject}

{alternative:plain}

Poštovani,

Niže se nalazi kupon koji Vam omogućuje pristup na {cfg:site_name}. Pomoću ovog kupona možete prenijeti jedan skup datoteka i učiniti ga dostupnim za preuzimanje grupi ljudi.

Izdano: {guest.user_email}
Poveznica na kupon: {guest.upload_link}

Kupon je dostupan do {date:guest.expires} i nakon toga će automatski biti obrisan.

{if:guest.message}Osobna poruka od {guest.user_email}: {guest.message}{endif}

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Niže se nalazi kupon koji Vam omogućuje pristup na <a href="{cfg:site_url}">{cfg:site_name}</a>. Pomoću ovog kupona možete prenijeti jedan skup datoteka i učiniti ga dostupnim za preuzimanje grupi ljudi.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Informacije o kuponu</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Izdano</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Poveznica na kupon</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Vrijedi do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Osobna poruka od {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>