<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Voucher ospite ricevuto
subject: {guest.subject}

{alternative:plain}

Gentile utente,

Di seguito è riportato un voucher che concede l'accesso a {cfg:site_name}. Puoi utilizzare questo voucher per caricare un set di file e renderlo disponibile per il download a un gruppo di persone.

Emittente: {guest.user_email}
Link del Voucher: {guest.upload_link}

Il voucher è disponibile fino al {date:guest.expires}, dopodiché verrà automaticamente eliminato.

{if:guest.message}Messaggio personale da {guest.user_email}: {guest.message}{endif}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Di seguito è riportato un voucher che concede l'accesso a <a href="{cfg:site_url}">{cfg:site_name}</a>. Puoi utilizzare questo voucher per caricare un set di file e renderlo disponibile per il download a un gruppo di persone.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Dettagli del Voucher</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Emittente</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Link del Voucher</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Valido fino al</td>
{if:guest.does_not_expire}
            <td>mai</td>
{else}
            <td>{date:guest.expires}</td>
{endif}
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Messaggio personale da {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

