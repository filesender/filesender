<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Fichero/s disponible/s para descargar 
Asunto: {transfer.subject}

{alternative:plain}

Hola,

{if:transfer.files>1}Los siguientes ficheros han sido subidos{else}el siguiente fichero ha sido subido{endif} al servicio FileSender de {cfg:site_name} por {transfer.user_email} y tienes permisos de descarga sobre su contenido:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{transfer.files.first().name} ({size:transfer.files.first().size})
{endif}

Enlace de descarga: {recipient.download_link}

La transacción estará disponible hasta el {date:transfer.expires}. Después de esta fecha será automáticamente eliminada.

{if:transfer.message || transfer.subject}
Mensaje personal de {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    {if:transfer.files>1}los siguientes ficheros han sido subidos{else}el siguiente fichero ha sido subido{endif} al servicio FileSender de {cfg:site_name} por {transfer.user_email} y tienes permisos de descarga sobre su contenido.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalles de la transacción</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fichero{if:transfer.files>1}s{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.name} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().name} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        {if:transfer.files>1}
        <tr>
            <td>Tamaño</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Fecha de expiración</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Enlace de descarga</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Mensaje personal de {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Saludos,<br />
    {cfg:site_name}
</p>