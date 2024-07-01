<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Kutsumasi vieras jakoi tiedostoja

{alternative:plain}

Hei!

Pyynnöstäsi ilmoitamme, että kutsumasi vieras on siirtänyt yhden tai useampia tiedostoja palveluun {cfg:site_name}.

Käyttäjä: {guest.email}
Kutsulinkki: {cfg:site_url}?s=upload&vid={guest.token}

Kutsu on voimassa {date:guest.expires} asti, minkä jälkeen se poistuu ja lakkaa toimimasta.

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Pyynnöstäsi ilmoitamme, että kutsumasi vieras on siirtänyt yhden tai useampia tiedostoja palveluun {cfg:site_name}.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Kutsun tiedot</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Käyttäjä</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Kutsulinkki</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Kutsu erääntyy</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>