<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Descargar resumen 

{alternative:plain}

Hola,

{if:files>1}Varios ficheros{else}Un fichero{endif} que subiste {if:files>1}han{else}ha{endif} sido {if:files>1}descargados{else}descargado{endif} desde el servicio FileSender de {cfg:site_name} por {recipient.email} :

{if:files>1}{each:files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{files.first().name} ({size:files.first().size})
{endif}

Puedes acceder a tus ficheros y visualizar detalladamente las estadísticas de descarga en la página de transferencias, en {files.first().transfer.link}.

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    {if:files>1}Varios ficheros{else}Un fichero{endif} que subiste {if:files>1}han{else}ha{endif} sido {if:files>1}descargados{else}descargado{endif} desde el servicio FileSender de {cfg:site_name} por {recipient.email}.
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.name} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().name} ({size:files.first().size})
    {endif}
</p>

<p>
    Puedes acceder a tus ficheros y visualizar detalladamente las estad&iacute;sticas de descarga en la p&aacute;gina de transferencias, en <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Saludos,<br />
    {cfg:site_name}
</p>