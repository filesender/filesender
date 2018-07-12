onderwerp: Gast begint met het uploaden van bestanden

{alternative:plain}

Geachte mevrouw, heer,

De volgende gast is begonnen met het uploaden van bestanden via uw voucher :

Gast: {guest.email}
Voucher link: {cfg:site_url}?s=upload&vid={guest.token}

De voucher is beschikbaar tot {date:guest.expires} waarna deze automatisch verwijdert wordt.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
</p>

<p>
  De volgende gast is begonnen met het uploaden van bestanden via uw voucher :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gast</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Voucher link</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Geldig tot</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>