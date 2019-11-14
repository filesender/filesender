<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
asunto: El invitado ha terminado de subir archivos

{alternative:plain}

Estimado señor o señora,

El siguiente invitado finalizó la carga de archivos mediante un voucher de invitado:

Invitado: {guest.email}
Voucher link: {cfg:site_url}?s=upload&vid={guest.token}

El voucher está disponible hasta {date:guest.expires} Luego de este tiempo, será borrado.

Un saludo,
{cfg:site_name}

{alternative:html}

<p>
    Estimado señor o señora,
</p>

<p>
El siguiente invitado finalizó la carga de archivos mediante un voucher de invitado:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">detalles del Voucher</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Guest</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Enlace al Voucher</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Valido hasta</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Un saludos,<br />
    {cfg:site_name}
</p>
',
