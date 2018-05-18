onderwerp: (herinnering) gast voucher ontvangen
onderwerp: (herinnering) {guest.subject}

{alternative:plain}

Geachte mevrouw, heer,

Dit is een herinnering. Hieronder vindt u een voucher die toegang verleent tot {cfg: site_name}. U kunt deze voucher gebruiken om één set bestanden te uploaden en beschikbaar te maken als download voor een groep mensen.

Uitgever: {guest.user_email}
Voucher link: {guest.upload_link}

Deze voucher is beschikbaar tot {date:guest.expires} waarna deze automatisch verwijdert wordt.

{if:guest.message}Persoonlijke boodschap van {guest.user_email}: {guest.message}{endif}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
</p>

<p>
    Dit is een herinnering. Hieronder vindt u een voucher die toegang verleent tot <a href="{cfg:site_url}">{cfg:site_name}</a>. U kunt deze voucher gebruiken om één set bestanden te uploaden en beschikbaar te maken als download voor een groep mensen.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Uitgever</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Voucher link</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Geldig tot</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Persoonlijke boodschap van {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>

