<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Kutsu vastaanotettu

{alternative:plain}

Hei!

Sinulle on lähetetty palveluun {cfg:site_name} kutsu, jolla pääset jakamaan tiedostosi valitsemillesi vastaanottajille.

Lähettäjä: {guest.user_email}
Kutsulinkki: {guest.upload_link}

Kutsu on käytettävissä {date:guest.expires} asti, minkä jälkeen se poistetaan automaattisesti.

{if:guest.message}Käyttäjän {guest.user_email} viesti sinulle: {guest.message}{endif}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
   Hei!
</p>

<p>
   Sinulle on lähetetty palveluun <a href="{cfg:site_url}">{cfg:site_name}</a> kutsu, jolla pääset jakamaan tiedostosi valitsemillesi vastaanottajille.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Kutsun tiedot</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Lähettäjä</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Kutsulinkki</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Voimassa</td>
{if:guest.does_not_expire}
            <td>ei koskaan</td>
{else}
            <td>{date:guest.expires}</td>
{endif}
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Käyttäjän {guest.user_email} viesti sinulle:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>
