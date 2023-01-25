<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Invitación recibida
Asunto: {guest.subject}

{alternative:plain}

Hola,

En este mensaje encontrarás una invitación que te proporciona permisos de acceso al servicio FileSender de {cfg:site_name}. Puedes usar esta invitación para subir un conjunto
 de ficheros y que otras personas se los puedan descargar.

Emisor: {guest.user_email}
Enlace: {guest.upload_link}

Esta invitación estará disponible hasta el {date:guest.expires}. Pasada esta fecha será automáticamente eliminada.

{if:guest.message}Mensaje personal de {guest.user_email}: {guest.message}{endif}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    En este mensaje encontrar&aacute;s una invitaci&oacute;n que te proporciona permisos de acceso al servicio FileSender de {cfg:site_name}. Puedes usar esta invitación para subir un conjunto de ficheros y que otras personas se los puedan descargar.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalles de la invitación</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Emisor</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Enlace</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Fecha de expiración</td>
{if:guest.does_not_expire}
            <td>nunca</td>
{else}
            <td>{date:guest.expires}</td>
{endif}
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Mensaje personal de {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Saludos,<br />
    {cfg:site_name}
</p>
