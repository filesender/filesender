<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: El/los archivo(s) ya no está(n) disponible(s) para su descarga 

{alternative:plain}

Hola,

{if:transfer.files>1}
los ficheros asociados al identificador n°{transfer.id} han caducado y ya no están disponibles para descargar ({transfer.link}).
{else}
el fichero con identificador n°{transfer.id} ha caducado y ya no está disponible para descargar ({transfer.link}).
{endif}

Información adicional:
{if:transfer.files>1}{each:transfer.files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{transfer.files.first().name} ({size:transfer.files.first().size})
{endif}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    tu <a href="{transfer.link}">transferencia con identificador n°{transfer.id}</a> ha caducado y ya no está disponible para descargar.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Información adicional</th>
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
    </tbody>
</table>

<p>
    Saludos,<br />
    {cfg:site_name}
</p>