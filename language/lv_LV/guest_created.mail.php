<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Viesa piekļuves vaučers saņemts
subject: {guest.subject}

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Zemāk atradīsiet vaučeri, kas piešķir piekļuvi {cfg:site_name}. Šo piekļuves vaučeri var izmantot, lai augšupielādētu vienu failu kopu un padarītu to pieejamu lejupielādei personu grupai.

Izdevējs: {guest.user_email}
Piekļuves vaučera saite: {guest.upload_link}

{if:guest.does_not_expire}
Šim piekļuves vaučeram nav derīguma termiņš.
{else}
Piekļuves vaučers ir pieejams līdz {date:guest.expires}, pēc kura tas tiks automātiski izdzēsts.
{endif}

{if:guest.message}Personiska ziņa no {guest.user_email}: {guest.message}{endif}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Zemāk atradīsiet vaučeri, kas piešķir piekļuvi <a href="{cfg:site_url}">{cfg:site_name}</a>. Šo piekļuves vaučeri var izmantot, lai augšupielādētu vienu failu kopu un padarītu to pieejamu lejupielādei personu grupai.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Vaučera detalizēta informācija</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Izdevējs</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Vaučera saite</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">Šī uzaicinājuma derīgums nebeidzas</td>
{else}
            <td>Derīgs līdz</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Personiska ziņa no {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
   Ar cieņu<br />
    {cfg:site_name}
</p>
