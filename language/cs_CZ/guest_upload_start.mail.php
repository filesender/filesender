subject: Host zahájil nahrávání souborů

{alternative:plain}

Vážený uživateli,

Následující host zahájil nahrávání souborů na Vaši pozvánku :

Host: {guest.email}
Odkaz na pozvánku: {cfg:site_url}?s=upload&vid={guest.token}

Pozvání je k dispozici do {date:guest.expires},poté bude automaticky vymazáno.

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Následující host zahájil nahrávání souborů na Vaši pozvánku :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Podrobnosti pozvánky</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Host</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Odkaz na pozvánku</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Platnost do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>

