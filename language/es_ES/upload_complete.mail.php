<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Fichero{if:transfer.files>1}s{endif} subido satisfactoriamente

{alternative:plain}

Hola,

{if:transfer.files>1}los siguientes ficheros han sido subidos{else}el siguiente fichero ha sido subido{endif} satisfactoriamente al servicio FileSender de {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{transfer.files.first().name} ({size:transfer.files.first().size})
{endif}

Más información: {transfer.link}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    {if:transfer.files>1}los siguientes ficheros han sido subidos{else}el siguiente fichero ha sido subido{endif} satisfactoriamente al servicio FileSender de {cfg:site_name}.
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
        <tr>
            <td>Tamaño</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Más información</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Saludos,<br />
    {cfg:site_name}
</p>