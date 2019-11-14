<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Recordatorios automáticos enviados para envío de archivos n°{transfer.id}

{alternative:plain}

Hola,

Se envió un recordatorio automático a los destinatarios que no descargaron los archivos de tu transferencia n°{transfer.id} en el servicio FileSender de {cfg:site_name} ({transfer.link}): 

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    Se envió un recordatorio automático a los destinatarios que no descargaron los archivos de tu transferencia n°{transfer.id} en el servicio FileSender de {cfg:site_name} ({transfer.link}):
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Saludos,<br />
    {cfg:site_name}
</p>