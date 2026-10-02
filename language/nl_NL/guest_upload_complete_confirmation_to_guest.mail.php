<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gastgebruik om bestanden te versturen is beëindigd

{alternative:plain}

Geachte heer, mevrouw,

De volgende gastgebruiker kan geen bestanden meer versturen :

Gastgebruiker: {guest.email}
Voucher link: {cfg:site_url}?s=upload&vid={guest.token}

Het voucher is geldig tot {date:guest.expires} waarna deze automatisch wordt verwijderd.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
De volgende gastgebruiker kan geen bestanden meer versturen :
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
