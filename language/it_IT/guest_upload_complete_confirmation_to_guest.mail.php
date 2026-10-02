<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: L'ospite ha finito di caricare i file

{alternative:plain}

Gentile utente,

L'ospite seguente ha terminato di caricare i file utilizzando un Voucher:

Ospite: {guest.email}
Voucher: {cfg:site_url}?s=upload&vid={guest.token}

Il Voucher è disponibile fino al {date: guest.expires}, dopodiché verrà automaticamente eliminato.

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
L'ospite seguente ha terminato di caricare i file utilizzando un Voucher:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Dettagli Voucher</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Ospite</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Voucher</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Disponibile fino al</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>
',

