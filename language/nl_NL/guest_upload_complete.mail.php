<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gastgebruiker beëindigd het uploaden van bestanden

{alternative:plain}

Geachte heer, mevrouw,

De volgende gastgebruiker heeft het uploaden van bestanden via uw voucher beëindigd :

Gastgebruiker: {guest.email}
Voucher link: {cfg:site_url}?s=upload&vid={guest.token}

Het voucher is beschikbaar tot {date:guest.expires} waarna deze automatisch verwijdert wordt.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    De volgende gastgebruiker heeft het uploaden van bestanden via uw voucher beëindigd :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gastgebruiker</td>
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
