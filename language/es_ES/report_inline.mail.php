<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Informe sobre {target.type} #{target.id}

{alternative:plain}

Hola,

aquí se encuentra el informe sobre tu {target.type}:

{target.type} número: {target.id}

{if:target.type == "Transfer"}
Esta transferencia tiene {transfer.files} ficheros con un tamaño total de {size:transfer.size}.

Esta transferencia está/estuvo disponible hasta el {date:transfer.expires}.

Esta transferencia fue enviada a los destinatarios {transfer.recipients}.
{endif}
{if:target.type == "File"}
Este fichero se denomina {file.name}, tiene un tamaño de {size:file.size} y está/estuvo disponible hasta el {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
El destinatario tiene dirección de correo electrónico {recipient.email} y está/estuvo disponible hasta el {date:recipient.expires}.
{endif}

Aquí se encuentran los logs completos de la transferencia:

{raw:content.plain}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    aquí se encuentra el informe sobre tu {target.type}:<br /><br />
    
    {target.type} número: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Esta transferencia tiene {transfer.files} ficheros con un tamañoo total de {size:transfer.size}.<br /><br />
    
    Esta transferencia está/estuvo disponible hasta el {date:transfer.expires}.<br /><br />
    
    Esta transferencia fue enviada a los destinatarios {transfer.recipients}.
    {endif}
    {if:target.type == "File"}
    Este fichero se denomina {file.name}, tiene un tamaño de {size:file.size} y está/estuvo disponible hasta el {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    El destinatario tiene dirección de correo electrónico {recipient.email} y está/estuvo disponible hasta el {date:recipient.expires}.
    {endif}
</p>

<p>
    Aquí se encuentran los logs completos de la transferencia:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Fecha</th>
            <th>Evento</th>
            <th>Dirección IP</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Saludos,<br/>
{cfg:site_name}</p>