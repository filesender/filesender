<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Primljen vaučer za gosta
subject: {guest.subject}

{alternative:plain}

Poštovani,

U nastavku se nalazi vaučer koji Vam omogućuje pristup na {cfg:site_name}. Pomoću ovog vaučera možete postaviti jedan skup fajlova i učiniti ga dostupnim za preuzimanje grupi ljudi.

Izdavalac: {guest.user_email}
Link vaučera: {guest.upload_link}

Vaučer je dostupan do {date:guest.expires} i nakon toga će automatski biti obrisan.

{if:guest.message}Lična poruka od {guest.user_email}: {guest.message}{endif}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    U nastavku se nalazi vaučer koji Vam omogućuje pristup na <a href="{cfg:site_url}">{cfg:site_name}</a>. Pomoću ovog vaučera možete postaviti jedan skup fajlova i učiniti ga dostupnim za preuzimanje grupi ljudi.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Informacije o vaučeru</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Izdavalac</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Link vaučera</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Važi do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Lična poruka od {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>