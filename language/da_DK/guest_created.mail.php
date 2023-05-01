<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gæstevoucher
subject: {guest.subject}

{alternative:plain}

Kære gæst!

Herunder har du en voucher som giver adgang til {cfg:site_name}. Du kan bruge voucheren til at uploade én portion filer og gøre den tilgængelig for hentning for en gruppe modtagere.

Udsteder: {guest.user_email}
Link til voucheren: {guest.upload_link}

Voucheren gælder indtil {date:guest.expires}, hvorefter den automatisk slettes.

{if:guest.message}Personlig meddelelse fra {guest.user_email}: {guest.message}{endif}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære gæst!
</p>

<p>
 Herunder har du en voucher som giver adgang til {cfg:site_name}. Du kan bruge voucheren til at uploade én portion filer og gøre den tilgængelig for hentning for en gruppe modtagere.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaljer om voucheren</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Udsteder</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Link til voucheren</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Gyldig indtil</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personlig meddelelse fra {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>