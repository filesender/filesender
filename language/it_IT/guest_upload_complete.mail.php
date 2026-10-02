<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: L'ospite ha terminato l'upload dei file

{alternative:plain}

Gentile utente,

Il seguente ospite ha terminato di caricare i file dal tuo Voucher :

Ospite: {guest.email}
Link del Voucher: {cfg:site_url}?s=upload&vid={guest.token}

Il voucher è disponibile fino al {date:guest.expires}, dopodiché verrà automaticamente eliminato.

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Il seguente ospite ha terminato di caricare i file dal tuo Voucher :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Dettagli del Voucher</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Ospite</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Link del Voucher</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Valido fino al</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

