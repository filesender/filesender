<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Viesis ir pabeidzis failu augšupielādi

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Šis viesis ir pabeidzis failu augšupielādi no Jūsu piekļuves vaučera:

Viesis: {guest.email}
Piekļuves vaučera saite: {cfg:site_url}?s=upload&vid={guest.token}

Piekļuves vaučers ir pieejams līdz {date:guest.expires}, pēc kura tas tiks automātiski izdzēsts.

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Šis viesis ir pabeidzis failu augšupielādi no Jūsu piekļuves vaučera:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Vaučera detalizēta informācija</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Viesis</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Vaučera saite</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Derīgs līdz</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Ar cieņu<br />
    {cfg:site_name}
</p>