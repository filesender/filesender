<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Host ukončil nahrávání souborů

{alternative:plain}

Vážený uživateli,

Následující host ukončil nahrávání souborů pomocí pozvánky hosta :

Host: {guest.email}
Odkaz na pozvánku: {cfg:site_url}?s=upload&vid={guest.token}

Pozvánka je k dispozici do {date:guest.expires}, poté bude automaticky smazán.

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
   Vážený uživateli,
</p>

<p>
Následující host ukončil nahrávání souborů pomocí pozvánky hosta :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaily pozvánky</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Host</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Odkaz na pozvánku</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Platná do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>
',
