<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Feedback del {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}invitado{endif}#{target_id} {target.email}

{alternative:plain}

Hola,

Se ha recibido un mensaje de opinión del {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}invitado{endif}#{target_id} {target.email}. Puedes encontrarlo adjunto.

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
 Se ha recibido un mensaje de opini&oacute;n del {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}invitado{endif}#{target_id} {target.email}. Puedes encontrarlo adjunto.
</p>

<p>
    Saludos,<br />
     {cfg:site_name}
</p>
~