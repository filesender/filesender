<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: (recordatorio) invitación recibida
Asunto: (recordatorio) {guest.subject}

{alternative:plain}

Hola,

Esto es un recordatorio. Bajo este mensaje encontrarás una invitación que te proporciona permisos de acceso al {cfg:site_name}. Puedes usar esta invitación para subir un grupo de ficheros y que otras personas se los puedan descargar.

Emisor: {guest.user_email}
Enlace: {guest.upload_link}

La invitación estará disponible hasta el {date:guest.expires}. Después de esta fecha será automáticamente eliminada.

{if:guest.message}Mensaje personal de {guest.user_email}: {guest.message}{endif}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    Esto es un recordatorio. En este mensaje encontrarás una invitación que te proporciona permisos de acceso al {cfg:site_name}. Puedes usar esta invitación para subir un grupo de ficheros y que otras personas se los puedan descargar.
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
            <td>{date:guest.expires}</td>
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